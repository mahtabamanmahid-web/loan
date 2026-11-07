<?php

namespace App\Http\Controllers\Payment;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use App\Models\LoanInstallment;
use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * Customer-facing payment flow:
 *   payFee / payInstallment → redirect to UddoktaPay → callback → result page
 */
class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    /** Start paying the (disclosed, upfront) processing fee of an approved application. */
    public function payFee(LoanApplication $application): RedirectResponse
    {
        $this->ensureOwner($application);
        $tx = $this->payments->initiateProcessingFee($application, auth()->user());

        return redirect()->away($tx->payment_url);
    }

    /** Start paying an installment (full due amount, or a custom partial amount). */
    public function payInstallment(Request $request, LoanInstallment $installment): RedirectResponse
    {
        $this->ensureOwner($installment->loan);
        $request->validate(['amount' => 'nullable|numeric|min:1']);
        $custom = $request->filled('amount') ? (float) $request->input('amount') : null;

        $tx = $this->payments->initiateInstallment($installment, auth()->user(), $custom);

        return redirect()->away($tx->payment_url);
    }

    /**
     * UddoktaPay sends the customer back here with ?invoice_id=...
     * We verify the invoice with UddoktaPay's server before showing success.
     */
    public function callback(Request $request): RedirectResponse
    {
        $invoiceId = (string) $request->input('invoice_id', '');

        if ($invoiceId === '') {
            return redirect()->route(auth()->check() ? 'user.transactions.index' : 'login')
                ->with('error', __('Payment information was not received. If money was deducted, it will be updated automatically shortly.'));
        }

        try {
            $tx = $this->payments->verifyInvoice($invoiceId, 'redirect', $request->ip());
        } catch (Throwable $e) {
            report($e);

            return redirect()->route(auth()->check() ? 'user.transactions.index' : 'login')
                ->with('error', __('We could not verify the payment right now. If money was deducted, it will be updated automatically shortly.'));
        }

        // Guests (expired session) are asked to log in; the result is already saved.
        if (! auth()->check() || auth()->id() !== $tx->user_id) {
            return redirect()->route('login')->with('success', __('Payment status updated. Please log in to see the details.'));
        }

        return redirect()->route('payment.result', $tx);
    }

    /** Customer pressed "cancel" on the gateway page. */
    public function cancel(Request $request): RedirectResponse
    {
        $tx = PaymentTransaction::where('reference', (string) $request->query('reference'))->first();

        if ($tx && $tx->user_id === auth()->id()) {
            $this->payments->markCancelled($tx);

            return redirect()->route('payment.result', $tx);
        }

        return redirect()->route('user.transactions.index')->with('error', __('Payment was cancelled.'));
    }

    /** Success / failed / pending screen. */
    public function result(PaymentTransaction $transaction): View
    {
        $this->ensureOwner($transaction);

        return view('user.payments.result', [
            'tx' => $transaction->load('application', 'loan'),
            'success' => $transaction->status === PaymentStatus::Completed,
        ]);
    }
}
