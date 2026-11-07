<?php

/*
|--------------------------------------------------------------------------
| UddoktaPay configuration
|--------------------------------------------------------------------------
| Docs: https://uddoktapay.readme.io/reference/api-information
|
| UddoktaPay authenticates with ONE key (header RT-UDDOKTAPAY-API-KEY).
| There is no separate "API secret" – the API key itself is the secret, so it
| is only ever used server-side and is never sent to the browser.
|
| Sandbox base URL : https://sandbox.uddoktapay.com
| Live base URL    : your own UddoktaPay panel domain, e.g. https://pay.your-domain.com
*/

return [
    'mode' => env('UDDOKTAPAY_MODE', 'sandbox'),

    'base_url' => rtrim((string) env('UDDOKTAPAY_BASE_URL', 'https://sandbox.uddoktapay.com'), '/'),

    'api_key' => env('UDDOKTAPAY_API_KEY'),

    // Where UddoktaPay redirects the customer after payment (invoice_id is appended).
    // Leave empty to use the route('payment.callback') URL automatically.
    'callback_url' => env('UDDOKTAPAY_CALLBACK_URL'),
    'cancel_url' => env('UDDOKTAPAY_CANCEL_URL'),
    // Server-to-server IPN / webhook URL. Defaults to route('api.payments.webhook').
    'webhook_url' => env('UDDOKTAPAY_WEBHOOK_URL'),

    'timeout' => (int) env('UDDOKTAPAY_TIMEOUT', 30),

    'endpoints' => [
        'checkout' => '/api/checkout-v2',
        'verify' => '/api/verify-payment',
        'refund' => '/api/refund-payment',
    ],
];
