<?php

namespace Database\Factories;

use App\Models\LoanProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Test-only factory.
 *
 * @extends Factory<LoanProduct>
 */
class LoanProductFactory extends Factory
{
    protected $model = LoanProduct::class;

    public function definition(): array
    {
        $name = 'Test Loan '.Str::random(4);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'min_amount' => 5000,
            'max_amount' => 100000,
            'repayment_frequency' => 'monthly',
            'processing_fee_type' => 'percent',
            'processing_fee_value' => 2,
            'fee_collection' => 'deduct',
            'late_fee_per_installment' => 100,
            'grace_days' => 3,
            'is_active' => true,
        ];
    }

    public function upfrontFee(): static
    {
        return $this->state(fn () => ['fee_collection' => 'upfront']);
    }

    /** Adds a 6-month term at 12% (and 12-month at 14%). */
    public function withTerms(): static
    {
        return $this->afterCreating(function (LoanProduct $p) {
            $p->terms()->createMany([['months' => 6, 'interest_rate' => 12], ['months' => 12, 'interest_rate' => 14]]);
        });
    }
}
