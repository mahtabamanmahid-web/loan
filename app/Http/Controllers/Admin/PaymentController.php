<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\ActivityLogger;
use App\Services\PaymentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.payments.index', [
            'payments' => $this->filtered($request)->with('user')->latest()->paginate(25)->withQueryString(),
            'statuses' => PaymentStatus::cases(),
        ]);
    }

    public function show(PaymentTransaction $transaction): View
    {
        return view('admin.payments.show', ['tx' => $transaction->load('user', 'application', 'loan', 'installment', 'logs')]);
    }

    /** Ask UddoktaPay again for the status of this invoice (e.g. pending or missed webhook). */
    public function verify(Request $request, PaymentTransaction $transaction, PaymentService $payments): RedirectResponse
    {
        if (! $transaction->gateway_invoice_id) {
            return back()->with('warning', __('This payment has no gateway invoice yet (customer did not finish checkout).'));
        }

        try {
            $tx = $payments->verifyInvoice($transaction->gateway_invoice_id, 'admin', $request->ip());
        } catch (Throwable $e) {
            return back()->with('error', __('Verification failed: :msg', ['msg' => $e->getMessage()]));
        }
        ActivityLogger::log('payment.verified', $tx, "Manually re-verified {$tx->reference}: {$tx->status->value}");

        return back()->with('success', __('Gateway status: :status', ['status' => $tx->status->label()]));
    }

    public function refund(Request $request, PaymentTransaction $transaction, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:'.$transaction->amount],
            'reason' => ['required', 'string', 'max:255'],
        ]);
        $payments->refund($transaction, $request->user(), (float) $data['amount'], $data['reason']);

        return back()->with('success', __('Refund processed.'));
    }

    /** Summary report for a date range. */
    public function reports(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now();
        $base = fn () => PaymentTransaction::whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        return view('admin.payments.reports', [
            'from' => $from, 'to' => $to,
            'byStatus' => $base()->select('status', DB::raw('count(*) as total'), DB::raw('sum(amount) as amount'))->groupBy('status')->get(),
            'byPurpose' => $base()->where('status', 'completed')->select('purpose', DB::raw('count(*) as total'), DB::raw('sum(amount) as amount'))->groupBy('purpose')->get(),
            'byMethod' => $base()->where('status', 'completed')->select('payment_method', DB::raw('count(*) as total'), DB::raw('sum(amount) as amount'))->groupBy('payment_method')->get(),
            'daily' => $base()->where('status', 'completed')->select(DB::raw('DATE(paid_at) as day'), DB::raw('count(*) as total'), DB::raw('sum(amount) as amount'))->groupBy('day')->orderBy('day')->get(),
        ]);
    }

    /** CSV export (opens directly in Excel – UTF-8 with BOM so Bangla names display correctly). */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->with('user')->latest();
        ActivityLogger::log('payment.exported', null, 'Payments exported to CSV', $request->only('status', 'purpose', 'from', 'to', 'search'));

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Reference', 'Date', 'Customer', 'Phone', 'Purpose', 'Amount', 'Status', 'Method', 'Transaction ID', 'Invoice ID', 'Paid At', 'Refunded']);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $tx) {
                    fputcsv($out, [
                        $tx->reference, $tx->created_at->format('Y-m-d H:i'), $tx->user?->name, $tx->user?->phone, $tx->purpose,
                        $tx->amount, $tx->status->value, $tx->payment_method, $tx->gateway_transaction_id, $tx->gateway_invoice_id,
                        optional($tx->paid_at)->format('Y-m-d H:i'), $tx->refunded_amount,
                    ]);
                }
            });
            fclose($out);
        }, 'payments-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request): Builder
    {
        $q = PaymentTransaction::query()->search($request->query('search'));
        foreach (['status', 'purpose'] as $f) {
            if ($v = $request->query($f)) {
                $q->where($f, $v);
            }
        }
        if ($from = $request->query('from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        return $q;
    }
}
