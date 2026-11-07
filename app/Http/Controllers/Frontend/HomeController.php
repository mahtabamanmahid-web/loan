<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\LoanProduct;
use App\Models\Review;
use App\Services\ProductCatalog;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(ProductCatalog $catalog): View
    {
        // Order & visibility of homepage sections is controlled from Admin → Website
        $sections = json_decode((string) setting('home_sections'), true) ?: [];

        return view('frontend.home', [
            'sections' => $sections,
            'products' => LoanProduct::active()->with('activeTerms')->get(),
            'calculatorProducts' => $catalog->forCalculator(),
            'presets' => $catalog->presets(),
            'reviews' => Review::public()->limit(9)->get(),   // only verified + published
            'faqs' => Faq::active()->get(),
        ]);
    }

    public function calculator(ProductCatalog $catalog): View
    {
        return view('frontend.calculator', [
            'calculatorProducts' => $catalog->forCalculator(),
            'presets' => $catalog->presets(),
        ]);
    }
}
