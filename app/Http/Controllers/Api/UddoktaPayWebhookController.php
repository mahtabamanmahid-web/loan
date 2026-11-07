<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\UddoktaPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * UddoktaPay IPN (webhook).
 *
 * 1. Authenticate: header RT-UDDOKTAPAY-API-KEY must equal our API key.
 * 2. Never trust the posted body – take only the invoice_id and re-verify it
 *    with the verify-payment API (PaymentService::verifyInvoice).
 * 3. Idempotent: repeated webhooks for the same invoice are ignored safely.
 */
class UddoktaPayWebhookController extends Controller
{
    public function __invoke(Request $request, UddoktaPayService $gateway, PaymentService $payments): JsonResponse
    {
        if (! $gateway->isValidWebhookKey($request->header(UddoktaPayService::HEADER))) {
            $payments->log(null, 'webhook_unauthorized', 'Webhook with invalid API key header', ['ip' => $request->ip()], 'warning');

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $invoiceId = (string) $request->input('invoice_id', '');
        if ($invoiceId === '') {
            return response()->json(['message' => 'invoice_id missing'], 422);
        }

        try {
            $tx = $payments->verifyInvoice($invoiceId, 'webhook', $request->ip());
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Verification failed'], 400);
        }

        return response()->json(['message' => 'OK', 'status' => $tx->status->value]);
    }
}
