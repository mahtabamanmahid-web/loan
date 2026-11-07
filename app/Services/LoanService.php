<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Exceptions\LoanWorkflowException;
use App\Models\Loan;
use App\Models\LoanAgreement;
use App\Models\LoanApplication;
use App\Models\LoanInstallment;
use App\Models\LoanProduct;
use App\Models\LoanRepayment;
use App\Models\LoanTerm;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Notifications\ApplicationStatusChanged;
use App\Notifications\ApplicationSubmitted;
use App\Notifications\LoanDisbursed;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The loan life-cycle engine.
 *
 *  submit → (under review) → approve | reject | request documents
 *         → create agreement → customer accepts (+ upfront fee if configured)
 *         → admin records REAL disbursement → loan + installment schedule created
 *         → repayments → completed
 *
 * Every state change validates the current state first, so actions cannot be
 * applied twice or out of order (e.g. a loan can never be disbursed twice).
 */
class LoanService
{
    public function __construct(
        private LoanCalculator $calculator,
        private ReferenceGenerator $refs,
    ) {}

    /* ===================================================================
     |  Application
     * =================================================================== */

    public function submit(User $user, LoanProduct $product, LoanTerm $term, float $amount, array $applicant, string $ip): LoanApplication
    {
        $this->assertAmountAllowed($product, $amount);

        if ($term->loan_product_id !== $product->id || ! $term->is_active) {
            throw new LoanWorkflowException(__('Selected loan duration is not available for this package.'));
        }

        $openCount = $user->applications()->whereIn('status', ApplicationStatus::open())->count();
        if ($openCount >= (int) sys_setting('max_open_applications')) {
            throw new LoanWorkflowException(__('You already have an application in progress. Please wait for its result.'));
        }

        $calc = $this->calculator->calculate($amount, $term->months, (float) $term->interest_rate, $product);

        $application = DB::transaction(function () use ($user, $product, $term, $calc, $applicant, $ip) {
            // Keep the customer's profile in sync with the latest information
            $user->profile()->updateOrCreate([], [
                'occupation' => $applicant['occupation'] ?? null,
            ]);

            return LoanApplication::create([
                'reference' => $this->refs->make('APP', 'loan_applications'),
                'user_id' => $user->id,
                'loan_product_id' => $product->id,
                'loan_term_id' => $term->id,
                ...collect($applicant)->only([
                    'applicant_name', 'phone', 'email', 'occupation', 'purpose',
                ])->all(),
                'requested_amount' => $calc['amount'],
                'requested_months' => $calc['months'],
                'interest_rate' => $calc['interest_rate'],
                'total_interest' => $calc['total_interest'],
                'total_payable' => $calc['total_payable'],
                'installment_amount' => $calc['installment_amount'],
                'processing_fee' => $calc['processing_fee'],
                'status' => $user->isKycApproved() ? ApplicationStatus::Submitted : ApplicationStatus::NidPending,
                'terms_accepted_at' => now(),
                'terms_accepted_ip' => $ip,
            ]);
        });

        $user->notify(new ApplicationSubmitted($application));

        return $application;
    }

    public function assertAmountAllowed(LoanProduct $product, float $amount): void
    {
        $min = max((float) $product->min_amount, (float) sys_setting('min_loan_amount'));
        $max = min((float) $product->max_amount, (float) sys_setting('max_loan_amount'));

        if ($amount < $min || $amount > $max) {
            throw new LoanWorkflowException(__('Loan amount must be between :min and :max.', ['min' => money($min), 'max' => money($max)]));
        }
    }

    public function cancel(LoanApplication $app): void
    {
        $this->assertStatus($app, [ApplicationStatus::Submitted, ApplicationStatus::NidPending, ApplicationStatus::DocumentsRequested]);
        $app->update(['status' => ApplicationStatus::Cancelled]);
    }

    /* ===================================================================
     |  Admin review
     * =================================================================== */

    public function startReview(LoanApplication $app, User $admin): void
    {
        $this->assertStatus($app, [ApplicationStatus::Submitted, ApplicationStatus::DocumentsRequested, ApplicationStatus::NidPending]);
        $this->transition($app, ApplicationStatus::UnderReview, $admin, ['reviewed_at' => now()]);
    }

    public function approve(LoanApplication $app, User $admin, float $amount, int $months, float $rate, ?string $note): void
    {
        $this->assertStatus($app, [ApplicationStatus::Submitted, ApplicationStatus::UnderReview, ApplicationStatus::DocumentsRequested]);

        if (! $app->user->isKycApproved()) {
            throw new LoanWorkflowException(__('Customer NID must be verified before approval.'));
        }
        $this->assertAmountAllowed($app->product, $amount);

        $this->transition($app, ApplicationStatus::Approved, $admin, [
            'approved_amount' => $amount,
            'approved_months' => $months,
            'approved_interest_rate' => $rate,
            'admin_note' => $note,
            'approved_at' => now(),
            'reviewed_at' => $app->reviewed_at ?? now(),
        ]);
    }

    public function reject(LoanApplication $app, User $admin, string $reason): void
    {
        $this->assertStatus($app, [ApplicationStatus::Submitted, ApplicationStatus::UnderReview, ApplicationStatus::NidPending,
            ApplicationStatus::DocumentsRequested, ApplicationStatus::Approved, ApplicationStatus::AwaitingAgreement]);

        if ($app->isFeePaid()) {
            throw new LoanWorkflowException(__('A fee was already paid for this application. Refund it from Payments before rejecting.'));
        }

        $this->transition($app, ApplicationStatus::Rejected, $admin, ['admin_note' => $reason, 'rejected_at' => now()]);
    }

    public function requestDocuments(LoanApplication $app, User $admin, string $message): void
    {
        $this->assertStatus($app, [ApplicationStatus::Submitted, ApplicationStatus::UnderReview, ApplicationStatus::NidPending]);
        $this->transition($app, ApplicationStatus::DocumentsRequested, $admin, ['admin_note' => $message]);
    }

    /* ===================================================================
     |  Agreement
     * =================================================================== */

    public function createAgreement(LoanApplication $app, User $admin): LoanAgreement
    {
        $this->assertStatus($app, [ApplicationStatus::Approved]);

        $calc = $this->calculator->calculate($app->finalAmount(), $app->finalMonths(), $app->finalRate(), $app->product);

        // Agreements are frozen in English: legally consistent and renders correctly in PDF.
        $locale = app()->getLocale();
        app()->setLocale('en');
        $content = view('agreements.content', ['application' => $app, 'calc' => $calc])->render();
        app()->setLocale($locale);

        $agreement = DB::transaction(function () use ($app, $admin, $calc, $content) {
            $agreement = LoanAgreement::create([
                'agreement_number' => $this->refs->make('AGR', 'loan_agreements', 'agreement_number'),
                'loan_application_id' => $app->id,
                'content' => $content,
                'principal' => $calc['amount'],
                'months' => $calc['months'],
                'interest_rate' => $calc['interest_rate'],
                'total_payable' => $calc['total_payable'],
                'installment_amount' => $calc['installment_amount'],
                'processing_fee' => $calc['processing_fee'],
                'fee_collection' => $calc['fee_collection'],
                'created_by' => $admin->id,
            ]);

            $app->update(['status' => ApplicationStatus::AwaitingAgreement]);

            return $agreement;
        });

        ActivityLogger::log('loan.agreement_created', $app, "Agreement {$agreement->agreement_number} created");
        $app->user->notify(new ApplicationStatusChanged($app->fresh()));

        return $agreement;
    }

    public function acceptAgreement(LoanApplication $app, string $ip): void
    {
        $this->assertStatus($app, [ApplicationStatus::AwaitingAgreement]);

        $agreement = $app->agreement;
        if (! $agreement || $agreement->isAccepted()) {
            throw new LoanWorkflowException(__('This agreement was already accepted.'));
        }

        $agreement->forceFill(['accepted_at' => now(), 'accepted_ip' => $ip])->save();
    }

    /* ===================================================================
     |  Disbursement – creates the Loan ONLY after real money has been sent
     * =================================================================== */

    /**
     * @param  array{method: string, account?: ?string, reference: string, disbursed_at: string, first_due_date: string, note?: ?string}  $data
     */
    public function disburse(LoanApplication $app, User $admin, array $data): Loan
    {
        $loan = DB::transaction(function () use ($app, $admin, $data) {
            // Lock the row so two admins clicking at the same time cannot disburse twice
            $app = LoanApplication::whereKey($app->id)->lockForUpdate()->firstOrFail();

            $this->assertStatus($app, [ApplicationStatus::AwaitingAgreement]);
            if ($app->loan()->exists()) {
                throw new LoanWorkflowException(__('This application has already been disbursed.'));
            }
            if (! $app->agreement?->isAccepted()) {
                throw new LoanWorkflowException(__('Customer has not accepted the loan agreement yet.'));
            }
            if ($app->requiresUpfrontFee()) {
                throw new LoanWorkflowException(__('Processing fee has not been paid yet.'));
            }

            $agreement = $app->agreement;
            $product = $app->product;
            $calc = $this->calculator->calculate((float) $agreement->principal, (int) $agreement->months, (float) $agreement->interest_rate, $product);
            $disbursed = $agreement->fee_collection === 'deduct'
                ? round((float) $agreement->principal - (float) $agreement->processing_fee, 2)
                : (float) $agreement->principal;

            $loan = Loan::create([
                'loan_number' => $this->refs->make('LN', 'loans', 'loan_number'),
                'loan_application_id' => $app->id,
                'user_id' => $app->user_id,
                'principal' => $agreement->principal,
                'interest_rate' => $agreement->interest_rate,
                'months' => $agreement->months,
                'total_interest' => $calc['total_interest'],
                'total_payable' => $calc['total_payable'],
                'installment_amount' => $calc['installment_amount'],
                'processing_fee' => $agreement->processing_fee,
                'disbursed_amount' => $disbursed,
                'disbursement_method' => $data['method'],
                'disbursement_account' => $data['account'] ?? null,
                'disbursement_reference' => $data['reference'],
                'disbursed_at' => Carbon::parse($data['disbursed_at']),
                'first_due_date' => Carbon::parse($data['first_due_date'])->toDateString(),
                'status' => LoanStatus::Active,
                'disbursed_by' => $admin->id,
                'note' => $data['note'] ?? null,
            ]);

            $schedule = $this->calculator->schedule(
                (float) $agreement->principal, (int) $agreement->months, (float) $agreement->interest_rate,
                Carbon::parse($data['first_due_date']), $product->repayment_frequency
            );
            $loan->installments()->createMany($schedule);

            $app->update(['status' => ApplicationStatus::Disbursed]);

            return $loan;
        });

        ActivityLogger::log('loan.disbursed', $loan, "Loan {$loan->loan_number} disbursed ({$loan->disbursed_amount})", ['reference' => $loan->disbursement_reference]);
        $loan->user->notify(new LoanDisbursed($loan));

        return $loan;
    }

    /* ===================================================================
     |  Repayments
     * =================================================================== */

    /**
     * Apply a payment to the loan, oldest unpaid installment first.
     * Must be called inside the caller's DB transaction when tied to a gateway payment.
     */
    public function recordRepayment(Loan $loan, float $amount, string $method, ?string $reference = null,
        ?PaymentTransaction $transaction = null, ?User $recordedBy = null, ?string $note = null): LoanRepayment
    {
        return DB::transaction(function () use ($loan, $amount, $method, $reference, $transaction, $recordedBy, $note) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if ($loan->status !== LoanStatus::Active) {
                throw new LoanWorkflowException(__('This loan is not active.'));
            }
            if ($transaction && LoanRepayment::where('payment_transaction_id', $transaction->id)->exists()) {
                throw new LoanWorkflowException(__('This payment was already applied.'));
            }

            $amount = round($amount, 2);
            if ($amount <= 0 || $amount > $loan->outstanding() + 0.01) {
                throw new LoanWorkflowException(__('Amount must be between 1 and the outstanding balance (:amount).', ['amount' => money($loan->outstanding())]));
            }

            $remaining = $amount;
            $firstInstallmentId = $transaction?->loan_installment_id;

            $unpaid = $loan->installments()->where('status', '!=', InstallmentStatus::Paid->value)
                ->orderBy('installment_number')->lockForUpdate()->get();

            foreach ($unpaid as $inst) {
                if ($remaining <= 0) {
                    break;
                }
                $pay = min($remaining, $inst->dueAmount());
                $inst->paid_amount = round((float) $inst->paid_amount + $pay, 2);
                $remaining = round($remaining - $pay, 2);

                if ($inst->dueAmount() <= 0.009) {
                    $inst->status = InstallmentStatus::Paid;
                    $inst->paid_at = now();
                } else {
                    $inst->status = $inst->due_date->isPast() && ! $inst->due_date->isToday() ? InstallmentStatus::Overdue : InstallmentStatus::Partial;
                }
                $inst->save();
                $firstInstallmentId ??= $inst->id;
            }

            $repayment = $loan->repayments()->create([
                'loan_installment_id' => $firstInstallmentId,
                'payment_transaction_id' => $transaction?->id,
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
                'paid_at' => now(),
                'recorded_by' => $recordedBy?->id,
                'note' => $note,
            ]);

            $loan->total_paid = round((float) $loan->total_paid + $amount, 2);
            if ($loan->outstanding() <= 0.009) {
                $loan->status = LoanStatus::Completed;
                $loan->completed_at = now();
                $loan->application()->update(['status' => ApplicationStatus::Completed->value]);
            } elseif ($loan->application->status === ApplicationStatus::Disbursed) {
                $loan->application()->update(['status' => ApplicationStatus::Active->value]);
            }
            $loan->save();

            return $repayment;
        });
    }

    /**
     * Daily job: flag overdue installments and apply the product's late fee once.
     * Returns the number of installments marked overdue.
     */
    public function markOverdueInstallments(): int
    {
        $count = 0;

        LoanInstallment::query()
            ->whereIn('status', [InstallmentStatus::Pending->value, InstallmentStatus::Partial->value])
            ->whereHas('loan', fn ($q) => $q->where('status', LoanStatus::Active->value))
            ->with('loan.application.product')
            ->chunkById(200, function ($installments) use (&$count) {
                foreach ($installments as $inst) {
                    $product = $inst->loan->application->product;
                    $graceEnds = $inst->due_date->copy()->addDays((int) $product->grace_days)->endOfDay();
                    if (now()->lessThanOrEqualTo($graceEnds)) {
                        continue;
                    }

                    DB::transaction(function () use ($inst, $product) {
                        $fee = (float) $product->late_fee_per_installment;
                        $inst->status = InstallmentStatus::Overdue;
                        if ($fee > 0 && (float) $inst->late_fee == 0.0) {
                            $inst->late_fee = $fee;
                            $inst->loan->increment('total_late_fee', $fee);
                        }
                        $inst->save();
                    });
                    $count++;
                }
            });

        return $count;
    }

    /* ===================================================================
     |  Internals
     * =================================================================== */

    /** @param list<ApplicationStatus> $allowed */
    private function assertStatus(LoanApplication $app, array $allowed): void
    {
        if (! in_array($app->status, $allowed, true)) {
            throw new LoanWorkflowException(__('This action is not allowed while the application is ":status".', ['status' => $app->status->label()]));
        }
    }

    private function transition(LoanApplication $app, ApplicationStatus $to, User $admin, array $extra = []): void
    {
        $from = $app->status;
        $app->forceFill(array_merge($extra, ['status' => $to, 'reviewed_by' => $admin->id]))->save();

        ActivityLogger::log('loan.'.$to->value, $app, "Application {$app->reference}: {$from->value} → {$to->value}");

        if (in_array($to, [ApplicationStatus::Approved, ApplicationStatus::Rejected, ApplicationStatus::DocumentsRequested], true)) {
            $app->user->notify(new ApplicationStatusChanged($app));
        }
    }
}
