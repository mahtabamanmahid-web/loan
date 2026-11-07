<?php

namespace Database\Seeders;

use App\Models\LoanProduct;
use Illuminate\Database\Seeder;

/**
 * Starter loan packages so the site works after installation.
 * These are CONFIGURATION, not customer data – review and change every rate,
 * fee and limit in Admin → Loan Packages to match your licensed product terms.
 */
class LoanProductSeeder extends Seeder
{
    public function run(): void
    {
        if (LoanProduct::exists()) {
            return;
        }

        $products = [
            [
                'name' => 'Personal Loan', 'name_bn' => 'ব্যক্তিগত ঋণ', 'slug' => 'personal-loan', 'icon' => 'user-round',
                'description' => 'For personal and family needs.', 'description_bn' => 'ব্যক্তিগত ও পারিবারিক প্রয়োজনে।',
                'min_amount' => 5000, 'max_amount' => 50000, 'processing_fee_value' => 1,
                'terms' => [3 => 10, 6 => 11, 9 => 12, 12 => 13],
            ],
            [
                'name' => 'Small Business Loan', 'name_bn' => 'ক্ষুদ্র ব্যবসা ঋণ', 'slug' => 'small-business-loan', 'icon' => 'store',
                'description' => 'Working capital for small shops and businesses.', 'description_bn' => 'ছোট দোকান ও ব্যবসার মূলধনের জন্য।',
                'min_amount' => 10000, 'max_amount' => 100000, 'processing_fee_value' => 1.5,
                'terms' => [6 => 11, 9 => 12, 12 => 12.5],
            ],
        ];

        foreach ($products as $i => $p) {
            $terms = $p['terms'];
            unset($p['terms']);

            $product = LoanProduct::create($p + [
                'processing_fee_type' => 'percent',
                'fee_collection' => 'deduct',
                'fee_refundable' => false,
                'fee_purpose' => 'Covers application processing, KYC verification and documentation.',
                'fee_refund_policy' => 'Deducted from the disbursed amount only when the loan is disbursed. Nothing is charged if the application is rejected.',
                'late_fee_per_installment' => 0,
                'grace_days' => 3,
                'sort_order' => $i,
            ]);

            foreach ($terms as $months => $rate) {
                $product->terms()->create(['months' => $months, 'interest_rate' => $rate]);
            }
        }
    }
}
