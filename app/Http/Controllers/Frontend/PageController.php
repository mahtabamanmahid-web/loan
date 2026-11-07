<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\LoanProduct;
use Illuminate\View\View;

/** Legal pages – content managed in Admin → Website. */
class PageController extends Controller
{
    /** Loan Terms & Fee Disclosure – every fee shown BEFORE any agreement or payment. */
    public function loanTerms(): View
    {
        return view('frontend.loan-terms', [
            'products' => LoanProduct::active()->with('activeTerms')->get(),
            'content' => setting('loan_terms_disclosure'),
        ]);
    }

    public function terms(): View
    {
        return view('frontend.page', ['title' => __('Terms & Conditions'), 'content' => setting('terms_conditions')]);
    }

    public function privacy(): View
    {
        return view('frontend.page', ['title' => __('Privacy Policy'), 'content' => setting('privacy_policy')]);
    }
}
