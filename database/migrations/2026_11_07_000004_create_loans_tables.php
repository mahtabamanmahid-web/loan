<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_agreements', function (Blueprint $table) {
            $table->id();
            $table->string('agreement_number', 30)->unique();
            $table->foreignId('loan_application_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('content');                         // frozen agreement text
            $table->decimal('principal', 14, 2);
            $table->unsignedSmallInteger('months');
            $table->decimal('interest_rate', 6, 2);
            $table->decimal('total_payable', 14, 2);
            $table->decimal('installment_amount', 14, 2);
            $table->decimal('processing_fee', 14, 2)->default(0);
            $table->string('fee_collection', 20)->default('deduct');
            $table->timestamp('accepted_at')->nullable();
            $table->string('accepted_ip', 45)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // A loan exists ONLY after real disbursement has been recorded.
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number', 30)->unique();          // LN-2026-000001
            $table->foreignId('loan_application_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('principal', 14, 2);
            $table->decimal('interest_rate', 6, 2);
            $table->unsignedSmallInteger('months');
            $table->decimal('total_interest', 14, 2);
            $table->decimal('total_payable', 14, 2);
            $table->decimal('installment_amount', 14, 2);
            $table->decimal('processing_fee', 14, 2)->default(0);
            $table->decimal('disbursed_amount', 14, 2);
            $table->decimal('total_paid', 14, 2)->default(0);
            $table->decimal('total_late_fee', 14, 2)->default(0);
            $table->string('disbursement_method', 30);            // bank|bkash|nagad|rocket|cash|cheque
            $table->string('disbursement_account')->nullable();
            $table->string('disbursement_reference')->unique();   // bank/MFS transaction ref – prevents double recording
            $table->timestamp('disbursed_at');
            $table->date('first_due_date');
            $table->string('status', 20)->default('active')->index(); // active|completed|defaulted
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('loan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('installment_number');
            $table->date('due_date')->index();
            $table->decimal('principal_component', 14, 2);
            $table->decimal('interest_component', 14, 2);
            $table->decimal('amount', 14, 2);
            $table->decimal('late_fee', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->string('status', 20)->default('pending')->index(); // pending|partial|paid|overdue
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();
            $table->unique(['loan_id', 'installment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_installments');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('loan_agreements');
    }
};
