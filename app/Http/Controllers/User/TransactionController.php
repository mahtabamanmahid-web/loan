<?php

namespace App\Http\Controllers\User;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /** All / successful / pending / failed tabs. */
    public function index(Request $request): View
    {
        $tab = $request->query('status', 'all');
        $query = auth()->user()->transactions();

        match ($tab) {
            'completed' => $query->where('status', PaymentStatus::Completed->value),
            'pending' => $query->whereIn('status', [PaymentStatus::Pending->value, PaymentStatus::Initiated->value]),
            'failed' => $query->whereIn('status', [PaymentStatus::Failed->value, PaymentStatus::Cancelled->value]),
            default => null,
        };

        return view('user.transactions.index', [
            'transactions' => $query->paginate(15)->withQueryString(),
            'tab' => $tab,
        ]);
    }

    public function show(PaymentTransaction $transaction): View
    {
        $this->ensureOwner($transaction);

        return view('user.transactions.show', ['tx' => $transaction->load('application', 'loan', 'installment')]);
    }

    /** PDF receipt – only for successful (or refunded) payments. */
    public function receipt(PaymentTransaction $transaction): Response
    {
        $this->ensureOwner($transaction);
        abort_unless(in_array($transaction->status, [PaymentStatus::Completed, PaymentStatus::Refunded], true), 404);

        return Pdf::loadView('pdf.receipt', ['tx' => $transaction->load('user', 'application', 'loan', 'installment')])
            ->download('receipt-'.$transaction->reference.'.pdf');
    }
}
