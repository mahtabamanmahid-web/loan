<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();             // APP-2026-000001
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_product_id')->constrained()->restrictOnDelete();
            $table->foreignId('loan_term_id')->nullable()->constrained()->nullOnDelete();

            // Applicant information (snapshot at time of application)
            $table->string('applicant_name');
            $table->string('phone', 20);
            $table->string('email');
            $table->text('present_address');
            $table->text('permanent_address');
            $table->string('occupation');
            $table->decimal('monthly_income', 14, 2);
            $table->string('purpose', 500);
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();

            // Requested loan (calculator snapshot)
            $table->decimal('requested_amount', 14, 2);
            $table->unsignedSmallInteger('requested_months');
            $table->decimal('interest_rate', 6, 2);
            $table->decimal('total_interest', 14, 2);
            $table->decimal('total_payable', 14, 2);
            $table->decimal('installment_amount', 14, 2);
            $table->decimal('processing_fee', 14, 2)->default(0);

            // Admin decision (may differ from request)
            $table->decimal('approved_amount', 14, 2)->nullable();
            $table->unsignedSmallInteger('approved_months')->nullable();
            $table->decimal('approved_interest_rate', 6, 2)->nullable();

            $table->string('status', 30)->default('submitted')->index();
            $table->text('admin_note')->nullable();                 // shown to customer
            $table->text('internal_note')->nullable();              // staff only
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('terms_accepted_ip', 45)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
