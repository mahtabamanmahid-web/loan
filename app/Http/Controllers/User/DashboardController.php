<?php

namespace App\Http\Controllers\User;

use App\Enums\ApplicationStatus;
use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Models\LoanInstallment;
use Illuminate\View\View;

/** Customer home: every number comes from the database. */
class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user()->load('latestDocument');

        $loans = $user->loans()->with('installments')->get();
        $activeLoans = $loans->where('status', LoanStatus::Active);

        $nextInstallment = LoanInstallment::query()
            ->whereIn('loan_id', $activeLoans->pluck('id'))
            ->where('status', '!=', InstallmentStatus::Paid->value)
            ->orderBy('due_date')->with('loan')->first();

        return view('user.dashboard', [
            'user' => $user,
            'stats' => [
                'total_approved' => $loans->sum(fn ($l) => (float) $l->principal),
                'current_balance' => $activeLoans->sum(fn ($l) => $l->outstanding()),
                'total_paid' => $loans->sum(fn ($l) => (float) $l->total_paid),
                'remaining_installments' => $activeLoans->sum(fn ($l) => $l->installments->where('status', '!=', InstallmentStatus::Paid)->count()),
            ],
            'nextInstallment' => $nextInstallment,
            'activeLoans' => $activeLoans,
            'openApplications' => $user->applications()->whereIn('status', [...ApplicationStatus::open(), ApplicationStatus::Disbursed->value])->with('product')->get(),
            'recentTransactions' => $user->transactions()->limit(5)->get(),
            'canReview' => $loans->isNotEmpty() && ! \App\Models\Review::where('user_id', $user->id)->exists(),
        ]);
    }
}
