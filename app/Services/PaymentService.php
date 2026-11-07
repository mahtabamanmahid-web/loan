<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\LoanWorkflowException;
use App\Exceptions\PaymentGatewayException;
use App\Models\LoanApplication;
use App\Models\LoanInstallment;
use App\Models\PaymentLog;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Notifications\PaymentSuccessful;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Payment workflow:
 *
 *  1. initiate*()  – create a local transaction (status "initiated") with our own reference,
 *                    ask UddoktaPay for a checkout URL, mark "pending".
 *  2. customer pays on the gateway.
 *  3. verifyInvoice() – called from the browser redirect AND from the webhook.
 *                    It ALWAYS asks UddoktaPay's verify API (never trusts the browser),
 *                    checks status, amount and reference, then applies the business
 *                    effect exactly once (row lock + processed_at = idempotency).
 *
 * Successful payment of a processing fee does NOT approve or disburse a loan.
 */
class PaymentService
{
    public function __construct(
        private UddoktaPayService $gateway,
        private LoanService $loans,
        private ReferenceGenerator $refs,
    ) {}

    /* ---------------------------------------------------------------- initiate */

    public function initiateProcessingFee(LoanApplication $app, User $user): PaymentTransaction
    {
        if ($app->user_id !== $user->id) {
            throw new LoanWorkflowException(__('Application not found.'));
        }
        if ($app->status !== ApplicationStatus::AwaitingAgreement || ! $app->agreement?->isAccepted()) {
            throw new LoanWorkflowException(__('Please review and accept the loan agreement first.'));
        }
        if (! $app->requiresUpfrontFee()) {
            throw new LoanWorkflowException(__('No fee is due for this application.'));
        }

        return $this->initiate($user, (float) $app->agreement->processing_fee, 'processing_fee', [
            'loan_application_id' => $app->id,
            'description' => __('Processing fee for application :ref', ['ref' => $app->reference]),
        ]);
    }

    public function initiateInstallment(LoanInstallment $installment, User $user, ?float $amount = null): PaymentTransaction
    {
        $loan = $installment->loan;
        if ($loan->user_id !== $user->id || $loan->status !== LoanStatus::Active) {
            throw new LoanWorkflowException(__('This loan is not active.'));
        }

        $due = $installment->dueAmount();
        $amount = round($amount ?? $due, 2);
        if ($due <= 0) {
            throw new LoanWorkflowException(__('This installment is already paid.'));
        }
        if ($amount < 1 || $amount > $loan->outstanding()) {
            throw new LoanWorkflowException(__('Invalid payment amount.'));
        }

        return $this->initiate($user, $amount, 'installment', [
            'loan_id' => $loan->id,
            'loan_installment_id' => $installment->id,
            'description' => __('Installment #:no for loan :loan', ['no' => $installment->installment_number, 'loan' => $loan->loan_number]),
        ]);
    }

    private function initiate(User $user, float $amount, string $purpose, array $attributes): PaymentTransaction
    {
        $tx = PaymentTransaction::create(array_merge($attributes, [
            'reference' => $this->refs->make('PAY', 'payment_transactions', 'reference', 8),
            'user_id' => $user->id,
            'purpose' => $purpose,
            'amount' => $amount,
            'status' => PaymentStatus::Initiated,
        ]));

        $this->log($tx, 'initiated', 'Payment initiated', ['amount' => $amount, 'purpose' => $purpose]);

        try {
            $charge = $this->gateway->createCharge([
                'full_name' => $user->name,
                'email' => $user->email,
                'amount' => $amount,
                // Our reference travels through the gateway and comes back on verify
                'metadata' => ['reference' => $tx->reference, 'user_id' => (string) $user->id, 'purpose' => $purpose],
                'redirect_url' => config('uddoktapay.callback_url') ?: route('payment.callback'),
                'cancel_url' => (config('uddoktapay.cancel_url') ?: route('payment.cancel')).'?reference='.$tx->reference,
                'webhook_url' => config('uddoktapay.webhook_url') ?: route('api.payments.webhook'),
            ]);
        } catch (PaymentGatewayException $e) {
            $tx->update(['status' => PaymentStatus::Failed, 'failure_reason' => Str::limit($e->getMessage(), 250)]);
            $this->log($tx, 'initiate_failed', $e->getMessage(), [], 'error');
            throw $e;
        }

        $tx->update(['status' => PaymentStatus::Pending, 'payment_url' => $charge['payment_url']]);
        $this->log($tx, 'charge_created', 'Checkout URL received', $charge['raw']);

        return $tx;
    }

    /* ---------------------------------------------------------------- verify */

    /**
     * Verify an invoice with UddoktaPay and process it once.
     * Safe to call any number of times for the same invoice (redirect + webhook + retries).
     */
    public function verifyInvoice(string $invoiceId, string $source, ?string $ip = null): PaymentTransaction
    {
        $invoiceId = trim($invoiceId);
        if ($invoiceId === '' || strlen($invoiceId) > 100) {
            throw new PaymentGatewayException('Invalid invoice id.');
        }

        // 1. Ask the gateway – never trust redirect parameters alone.
        $data = $this->gateway->verifyPayment($invoiceId);

        $metadata = $data['metadata'] ?? [];
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?: [];
        }
        $reference = $metadata['reference'] ?? null;

        $tx = $reference ? PaymentTransaction::where('reference', $reference)->first() : null;
        $tx ??= PaymentTransaction::where('gateway_invoice_id', $invoiceId)->first();

        if (! $tx) {
            $this->log(null, 'verify_unknown', "Verify for unknown reference via $source", ['invoice_id' => $invoiceId, 'response' => $data], 'warning', $ip);
            throw new PaymentGatewayException('Payment reference not found.');
        }

        // 2. Lock the transaction row: concurrent redirect + webhook cannot both process it.
        return DB::transaction(function () use ($tx, $data, $invoiceId, $source, $ip) {
            $tx = PaymentTransaction::whereKey($tx->id)->lockForUpdate()->first();

            if ($tx->processed_at !== null) {
                $this->log($tx, 'duplicate', "Already processed – ignored ($source)", ['invoice_id' => $invoiceId], 'info', $ip);

                return $tx;
            }

            // The same invoice must not be attached to a different transaction
            if ($tx->gateway_invoice_id && $tx->gateway_invoice_id !== $invoiceId) {
                $this->log($tx, 'invoice_mismatch', 'Invoice id does not match this transaction', ['invoice_id' => $invoiceId], 'error', $ip);
                throw new PaymentGatewayException('Invoice mismatch.');
            }

            $gatewayStatus = strtoupper((string) ($data['status'] ?? ''));
            $trxId = $data['transaction_id'] ?? null;
            $paidAmount = (float) ($data['amount'] ?? 0);

            $tx->fill([
                'gateway_invoice_id' => $invoiceId,
                'payment_method' => $data['payment_method'] ?? $tx->payment_method,
                'sender_number' => $data['sender_number'] ?? $tx->sender_number,
                'gateway_fee' => isset($data['fee']) ? (float) $data['fee'] : $tx->gateway_fee,
                'charged_amount' => isset($data['charged_amount']) ? (float) $data['charged_amount'] : $tx->charged_amount,
                'gateway_response' => $data,
                'verified_at' => now(),
            ]);

            $this->log($tx, 'verify', "Verified via $source: $gatewayStatus", $data, 'info', $ip);

            if ($gatewayStatus === 'COMPLETED') {
                // 3. Amount check – the paid amount must cover what we asked for.
                if ($paidAmount + 0.009 < (float) $tx->amount) {
                    $tx->status = PaymentStatus::Failed;
                    $tx->failure_reason = "Amount mismatch: expected {$tx->amount}, got {$paidAmount}";
                    $tx->processed_at = now();
                    $tx->save();
                    $this->log($tx, 'amount_mismatch', $tx->failure_reason, [], 'error', $ip);

                    return $tx;
                }

                // 4. A gateway transaction id can only ever be used once.
                if ($trxId && PaymentTransaction::where('gateway_transaction_id', $trxId)->whereKeyNot($tx->id)->exists()) {
                    $tx->status = PaymentStatus::Failed;
                    $tx->failure_reason = 'Gateway transaction id already used.';
                    $tx->processed_at = now();
                    $tx->save();
                    $this->log($tx, 'duplicate_trx', $tx->failure_reason, ['transaction_id' => $trxId], 'error', $ip);

                    return $tx;
                }

                $tx->gateway_transaction_id = $trxId;
                $tx->status = PaymentStatus::Completed;
                $tx->paid_at = isset($data['date']) ? rescue(fn () => \Illuminate\Support\Carbon::parse($data['date']), now(), false) : now();
                $tx->processed_at = now();
                $tx->save();

                try {
                    $this->applyBusinessEffect($tx);
                    $this->log($tx, 'completed', 'Payment completed and applied', [], 'info', $ip);
                } catch (LoanWorkflowException $e) {
                    // Money was received but could not be applied automatically (e.g. loan closed
                    // in the meantime). Keep it as completed and flag it for manual review.
                    $this->log($tx, 'apply_failed', 'MANUAL REVIEW: '.$e->getMessage(), [], 'error', $ip);
                }

                DB::afterCommit(fn () => rescue(fn () => $tx->user->notify(new PaymentSuccessful($tx))));

                return $tx;
            }

            if ($gatewayStatus === 'PENDING') {
                $tx->status = PaymentStatus::Pending;
                $tx->save();

                return $tx;
            }

            // ERROR / anything else → failed (final)
            $tx->status = PaymentStatus::Failed;
            $tx->failure_reason = Str::limit((string) ($data['message'] ?? 'Payment failed at gateway.'), 250);
            $tx->processed_at = now();
            $tx->save();

            return $tx;
        });
    }

    /** What a successful payment changes in the loan system. */
    private function applyBusinessEffect(PaymentTransaction $tx): void
    {
        if ($tx->purpose === 'installment' && $tx->loan) {
            $this->loans->recordRepayment($tx->loan, (float) $tx->amount, 'gateway', $tx->gateway_transaction_id, $tx);
        }
        // processing_fee: nothing else to do – the admin sees the fee as paid
        // and may now record the disbursement. Payment ≠ approval.
    }

    public function markCancelled(PaymentTransaction $tx): void
    {
        DB::transaction(function () use ($tx) {
            $tx = PaymentTransaction::whereKey($tx->id)->lockForUpdate()->first();
            if (in_array($tx->status, [PaymentStatus::Initiated, PaymentStatus::Pending], true) && $tx->processed_at === null) {
                $tx->update(['status' => PaymentStatus::Cancelled]);
                $this->log($tx, 'cancelled', 'Customer cancelled at gateway');
            }
        });
    }

    /* ---------------------------------------------------------------- refund */

    public function refund(PaymentTransaction $tx, User $admin, float $amount, string $reason): void
    {
        if ($tx->status !== PaymentStatus::Completed) {
            throw new LoanWorkflowException(__('Only successful payments can be refunded.'));
        }
        if ($tx->purpose !== 'processing_fee') {
            throw new LoanWorkflowException(__('Installment payments cannot be refunded online. Adjust the loan manually.'));
        }
        if ($amount <= 0 || $amount > (float) $tx->amount) {
            throw new LoanWorkflowException(__('Invalid refund amount.'));
        }

        try {
            $response = $this->gateway->refund((string) $tx->gateway_transaction_id, (string) $tx->payment_method, $amount, $tx->purposeLabel(), $reason);
        } catch (Throwable $e) {
            $this->log($tx, 'refund_failed', $e->getMessage(), [], 'error');
            throw new LoanWorkflowException(__('Refund failed: :msg', ['msg' => $e->getMessage()]));
        }

        $tx->forceFill([
            'status' => PaymentStatus::Refunded,
            'refunded_amount' => $amount,
            'refund_reason' => $reason,
            'refunded_at' => now(),
            'refunded_by' => $admin->id,
        ])->save();

        $this->log($tx, 'refunded', "Refunded $amount: $reason", $response);
        ActivityLogger::log('payment.refunded', $tx, "Refunded {$amount} for {$tx->reference}");
    }

    /* ---------------------------------------------------------------- audit */

    public function log(?PaymentTransaction $tx, string $event, ?string $message = null, array $payload = [], string $level = 'info', ?string $ip = null): void
    {
        PaymentLog::create([
            'payment_transaction_id' => $tx?->id,
            'event' => $event,
            'level' => $level,
            'message' => Str::limit((string) $message, 250),
            'payload' => $payload ?: null,
            'ip_address' => $ip ?? request()?->ip(),
        ]);
    }
}
