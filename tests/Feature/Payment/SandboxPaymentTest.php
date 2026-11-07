<?php

namespace Tests\Feature\Payment;

use App\Exceptions\PaymentGatewayException;
use App\Services\UddoktaPayService;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * LIVE test against the public UddoktaPay sandbox (needs internet).
 * Skipped by default. Run with:
 *   UDDOKTAPAY_SANDBOX_TEST=1 php vendor/bin/phpunit --group sandbox
 */
#[Group('sandbox')]
class SandboxPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! getenv('UDDOKTAPAY_SANDBOX_TEST')) {
            $this->markTestSkipped('Set UDDOKTAPAY_SANDBOX_TEST=1 to run live sandbox tests.');
        }

        config([
            'uddoktapay.base_url' => 'https://sandbox.uddoktapay.com',
            // Public sandbox key published in the UddoktaPay docs
            'uddoktapay.api_key' => getenv('UDDOKTAPAY_API_KEY') ?: '982d381360a69d419689740d9f2e26ce36fb7a50',
        ]);
    }

    public function test_sandbox_checkout_returns_payment_url(): void
    {
        $result = app(UddoktaPayService::class)->createCharge([
            'full_name' => 'Sandbox Tester',
            'email' => 'sandbox@example.com',
            'amount' => 10,
            'metadata' => ['reference' => 'TEST-'.uniqid()],
            'redirect_url' => 'https://example.com/payment/callback',
            'cancel_url' => 'https://example.com/payment/cancel',
            'webhook_url' => 'https://example.com/api/v1/payments/uddoktapay/webhook',
        ]);

        $this->assertStringStartsWith('https://', $result['payment_url']);
        fwrite(STDERR, "\nSandbox checkout URL: {$result['payment_url']}\n");
    }

    public function test_sandbox_verify_unknown_invoice_is_not_completed(): void
    {
        try {
            $data = app(UddoktaPayService::class)->verifyPayment('INVALID-INVOICE-'.uniqid());
            $this->assertNotSame('COMPLETED', strtoupper((string) ($data['status'] ?? '')));
        } catch (PaymentGatewayException $e) {
            // An error response is also an acceptable "not paid" outcome
            $this->assertNotEmpty($e->getMessage());
        }
    }
}
