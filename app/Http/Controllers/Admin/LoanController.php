<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InstallmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\ActivityLogger;
use App\Services\LoanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $q = Loan::with('user', 'application.product');

        if ($s = trim((string) $request->query('search'))) {
            $q->where(fn ($q) => $q->where('loan_number', 'like', "%$s%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%")));
        }
        if ($status = $request->query('status')) {
            $q->where('status', $status);
        }
        if ($request->boolean('overdue')) {
            $q->whereHas('installments', fn ($i) => $i->where('status', InstallmentStatus::Overdue->value));
        }

        return view('admin.loans.index', ['loans' => $q->latest()->paginate(20)->withQueryString()]);
    }

    public function show(Loan $loan): View
    {
        return view('admin.loans.show', ['loan' => $loan->load('user', 'application.product', 'installments', 'repayments.recorder', 'disbursedBy')]);
    }

    /** Record a repayment received outside the gateway (cash / bank / MFS). */
    public function recordRepayment(Request $request, Loan $loan, LoanService $loans): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', Rule::in(['cash', 'bank', 'bkash', 'nagad', 'rocket', 'cheque'])],
            'reference' => ['required', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $repayment = $loans->recordRepayment($loan, (float) $data['amount'], $data['method'], $data['reference'], null, $request->user(), $data['note'] ?? null);
        ActivityLogger::log('loan.repayment_recorded', $loan, "Manual repayment {$repayment->amount} ({$data['method']})", ['reference' => $data['reference']]);

        return back()->with('success', __('Repayment recorded.'));
    }
}
