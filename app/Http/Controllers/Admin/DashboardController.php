<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus as S;
use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\LoanInstallment;
use App\Models\LoanRepayment;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);

        $statusCounts = LoanApplication::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $count = fn (array $statuses) => collect($statuses)->sum(fn ($s) => (int) ($statusCounts[$s->value] ?? 0));

        return view('admin.dashboard', [
            'stats' => [
                'users' => User::whereNull('admin_role_id')->count(),
                'applications' => $statusCounts->sum(),
                'pending' => $count([S::Submitted, S::UnderReview, S::NidPending, S::DocumentsRequested]),
                'approved' => $count([S::Approved, S::AwaitingAgreement, S::Disbursed, S::Active, S::Completed]),
                'rejected' => $count([S::Rejected]),
                'active_loans' => Loan::where('status', LoanStatus::Active->value)->count(),
                'disbursed' => (float) Loan::sum('disbursed_amount'),
                'repaid' => (float) LoanRepayment::sum('amount'),
                'pending_payments' => PaymentTransaction::whereIn('status', [PaymentStatus::Pending->value, PaymentStatus::Initiated->value])->count(),
                'successful_payments' => PaymentTransaction::where('status', PaymentStatus::Completed->value)->count(),
                'successful_amount' => (float) PaymentTransaction::where('status', PaymentStatus::Completed->value)->sum('amount'),
                'pending_kyc' => UserDocument::where('status', 'pending')->count(),
                'overdue' => LoanInstallment::where('status', InstallmentStatus::Overdue->value)->count(),
            ],
            'recentApplications' => LoanApplication::with('product')->latest()->limit(8)->get(),
            'recentPayments' => PaymentTransaction::with('user')->latest()->limit(8)->get(),
            'awaitingDisbursement' => LoanApplication::where('status', S::AwaitingAgreement->value)
                ->whereHas('agreement', fn ($q) => $q->whereNotNull('accepted_at'))->with('agreement')->limit(5)->get(),
        ]);
    }
}
