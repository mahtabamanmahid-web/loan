<?php

namespace Tests\Feature\Payment;

use App\Enums\PaymentStatus;
use App\Models\LoanApplication;
use App\Models\LoanProduct;
use App\Models\PaymentTransaction;
use Tests\TestCase;

/** Manual deposit payment (bKash/Nagad/...) used when UddoktaPay is not configured. */
class ManualPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // No online gateway configured → manual flow is used.
        config(['uddoktapay.api_key' => null]);
    }

    private function upfrontApplication(): LoanApplication
    {
        $product = LoanProduct::factory()->upfrontFee()->withTerms()->create()->load('terms');

        return $this->signedApplication($product, 20000);
    }

    public function test_paying_deposit_goes_to_manual_page_when_no_gateway(): void
    {
        $app = $this->upfrontApplication();

        $this->actingAs($app->user)->post(route('user.applications.pay-fee', $app))
            ->assertRedirectContains('/payment/manual/');

        $tx = PaymentTransaction::firstOrFail();
        $this->assertSame('manual', $tx->gateway);
        $this->assertSame(PaymentStatus::Initiated, $tx->status);

        // The manual page lists the active methods seeded by PaymentMethodSeeder
        $this->actingAs($app->user)->get(route('payment.manual', $tx))->assertOk()->assertSee('bKash');
    }

    public function test_customer_submits_proof_and_admin_approves(): void
    {
        $app = $this->upfrontApplication();
        $this->actingAs($app->user)->post(route('user.applications.pay-fee', $app));
        $tx = PaymentTransaction::firstOrFail();

        // Submit proof
        $this->actingAs($app->user)->post(route('payment.manual.submit', $tx), [
            'payment_method' => 'bKash', 'sender_number' => '01712345678', 'trx_id' => 'ABC123XYZ',
        ])->assertRedirect(route('payment.result', $tx));

        $tx->refresh();
        $this->assertSame(PaymentStatus::Pending, $tx->status);
        $this->assertSame('ABC123XYZ', $tx->gateway_transaction_id);
        $this->assertNull($tx->processed_at);

        // Admin approves
        $this->actingAs($this->admin('payment_manager'))->post(route('admin.payments.approve-manual', $tx))->assertRedirect();

        $tx->refresh();
        $this->assertSame(PaymentStatus::Completed, $tx->status);
        $this->assertNotNull($tx->processed_at);
        $this->assertTrue($app->fresh()->isFeePaid());
        // Paying the deposit must NOT auto-approve or disburse the loan
        $this->assertSame('awaiting_agreement', $app->fresh()->status->value);
        $this->assertDatabaseCount('loans', 0);
    }

    public function test_admin_can_reject_manual_payment(): void
    {
        $app = $this->upfrontApplication();
        $this->actingAs($app->user)->post(route('user.applications.pay-fee', $app));
        $tx = PaymentTransaction::firstOrFail();
        $this->actingAs($app->user)->post(route('payment.manual.submit', $tx), [
            'payment_method' => 'bKash', 'sender_number' => '01712345678', 'trx_id' => 'FAKE',
        ]);

        $this->actingAs($this->admin('payment_manager'))->post(route('admin.payments.reject-manual', $tx), ['reason' => 'No money received'])->assertRedirect();

        $this->assertSame(PaymentStatus::Failed, $tx->fresh()->status);
        $this->assertFalse($app->fresh()->isFeePaid());
    }

    public function test_admin_can_manage_payment_methods(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.payment-methods.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.payment-methods.create'))->assertOk();
        $this->actingAs($admin)->post(route('admin.payment-methods.store'), [
            'name' => 'My bKash', 'type' => 'bkash', 'account_number' => '01999999999', 'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('payment_methods', ['name' => 'My bKash', 'account_number' => '01999999999']);
    }

    public function test_support_agent_cannot_manage_payment_methods(): void
    {
        $this->actingAs($this->admin('support_agent'))->get(route('admin.payment-methods.index'))->assertForbidden();
    }
}
