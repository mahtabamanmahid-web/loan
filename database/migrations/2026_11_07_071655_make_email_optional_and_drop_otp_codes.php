<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customers now sign up and log in with mobile number + password only:
 * e-mail becomes optional and the OTP system is removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();   // still unique when filled
        });

        Schema::table('loan_applications', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        Schema::dropIfExists('otp_codes');
    }

    public function down(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->index();
            $table->string('purpose', 30);
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }
};
