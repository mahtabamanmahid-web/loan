<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use App\Services\LoanCalculator;
use App\Services\LoanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Loan application review workflow for staff. */
class ApplicationController extends Controller
{
    public function __construct(private LoanService $loans) {}

    public function index(Request $request): View
    {
        $q = LoanApplication::with('product', 'user.latestDocument')->search($request->query('search'));

        if ($status = $request->query('status')) {
            $q->where('status', $status);
        }
        if ($from = $request->query('from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        return view('admin.applications.index', [
            'applications' => $q->latest()->paginate(20)->withQueryString(),
            'statuses' => ApplicationStatus::cases(),
        ]);
    }

    public function show(LoanApplication $application, LoanCalculator $calculator): View
    {
        $application->load('product.terms', 'term', 'user.latestDocument', 'user.loans', 'agreement', 'loan', 'payments', 'reviewer');

        return view('admin.applications.show', [
            'application' => $application,
            'eligibility' => $calculator->eligibility((float) $application->monthly_income, 0, $application->finalAmount(), $application->finalMonths(), $application->finalRate()),
            'previousApplications' => LoanApplication::where('user_id', $application->user_id)->whereKeyNot($application->id)->latest()->limit(5)->get(),
        ]);
    }

    public function startReview(Request $request, LoanApplication $application): RedirectResponse
    {
        $this->loans->startReview($application, $request->user());

        return back()->with('success', __('Application moved to review.'));
    }

    public function requestDocuments(Request $request, LoanApplication $application): RedirectResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:500']]);
        $this->loans->requestDocuments($application, $request->user(), $data['message']);

        return back()->with('success', __('Customer has been asked for more documents.'));
    }

    public function approve(Request $request, LoanApplication $application): RedirectResponse
    {
        $data = $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:1'],
            'approved_months' => ['required', 'integer', 'min:1', 'max:120'],
            'approved_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->loans->approve($application, $request->user(), (float) $data['approved_amount'], (int) $data['approved_months'], (float) $data['approved_interest_rate'], $data['note'] ?? null);

        return back()->with('success', __('Application approved. Next: create the agreement.'));
    }

    public function reject(Request $request, LoanApplication $application): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $this->loans->reject($application, $request->user(), $data['reason']);

        return back()->with('success', __('Application rejected and customer notified.'));
    }

    public function createAgreement(Request $request, LoanApplication $application): RedirectResponse
    {
        $this->loans->createAgreement($application, $request->user());

        return back()->with('success', __('Agreement created and sent to the customer.'));
    }

    /** Record a disbursement that has REALLY been sent (bank / MFS reference required). */
    public function disburse(Request $request, LoanApplication $application): RedirectResponse
    {
        $data = $request->validate([
            'method' => ['required', Rule::in(['bank', 'bkash', 'nagad', 'rocket', 'cash', 'cheque'])],
            'account' => ['nullable', 'string', 'max:100'],
            'reference' => ['required', 'string', 'max:100', 'unique:loans,disbursement_reference'],
            'disbursed_at' => ['required', 'date', 'before_or_equal:now'],
            'first_due_date' => ['required', 'date', 'after:disbursed_at'],
            'note' => ['nullable', 'string', 'max:500'],
            'confirm' => ['accepted'],
        ], [
            'reference.unique' => __('This transfer reference was already used for another loan.'),
            'confirm.accepted' => __('Please confirm that the money has actually been sent.'),
        ]);

        $loan = $this->loans->disburse($application, $request->user(), $data);

        return redirect()->route('admin.loans.show', $loan)->with('success', __('Disbursement recorded. Loan :no is now active.', ['no' => $loan->loan_number]));
    }

    public function pdf(LoanApplication $application): Response
    {
        return Pdf::loadView('pdf.application', ['application' => $application->load('product', 'user')])
            ->download($application->reference.'.pdf');
    }
}
