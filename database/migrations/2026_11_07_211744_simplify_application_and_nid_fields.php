<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Simplified loan application (only name, mobile, occupation and loan reason are collected)
 * and NID verification by photo only (the NID number is no longer required).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->text('present_address')->nullable()->change();
            $table->text('permanent_address')->nullable()->change();
            $table->decimal('monthly_income', 14, 2)->nullable()->change();
        });

        Schema::table('user_documents', function (Blueprint $table) {
            $table->text('nid_number')->nullable()->change();
            $table->string('nid_hash', 64)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_documents', function (Blueprint $table) {
            $table->text('nid_number')->nullable(false)->change();
            $table->string('nid_hash', 64)->nullable(false)->change();
        });

        Schema::table('loan_applications', function (Blueprint $table) {
            $table->text('present_address')->nullable(false)->change();
            $table->text('permanent_address')->nullable(false)->change();
            $table->decimal('monthly_income', 14, 2)->nullable(false)->change();
        });
    }
};
