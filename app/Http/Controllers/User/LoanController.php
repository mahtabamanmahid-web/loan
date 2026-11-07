<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(): View
    {
        return view('user.loans.index', ['loans' => auth()->user()->loans()->with('application.product')->get()]);
    }

    /** Loan detail: repayment progress, installment schedule and payment buttons. */
    public function show(Loan $loan): View
    {
        $this->ensureOwner($loan);

        return view('user.loans.show', [
            'loan' => $loan->load('installments', 'repayments', 'application.product'),
            'next' => $loan->nextInstallment(),
        ]);
    }
}
