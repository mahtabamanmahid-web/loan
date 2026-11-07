<?php

namespace Tests;

use App\Enums\ApplicationStatus;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\LoanProduct;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Base test case: fresh SQLite in-memory DB, seeded with roles/permissions,
 * plus helpers to move a loan through its life-cycle.
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function customer(bool $kyc = true): User
    {
        return $kyc ? User::factory()->kycApproved()->create() : User::factory()->create();
    }

    protected function admin(string $role = 'super_admin'): User
    {
        return User::factory()->admin($role)->create()->fresh();
    }

    protected function product(array $attrs = []): LoanProduct
    {
        return LoanProduct::factory()->withTerms()->create($attrs)->load('terms');
    }

    /** Valid application form payload for POST /apply. */
    protected function applicationPayload(LoanProduct $product, float $amount = 20000, int $months = 6): array
    {
        return [
            'loan_product_id' => $product->id,
            'loan_term_id' => $product->terms->firstWhere('months', $months)->id,
            'amount' => $amount,
            'applicant_name' => 'Rahim Uddin',
            'occupation' => 'business',
            'purpose' => 'business',
            'accept_terms' => '1',
        ];
    }

    protected function submittedApplication(?User $user = null, ?LoanProduct $product = null, float $amount = 20000): LoanApplication
    {
        $user ??= $this->customer();
        $product ??= $this->product();
        $term = $product->terms->firstWhere('months', 6);

        return app(LoanService::class)->submit($user, $product, $term, $amount, [
            ...$this->applicationPayload($product, $amount),
            'phone' => $user->phone,
            'email' => $user->email,
        ], '127.0.0.1');
    }

    /** Application that is approved, has an agreement and the customer accepted it. */
    protected function signedApplication(?LoanProduct $product = null, float $amount = 20000): LoanApplication
    {
        $admin = $this->admin();
        $app = $this->submittedApplication(null, $product, $amount);
        $svc = app(LoanService::class);
        $svc->approve($app, $admin, $amount, 6, 12, null);
        $svc->createAgreement($app->fresh(), $admin);
        $svc->acceptAgreement($app->fresh(), '127.0.0.1');

        return $app->fresh(['agreement', 'product', 'user']);
    }

    protected function activeLoan(float $amount = 12000): Loan
    {
        $app = $this->signedApplication(null, $amount);

        return app(LoanService::class)->disburse($app, $this->admin(), [
            'method' => 'bkash',
            'reference' => 'TRX'.uniqid(),
            'disbursed_at' => now()->toDateTimeString(),
            'first_due_date' => now()->addMonth()->toDateString(),
        ])->fresh(['installments', 'user']);
    }

    protected function assertStatus(LoanApplication $app, ApplicationStatus $status): void
    {
        $this->assertSame($status, $app->fresh()->status);
    }
}
