<?php

namespace App\Services;

use App\Models\LoanProduct;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Pure calculation logic – no database writes.
 *
 * Interest model: FLAT annual rate (common for consumer/micro loans in Bangladesh).
 *     total interest = principal × (rate / 100) × (months / 12)
 *     installment    = (principal + total interest) / number of installments
 *
 * The same class powers the public calculator, the API, the application form
 * and the actual installment schedule, so the customer always sees the exact
 * numbers that will be charged.
 */
class LoanCalculator
{
    /**
     * @return array{amount: float, months: int, interest_rate: float, total_interest: float, total_payable: float,
     *               installments: int, installment_amount: float, processing_fee: float, fee_collection: string,
     *               net_disbursement: float, frequency: string}
     */
    public function calculate(float $amount, int $months, float $annualRate, ?LoanProduct $product = null): array
    {
        $amount = round(max($amount, 0), 2);
        $months = max($months, 1);
        $frequency = $product?->repayment_frequency ?? 'monthly';

        $totalInterest = round($amount * ($annualRate / 100) * ($months / 12), 2);
        $totalPayable = round($amount + $totalInterest, 2);
        $count = $this->installmentCount($months, $frequency);
        $installment = round($totalPayable / $count, 2);

        $fee = $product ? $product->feeFor($amount) : 0.0;
        $feeCollection = $product?->fee_collection ?? 'deduct';

        return [
            'amount' => $amount,
            'months' => $months,
            'interest_rate' => round($annualRate, 2),
            'total_interest' => $totalInterest,
            'total_payable' => $totalPayable,
            'installments' => $count,
            'installment_amount' => $installment,
            'processing_fee' => $fee,
            'fee_collection' => $feeCollection,
            'net_disbursement' => $feeCollection === 'deduct' ? round($amount - $fee, 2) : $amount,
            'frequency' => $frequency,
        ];
    }

    public function installmentCount(int $months, string $frequency = 'monthly'): int
    {
        return $frequency === 'weekly' ? (int) round($months * 52 / 12) : $months;
    }

    /**
     * Build the repayment schedule. The last installment absorbs rounding
     * differences so the sum always equals total_payable exactly.
     *
     * @return list<array{installment_number: int, due_date: string, principal_component: float, interest_component: float, amount: float}>
     */
    public function schedule(float $amount, int $months, float $annualRate, CarbonInterface $firstDueDate, string $frequency = 'monthly'): array
    {
        $calc = $this->calculate($amount, $months, $annualRate);
        $count = $this->installmentCount($months, $frequency);
        $principalEach = round($amount / $count, 2);
        $interestEach = round($calc['total_interest'] / $count, 2);

        $rows = [];
        $principalSum = 0.0;
        $interestSum = 0.0;
        $date = Carbon::parse($firstDueDate);

        for ($i = 1; $i <= $count; $i++) {
            $isLast = $i === $count;
            $principal = $isLast ? round($amount - $principalSum, 2) : $principalEach;
            $interest = $isLast ? round($calc['total_interest'] - $interestSum, 2) : $interestEach;
            $principalSum += $principal;
            $interestSum += $interest;

            $rows[] = [
                'installment_number' => $i,
                'due_date' => $date->toDateString(),
                'principal_component' => $principal,
                'interest_component' => $interest,
                'amount' => round($principal + $interest, 2),
            ];

            $date = $frequency === 'weekly' ? $date->copy()->addWeek() : $date->copy()->addMonthNoOverflow();
        }

        return $rows;
    }

    /**
     * Smart eligibility check – an *estimate* only, never a guarantee of approval.
     *
     * @return array{eligible: bool, max_amount: float, max_installment: float, reasons: list<string>}
     */
    public function eligibility(float $monthlyIncome, float $monthlyObligations, float $requestedAmount, int $months, float $annualRate): array
    {
        $min = (float) sys_setting('min_loan_amount');
        $max = (float) sys_setting('max_loan_amount');
        $minIncome = (float) sys_setting('min_monthly_income');
        $multiplier = (float) sys_setting('max_income_multiplier');

        $reasons = [];
        // Rule 1: installment should not exceed 40% of disposable income
        $disposable = max($monthlyIncome - $monthlyObligations, 0);
        $maxInstallment = round($disposable * 0.40, 2);
        // Rule 2: amount capped at income × multiplier and system maximum
        $capByIncome = $monthlyIncome * $multiplier;
        // Largest principal whose installment fits rule 1
        $factor = (1 + ($annualRate / 100) * ($months / 12)) / max($months, 1);
        $capByInstallment = $factor > 0 ? $maxInstallment / $factor : 0;
        $maxAmount = floor(min($max, $capByIncome, $capByInstallment) / 500) * 500;

        if ($monthlyIncome < $minIncome) {
            $reasons[] = __('Minimum monthly income required is :amount.', ['amount' => money($minIncome)]);
        }
        if ($maxAmount < $min) {
            $reasons[] = __('Your current income supports less than the minimum loan amount.');
        }
        if ($requestedAmount > $maxAmount && $maxAmount >= $min) {
            $reasons[] = __('Requested amount is higher than your estimated limit of :amount.', ['amount' => money($maxAmount)]);
        }

        return [
            'eligible' => $reasons === [],
            'max_amount' => max($maxAmount, 0),
            'max_installment' => $maxInstallment,
            'reasons' => $reasons,
        ];
    }
}
