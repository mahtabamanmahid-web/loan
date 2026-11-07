<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A loan package shown on the homepage (e.g. "Small Business Loan").
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('description_bn')->nullable();
            $table->string('icon', 40)->default('wallet');
            $table->decimal('min_amount', 14, 2);
            $table->decimal('max_amount', 14, 2);
            $table->string('repayment_frequency', 20)->default('monthly'); // monthly|weekly
            // Processing fee disclosure – must be shown before any agreement/payment.
            $table->string('processing_fee_type', 10)->default('percent'); // percent|fixed
            $table->decimal('processing_fee_value', 10, 2)->default(0);
            $table->string('fee_collection', 20)->default('deduct');      // deduct (from disbursement) | upfront (pay after approval)
            $table->boolean('fee_refundable')->default(false);
            $table->text('fee_purpose')->nullable();
            $table->text('fee_refund_policy')->nullable();
            $table->decimal('late_fee_per_installment', 10, 2)->default(0);
            $table->unsignedInteger('grace_days')->default(3);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Durations available for a product, each with its own interest rate.
        Schema::create('loan_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('months');
            $table->decimal('interest_rate', 6, 2);   // flat annual rate in %
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['loan_product_id', 'months']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_terms');
        Schema::dropIfExists('loan_products');
    }
};
