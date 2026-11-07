<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

/**
 * Starter manual payment methods. Edit the numbers in Admin → Payment Methods.
 * These are only shown to customers when UddoktaPay is NOT configured.
 */
class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        if (PaymentMethod::exists()) {
            return;
        }

        $methods = [
            ['name' => 'bKash', 'type' => 'bkash', 'account_kind' => 'personal'],
            ['name' => 'Nagad', 'type' => 'nagad', 'account_kind' => 'personal'],
            ['name' => 'Rocket', 'type' => 'rocket', 'account_kind' => 'personal'],
            ['name' => 'Upay', 'type' => 'upay', 'account_kind' => 'personal'],
        ];

        foreach ($methods as $i => $m) {
            PaymentMethod::create($m + [
                'account_number' => '',
                'instructions' => 'Send Money to this number, then enter the Transaction ID below.',
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }
}
