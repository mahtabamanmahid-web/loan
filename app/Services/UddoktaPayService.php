<?php

namespace App\Services;

use App\Exceptions\PaymentGatewayException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin HTTP client for the UddoktaPay API (v2).
 *
 *   POST {base}/api/checkout-v2      create a charge → returns payment_url
 *   POST {base}/api/verify-payment   verify by invoice_id → status COMPLETED|PENDING|ERROR
 *   POST {base}/api/refund-payment   refund a completed payment
 *
 * Every request carries the header RT-UDDOKTAPAY-API-KEY. The key is read from
 * .env and only used here, on the server.
 */
class UddoktaPayService
{
    public const HEADER = 'RT-UDDOKTAPAY-API-KEY';

    public function isConfigured(): bool
    {
        return filled(config('uddoktapay.api_key')) && filled(config('uddoktapay.base_url'));
    }

    /**
     * @param  array{full_name: string, email: string, amount: string|float, metadata: array, redirect_url: string, cancel_url: string, webhook_url?: string}  $data
     * @return array{payment_url: string, raw: array}
     */
    public function createCharge(array $data): array
    {
        $payload = [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'amount' => number_format((float) $data['amount'], 2, '.', ''),
            'metadata' => $data['metadata'],
            'redirect_url' => $data['redirect_url'],
            'return_type' => 'GET',            // invoice_id comes back as ?invoice_id=...
            'cancel_url' => $data['cancel_url'],
            'webhook_url' => $data['webhook_url'] ?? null,
        ];

        $json = $this->post('checkout', array_filter($payload, fn ($v) => $v !== null));

        if (empty($json['payment_url'])) {
            throw new PaymentGatewayException($json['message'] ?? 'Payment gateway did not return a payment URL.');
        }

        return ['payment_url' => $json['payment_url'], 'raw' => $json];
    }

    /**
     * @return array Raw verify response (keys: status, invoice_id, amount, transaction_id, payment_method, sender_number, metadata, date, fee, charged_amount ...)
     */
    public function verifyPayment(string $invoiceId): array
    {
        return $this->post('verify', ['invoice_id' => $invoiceId]);
    }

    public function refund(string $transactionId, string $paymentMethod, float $amount, string $productName, string $reason): array
    {
        return $this->post('refund', [
            'transaction_id' => $transactionId,
            'payment_method' => $paymentMethod,
            'amount' => number_format($amount, 2, '.', ''),
            'product_name' => $productName,
            'reason' => $reason,
        ]);
    }

    /** Constant-time check of the API key header sent with webhook (IPN) calls. */
    public function isValidWebhookKey(?string $headerValue): bool
    {
        $key = (string) config('uddoktapay.api_key');

        return $key !== '' && is_string($headerValue) && hash_equals($key, $headerValue);
    }

    private function post(string $endpoint, array $payload): array
    {
        if (! $this->isConfigured()) {
            throw new PaymentGatewayException('UddoktaPay is not configured. Set UDDOKTAPAY_BASE_URL and UDDOKTAPAY_API_KEY in .env');
        }

        $url = config('uddoktapay.base_url').config("uddoktapay.endpoints.$endpoint");

        try {
            /** @var Response $response */
            $response = Http::withHeaders([self::HEADER => config('uddoktapay.api_key')])
                ->acceptJson()
                ->asJson()
                ->timeout(config('uddoktapay.timeout'))
                ->retry(2, 300, throw: false)
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            throw new PaymentGatewayException('Could not connect to the payment gateway. Please try again.', 0, $e);
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new PaymentGatewayException('Invalid response from payment gateway (HTTP '.$response->status().').');
        }

        // UddoktaPay returns {"status": false, "message": "..."} on errors
        if ($response->failed() || (array_key_exists('status', $json) && $json['status'] === false)) {
            throw new PaymentGatewayException($json['message'] ?? ('Payment gateway error (HTTP '.$response->status().').'));
        }

        return $json;
    }
}
