<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('present_address')->nullable();     // encrypted cast
            $table->text('permanent_address')->nullable();   // encrypted cast
            $table->string('occupation')->nullable();
            $table->decimal('monthly_income', 14, 2)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->timestamps();
        });

        // KYC / NID documents. Files live on the PRIVATE disk – never in /public.
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('nid_number');                       // encrypted at rest
            $table->string('nid_hash', 64)->index();          // sha256 of NID – duplicate detection without decrypting
            $table->string('front_image_path');
            $table->string('back_image_path');
            $table->string('selfie_path')->nullable();
            $table->string('status', 30)->default('pending')->index(); // pending|approved|rejected|resubmit
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_documents');
        Schema::dropIfExists('user_profiles');
    }
};
