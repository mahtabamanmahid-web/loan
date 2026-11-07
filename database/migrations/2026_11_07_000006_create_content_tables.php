<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Website content: settings, reviews, FAQs, announcements.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Branding & homepage content (key/value, editable from Admin > Website)
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group', 30)->default('general')->index();
            $table->timestamps();
        });

        // Business rules: loan limits, maintenance mode, etc.
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group', 30)->default('loan')->index();
            $table->timestamps();
        });

        // Reviews must belong to a REAL customer account (user_id required).
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name');
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->boolean('is_verified')->default(false);   // admin confirmed the customer is real
            $table->boolean('is_published')->default(false)->index();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->string('question_bn')->nullable();
            $table->text('answer');
            $table->text('answer_bn')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('type', 20)->default('info');      // info|success|warning|danger
            $table->string('audience', 20)->default('all');   // all|public|customers
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('website_settings');
    }
};
