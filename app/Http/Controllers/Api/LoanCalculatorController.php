<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoanProduct;
use App\Models\LoanTerm;
use App\Services\LoanCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Public JSON endpoints for the loan & eligibility calculators. */
class LoanCalculatorController extends Controller
{
    public function __construct(private LoanCalculator $calculator) {}

    /** GET /api/v1/loan-products */
    public function products(): JsonResponse
    {
        $products = LoanProduct::active()->with('activeTerms')->get()->map(fn (LoanProduct $p) => [
            'id' => $p->id,
            'name' => $p->displayName(),
            'description' => $p->displayDescription(),
            'min_amount' => max((float) $p->min_amount, (float) sys_setting('min_loan_amount')),
            'max_amount' => min((float) $p->max_amount, (float) sys_setting('max_loan_amount')),
            'repayment_frequency' => $p->repayment_frequency,
            'processing_fee' => ['type' => $p->processing_fee_type, 'value' => (float) $p->processing_fee_value, 'collection' => $p->fee_collection],
            'terms' => $p->activeTerms->map(fn (LoanTerm $t) => ['id' => $t->id, 'months' => $t->months, 'interest_rate' => (float) $t->interest_rate])->values(),
        ]);

        return response()->json(['data' => $products]);
    }

    /** GET /api/v1/calculate?product_id=1&term_id=2&amount=20000 */
    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:loan_products,id',
            'term_id' => 'required|integer|exists:loan_terms,id',
            'amount' => 'required|numeric|min:1|max:100000000',
        ]);

        $product = LoanProduct::active()->findOrFail($data['product_id']);
        $term = $product->activeTerms()->findOrFail($data['term_id']);

        return response()->json(['data' => $this->calculator->calculate((float) $data['amount'], $term->months, (float) $term->interest_rate, $product)]);
    }

    /** POST /api/v1/eligibility  {monthly_income, monthly_obligations, amount, months} */
    public function eligibility(Request $request): JsonResponse
    {
        $data = $request->validate([
            'monthly_income' => 'required|numeric|min:0|max:100000000',
            'monthly_obligations' => 'nullable|numeric|min:0|max:100000000',
            'amount' => 'required|numeric|min:0|max:100000000',
            'months' => 'required|integer|min:1|max:120',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $rate = (float) ($data['interest_rate'] ?? sys_setting('default_interest_rate'));

        return response()->json(['data' => $this->calculator->eligibility(
            (float) $data['monthly_income'], (float) ($data['monthly_obligations'] ?? 0), (float) $data['amount'], (int) $data['months'], $rate
        )]);
    }
}
