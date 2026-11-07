<?php

namespace Tests\Unit;

use App\Models\LoanProduct;
use App\Services\LoanCalculator;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** Loan & installment calculation tests (flat annual interest). */
class LoanCalculatorTest extends TestCase
{
    private LoanCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new LoanCalculator;
    }

    public function test_flat_interest_calculation(): void
    {
        // 20,000 at 12% for 6 months → interest 1,200, total 21,200, 6 × 3,533.33
        $r = $this->calc->calculate(20000, 6, 12);

        $this->assertSame(1200.0, $r['total_interest']);
        $this->assertSame(21200.0, $r['total_payable']);
        $this->assertSame(6, $r['installments']);
        $this->assertSame(3533.33, $r['installment_amount']);
    }

    public function test_percent_fee_deducted_from_disbursement(): void
    {
        $product = new LoanProduct(['processing_fee_type' => 'percent', 'processing_fee_value' => 2, 'fee_collection' => 'deduct', 'repayment_frequency' => 'monthly']);
        $r = $this->calc->calculate(50000, 12, 10, $product);

        $this->assertSame(1000.0, $r['processing_fee']);
        $this->assertSame(49000.0, $r['net_disbursement']);
        $this->assertSame(5000.0, $r['total_interest']);
    }

    public function test_fixed_upfront_fee_is_not_deducted(): void
    {
        $product = new LoanProduct(['processing_fee_type' => 'fixed', 'processing_fee_value' => 300, 'fee_collection' => 'upfront', 'repayment_frequency' => 'monthly']);
        $r = $this->calc->calculate(10000, 3, 12, $product);

        $this->assertSame(300.0, $r['processing_fee']);
        $this->assertSame(10000.0, $r['net_disbursement']);
    }

    public function test_weekly_frequency_installment_count(): void
    {
        $product = new LoanProduct(['repayment_frequency' => 'weekly', 'processing_fee_type' => 'fixed', 'processing_fee_value' => 0]);
        $r = $this->calc->calculate(12000, 3, 12, $product);

        $this->assertSame(13, $r['installments']);
    }

    public function test_schedule_sums_exactly_to_total_payable(): void
    {
        $rows = $this->calc->schedule(10000, 3, 13, Carbon::parse('2026-01-31'));
        $total = $this->calc->calculate(10000, 3, 13)['total_payable'];

        $this->assertCount(3, $rows);
        $this->assertEqualsWithDelta($total, array_sum(array_column($rows, 'amount')), 0.001);
        $this->assertEqualsWithDelta(10000, array_sum(array_column($rows, 'principal_component')), 0.001);
        // Month-end dates must not overflow (Jan 31 → Feb 28 → Mar 28)
        $this->assertSame('2026-02-28', $rows[1]['due_date']);
    }

    public function test_zero_interest(): void
    {
        $r = $this->calc->calculate(9000, 3, 0);
        $this->assertSame(0.0, $r['total_interest']);
        $this->assertSame(3000.0, $r['installment_amount']);
    }

    public function test_eligibility_estimate(): void
    {
        $ok = $this->calc->eligibility(50000, 0, 20000, 6, 12);
        $this->assertTrue($ok['eligible']);
        $this->assertGreaterThanOrEqual(20000, $ok['max_amount']);

        $low = $this->calc->eligibility(5000, 0, 20000, 6, 12);
        $this->assertFalse($low['eligible']);
        $this->assertNotEmpty($low['reasons']);
    }

    public function test_api_calculate_endpoint_matches_service(): void
    {
        $product = $this->product();
        $term = $product->terms->firstWhere('months', 6);

        $this->getJson("/api/v1/calculate?product_id={$product->id}&term_id={$term->id}&amount=20000")
            ->assertOk()
            ->assertJsonPath('data.total_payable', 21200)
            ->assertJsonPath('data.processing_fee', 400);
    }
}
