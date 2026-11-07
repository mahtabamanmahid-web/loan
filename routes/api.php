<?php

use App\Http\Controllers\Api\LoanCalculatorController;
use App\Http\Controllers\Api\UddoktaPayWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API  (prefix: /api)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public calculator endpoints (used by the website and available to mobile apps)
    Route::middleware('throttle:api')->group(function () {
        Route::get('/loan-products', [LoanCalculatorController::class, 'products'])->name('api.products');
        Route::get('/calculate', [LoanCalculatorController::class, 'calculate'])->name('api.calculate');
        Route::post('/eligibility', [LoanCalculatorController::class, 'eligibility'])->name('api.eligibility');
    });

    // UddoktaPay IPN / webhook (server-to-server). Authenticated by the API key header.
    Route::post('/payments/uddoktapay/webhook', UddoktaPayWebhookController::class)
        ->middleware('throttle:webhook')
        ->name('api.payments.webhook');
});
