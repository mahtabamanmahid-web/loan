<?php

namespace Tests\Feature\Payment;

use App\Enums\InstallmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\PaymentTransaction;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * UddoktaPay integration tests (HTTP is faked – see SandboxPaymentTest for live sandbox).
 */
class PaymentTest extends TestCase
{
    private const KEY = 'test-api-key';

    /** What the fake verify-payment endpoint currently returns. */
    private array $verify = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['uddoktapay.base_url' => 'https://sandbox.uddoktapay.com', 'uddoktapay.api_key' => self::KEY]);

        Http::preventStrayRequests();
        Http::fake(function (Request $request) {
            if (str_ends_with($request->url(), '/api/checkout-v2')) {
                return Http::response(['status' => true, 'message' => 'Payment Url', 'payment_url' => 'https://sandbox.uddoktapay.com/payment/abc']);
            }

            return Http::response($this->verify);
        });
    }

    /** Set the response of the fake verify API. */
    private function fakeGateway(array $verify = []): void
    {
        $this->verify = $verify;
    }

    private function verifyResponse(PaymentTransaction $tx, array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Test', 'email' => 'a@b.com', 'amount' => (string) $tx->amount, 'fee' => '0.00', 'charged_amount' => (string) $tx->amount,
            'invoice_id' => 'INV123', 'metadata' => ['reference' => $tx->reference], 'payment_method' => 'bkash',
            'sender_number' => '01700000000', 'transaction_id' => 'TRX'.$tx->id, 'date' => now()->toDateTimeString(), 'status' => 'COMPLETED',
        ], $overrides);
    }

    private function initiateInstallment(Loan $loan): PaymentTransaction
    {
        $inst = $loan->fresh()->nextInstallment();
        $this->actingAs($loan->user)->post(route('user.installments.pay', $inst))
            ->assertRedirect('https://sandbox.uddoktapay.com/payment/abc');

        return PaymentTransaction::latest('id')->firstOrFail();
    }

    public function test_payment_initiation_sends_correct_request_and_never_exposes_key(): void
    {
        $this->fakeGateway();
        $loan = $this->activeLoan();
        $tx = $this->initiateInstallment($loan);

        $this->assertSame(PaymentStatus::Pending, $tx->status);
        $this->assertEquals((float) $loan->installments->first()->amount, (float) $tx->amount);
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/api/checkout-v2')
            && $r->header('RT-UDDOKTAPAY-API-KEY')[0] === self::KEY
            && $r['metadata']['reference'] === $tx->reference
            && $r['return_type'] === 'GET');

        // The key must never reach the browser
        $page = $this->actingAs($loan->user)->get(route('user.loans.show', $loan))->getContent();
        $this->assertStringNotContainsString(self::KEY, $page);
        $this->assertDatabaseHas('payment_logs', ['payment_transaction_id' => $tx->id, 'event' => 'initiated']);
    }

    public function test_customer_without_email_can_start_payment(): void
    {
        $loan = $this->activeLoan();
        $loan->user->update(['email' => null]);

        $this->initiateInstallment($loan->fresh());

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/api/checkout-v2')
            && filter_var($r['email'], FILTER_VALIDATE_EMAIL) !== false);
    }

    public function test_successful_verification_applies_repayment(): void
    {
        $loan = $this->activeLoan();
        $this->fakeGateway();
        $tx = $this->initiateInstallment($loan);
        $this->fakeGateway($this->verifyResponse($tx));

        $this->actingAs($loan->user)->get('/payment/callback?invoice_id=INV123')->assertRedirect(route('payment.result', $tx));

        $tx->refresh();
        $this->assertSame(PaymentStatus::Completed, $tx->status);
        $this->assertNotNull($tx->processed_at);
        $this->assertSame('INV123', $tx->gateway_invoice_id);
        $this->assertSame(InstallmentStatus::Paid, $loan->installments->first()->fresh()->status);
        $this->assertEquals((float) $tx->amount, (float) $loan->fresh()->total_paid);
        $this->actingAs($loan->user)->get(route('payment.result', $tx))->assertOk();
    }

    public function test_browser_redirect_alone_never_marks_success(): void
    {
        $loan = $this->activeLoan();
        $this->fakeGateway();
        $tx = $this->initiateInstallment($loan);
        // Gateway says the invoice is still pending
        $this->fakeGateway($this->verifyResponse($tx, ['status' => 'PENDING', 'transaction_id' => null]));

        $this->actingAs($loan->user)->get('/payment/callback?invoice_id=INV123&status=success');

        $this->assertSame(PaymentStatus::Pending, $tx->fresh()->status);
        $this->assertEquals(0, (float) $loan->fresh()->total_paid);
    }

    public function test_duplicate_callbacks_are_processed_only_once(): void
    {
        $loan = $this->activeLoan();
        $this->fakeGateway();
        $tx = $this->initiateInstallment($loan);
        $this->fakeGateway($this->verifyResponse($tx));

        // Redirect + webhook + refresh – three times the same invoice
        $this->actingAs($loan->user)->get('/payment/callback?invoice_id=INV123');
        $this->postJson('/api/v1/payments/uddoktapay/webhook', ['invoice_id' => 'INV123'], ['RT-UDDOKTAPAY-API-KEY' => self::KEY])->assertOk();
        $this->actingAs($loan->user)->get('/payment/callback?invoice_id=INV123');

        $this->assertEquals((float) $tx->amount, (float) $loan->fresh()->total_paid);
        $this->assertDatabaseCount('loan_repayments', 1);
        $this->assertDatabaseHas('payment_logs', ['payment_transaction_id' => $tx->id, 'event' => 'duplicate']);
    }

    public function test_amount_mismatch_is_rejected(): void
    {
        $loan = $this->activeLoan();
        $this->fakeGateway();
        $tx = $this->initiateInstallment($loan);
        $this->fakeGateway($this->verifyResponse($tx, ['amount' => '10.00']));

        $this->actingAs($loan->user)->get('/payment/callback?invoice_id=INV123');

        $this->assertSame(PaymentStatus::Failed, $tx->fresh()->status);
        $this->assertEquals(0, (float) $loan->fresh()->total_paid);
        $this->assertDatabaseHas('payment_logs', ['event' => 'amount_mismatch']);
    }

    public function test_same_gateway_transaction_cannot_pay_twice(): void
    {
        $loan = $this->activeLoan();
        $this->fakeGateway();
        $tx1 = $this->initiateInstallment($loan);
        $this->fakeGateway($this->verifyResponse($tx1, ['transaction_id' => 'SAMETRX', 'invoice_id' => 'INV1']));
        $this->actingAs($loan->user)->get('/payment/callback?invoice_id=INV1');

        $this->fakeGateway();
        $tx2 = $this->initiateInstallment($loan->fresh('installments'));
        // Attacker replays the same bKash TrxID for a second invoice
        $this->fakeGateway($this->verifyResponse($tx2, ['transaction_id' => 'SAMETRX', 'invoice_id' => 'INV2']));
        $this->actingAs($loan->user)->get('/payment/callback?invoice_id=INV2');

        $this->assertSame(PaymentStatus::Completed, $tx1->fresh()->status);
        $this->assertSame(PaymentStatus::Failed, $tx2->fresh()->status);
        $this->assertDatabaseCount('loan_repayments', 1);
    }

    public function test_webhook_requires_valid_api_key(): void
    {
        $this->postJson('/api/v1/payments/uddoktapay/webhook', ['invoice_id' => 'X'])->assertUnauthorized();
        $this->postJson('/api/v1/payments/uddoktapay/webhook', ['invoice_id' => 'X'], ['RT-UDDOKTAPAY-API-KEY' => 'wrong'])->assertUnauthorized();
        $this->assertDatabaseHas('payment_logs', ['event' => 'webhook_unauthorized']);
    }

    public function test_failed_payment_is_marked_failed(): void
    {
        $loan = $this->activeLoan();
        $this->fakeGateway();
        $tx = $this->initiateInstallment($loan);
        $this->fakeGateway($this->verifyResponse($tx, ['status' => 'ERROR', 'transaction_id' => null]));

        $this->actingAs($loan->user)->get('/payment/callback?invoice_id=INV123');
        $this->assertSame(PaymentStatus::Failed, $tx->fresh()->status);
    }

    public function test_upfront_fee_payment_does_not_approve_or_disburse(): void
    {
        $product = LoanProduct::factory()->upfrontFee()->withTerms()->create()->load('terms');
        $app = $this->signedApplication($product, 20000);
        $this->assertTrue($app->requiresUpfrontFee());

        $this->fakeGateway();
        $this->actingAs($app->user)->post(route('user.applications.pay-fee', $app))->assertRedirect();
        $tx = PaymentTransaction::firstOrFail();
        $this->assertEquals(400, (float) $tx->amount);

        $this->fakeGateway($this->verifyResponse($tx));
        $this->actingAs($app->user)->get('/payment/callback?invoice_id=INV123');

        $this->assertSame(PaymentStatus::Completed, $tx->fresh()->status);
        $this->assertFalse($app->fresh()->requiresUpfrontFee());
        $this->assertDatabaseCount('loans', 0);                // no loan created by payment
        $this->assertSame('awaiting_agreement', $app->fresh()->status->value);
    }

    public function test_receipt_pdf_only_for_owner_and_successful_payment(): void
    {
        $loan = $this->activeLoan();
        $this->fakeGateway();
        $tx = $this->initiateInstallment($loan);

        $this->actingAs($loan->user)->get(route('user.transactions.receipt', $tx))->assertNotFound(); // still pending

        $this->fakeGateway($this->verifyResponse($tx));
        $this->actingAs($loan->user)->get('/payment/callback?invoice_id=INV123');
        $this->actingAs($loan->user)->get(route('user.transactions.receipt', $tx))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($this->customer())->get(route('user.transactions.receipt', $tx))->assertNotFound();
    }
}
