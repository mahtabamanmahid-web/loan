<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin roles & permissions (Role-Based Access Control).
 * Runs before the users table because users.admin_role_id references admin_roles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();          // super_admin, manager, loan_officer ...
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();          // e.g. loans.approve
            $table->string('group')->index();          // e.g. loans
            $table->timestamps();
        });

        Schema::create('admin_role_permission', function (Blueprint $table) {
            $table->foreignId('admin_role_id')->constrained('admin_roles')->cascadeOnDelete();
            $table->foreignId('admin_permission_id')->constrained('admin_permissions')->cascadeOnDelete();
            $table->primary(['admin_role_id', 'admin_permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_role_permission');
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('admin_roles');
    }
};
