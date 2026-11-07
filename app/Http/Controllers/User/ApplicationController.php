<?php

namespace App\Http\Controllers\User;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreLoanApplicationRequest;
use App\Models\LoanApplication;
use App\Models\LoanProduct;
use App\Services\LoanService;
use App\Services\ProductCatalog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(private LoanService $loans) {}

    public function index(): View
    {
        return view('user.applications.index', [
            'applications' => auth()->user()->applications()->with('product')->paginate(10),
        ]);
    }

    /** The 4-step application wizard: Amount & Duration → Details → Review → Terms. */
    public function create(Request $request, ProductCatalog $catalog): View|RedirectResponse
    {
        $user = $request->user()->load('profile', 'latestDocument');

        // Limit on applications in progress (Admin → Loan Settings)
        $open = $user->applications()->whereIn('status', ApplicationStatus::open())->get();
        if ($open->count() >= (int) sys_setting('max_open_applications')) {
            return redirect()->route('user.applications.show', $open->first())
                ->with('info', __('You already have an application in progress.'));
        }

        // NID is collected AFTER the loan application is submitted (not before).
        $products = $catalog->forCalculator();

        return view('user.applications.create', [
            'user' => $user,
            'calculatorProducts' => $products,
            'presets' => $catalog->presets(),
            'disclosures' => LoanProduct::active()->get()->keyBy('id'),
            'initial' => [
                'productId' => (int) $request->query('product') ?: null,
                'termId' => (int) $request->query('term') ?: null,
                'amount' => (int) $request->query('amount') ?: null,
            ],
        ]);
    }

    public function store(StoreLoanApplicationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $product = LoanProduct::active()->findOrFail($data['loan_product_id']);
        $term = $product->activeTerms()->findOrFail($data['loan_term_id']);
        $user = $request->user();

        $application = $this->loans->submit($user, $product, $term, (float) $data['amount'], [
            ...$data,
            'phone' => $user->phone,
            'email' => $user->email,
        ], $request->ip());

        return redirect()->route('user.applications.show', $application)
            ->with('success', __('Your application has been submitted! Reference: :ref', ['ref' => $application->reference]));
    }

    /** Application status page with timeline, agreement and payment actions. */
    public function show(LoanApplication $application): View
    {
        $this->ensureOwner($application);

        return view('user.applications.show', [
            'application' => $application->load('product', 'agreement', 'loan', 'payments'),
        ]);
    }

    public function cancel(LoanApplication $application): RedirectResponse
    {
        $this->ensureOwner($application);
        $this->loans->cancel($application);

        return redirect()->route('user.applications.index')->with('success', __('Application cancelled.'));
    }

    /** Printable / downloadable application form. */
    public function pdf(LoanApplication $application): Response
    {
        $this->ensureOwner($application);

        return Pdf::loadView('pdf.application', ['application' => $application->load('product', 'user')])
            ->download($application->reference.'.pdf');
    }

    public function agreement(LoanApplication $application): View|RedirectResponse
    {
        $this->ensureOwner($application);
        if (! $application->agreement) {
            return redirect()->route('user.applications.show', $application)->with('info', __('Your agreement is not ready yet.'));
        }

        return view('user.applications.agreement', ['application' => $application->load('agreement', 'product')]);
    }

    public function acceptAgreement(Request $request, LoanApplication $application): RedirectResponse
    {
        $this->ensureOwner($application);
        $request->validate([
            'accept_agreement' => ['accepted'],
            'signature_name' => ['required', 'string', 'max:100'],
        ], ['accept_agreement.accepted' => __('Please tick the box to accept the agreement.')]);

        // Typed name must match the applicant name (simple e-signature)
        if (mb_strtolower(trim($request->signature_name)) !== mb_strtolower(trim($application->applicant_name))) {
            return back()->withErrors(['signature_name' => __('Please type your full name exactly as in the application.')]);
        }

        $this->loans->acceptAgreement($application, $request->ip());

        return redirect()->route('user.applications.show', $application)->with('success', __('Agreement accepted.'));
    }

    public function agreementPdf(LoanApplication $application): Response
    {
        $this->ensureOwner($application);
        abort_unless($application->agreement, 404);

        return Pdf::loadView('pdf.agreement', ['application' => $application->load('agreement', 'product')])
            ->download($application->agreement->agreement_number.'.pdf');
    }
}
