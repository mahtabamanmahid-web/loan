<?php

namespace App\Services;

use App\Models\LoanProduct;
use App\Models\LoanTerm;

/** Active loan packages in the shape the JavaScript calculator expects. */
class ProductCatalog
{
    public function forCalculator(): array
    {
        $globalMin = (float) sys_setting('min_loan_amount');
        $globalMax = (float) sys_setting('max_loan_amount');

        return LoanProduct::active()->with('activeTerms')->get()
            ->filter(fn (LoanProduct $p) => $p->activeTerms->isNotEmpty())
            ->map(fn (LoanProduct $p) => [
                'id' => $p->id,
                'name' => $p->displayName(),
                'icon' => $p->icon,
                'min_amount' => max((float) $p->min_amount, $globalMin),
                'max_amount' => min((float) $p->max_amount, $globalMax),
                'repayment_frequency' => $p->repayment_frequency,
                'processing_fee' => [
                    'type' => $p->processing_fee_type,
                    'value' => (float) $p->processing_fee_value,
                    'collection' => $p->fee_collection,
                ],
                'terms' => $p->activeTerms->map(fn (LoanTerm $t) => [
                    'id' => $t->id, 'months' => $t->months, 'interest_rate' => (float) $t->interest_rate,
                ])->values()->all(),
            ])->values()->all();
    }

    /** Preset amount buttons (Admin → Loan Settings). */
    public function presets(): array
    {
        return collect(explode(',', (string) sys_setting('loan_amount_presets')))
            ->map(fn ($v) => (int) trim($v))->filter()->values()->all();
    }
}
