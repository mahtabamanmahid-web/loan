<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();            // our own reference, sent in metadata
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('loan_installment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('purpose', 30)->index();               // processing_fee|installment
            $table->string('description')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('BDT');
            $table->string('gateway', 30)->default('uddoktapay');
            $table->string('status', 20)->default('initiated')->index(); // initiated|pending|completed|failed|cancelled|refunded
            $table->string('gateway_invoice_id')->nullable()->unique();
            $table->string('gateway_transaction_id')->nullable()->unique(); // a gateway trx can be used only once
            $table->string('payment_method', 40)->nullable();
            $table->string('sender_number', 30)->nullable();
            $table->decimal('gateway_fee', 14, 2)->nullable();
            $table->decimal('charged_amount', 14, 2)->nullable();
            $table->string('payment_url', 1000)->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('processed_at')->nullable();         // idempotency guard: business effect applied once
            $table->string('failure_reason')->nullable();
            $table->decimal('refunded_amount', 14, 2)->default(0);
            $table->string('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        // Append-only audit log of everything that happens to a payment.
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 50)->index();                // initiated|redirect|webhook|verify|duplicate|refund ...
            $table->string('level', 10)->default('info');
            $table->string('message')->nullable();
            $table->json('payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_installment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('method', 30);                         // gateway|cash|bank|bkash|nagad
            $table->string('reference')->nullable();
            $table->timestamp('paid_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
        Schema::dropIfExists('payment_logs');
        Schema::dropIfExists('payment_transactions');
    }
};
