<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manual payment methods (bKash, Nagad, Rocket, Upay, bank, QR...) shown to the
 * customer when the UddoktaPay online gateway is not configured. Managed from
 * Admin → Payment Methods.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // e.g. "bKash"
            $table->string('type', 20)->default('mobile'); // bkash|nagad|rocket|upay|bank|other
            $table->string('account_number')->nullable(); // mobile/account number
            $table->string('account_name')->nullable();
            $table->string('account_kind', 20)->nullable(); // personal|agent|merchant
            $table->text('instructions')->nullable();      // Bangla "how to pay" text
            $table->string('qr_path')->nullable();         // QR image on the public disk
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
