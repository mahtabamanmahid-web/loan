<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\KycController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\LoanProductController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel routes  (prefix: /admin, name: admin.)
|--------------------------------------------------------------------------
| Every action is protected by a permission: ->middleware('can:permission.slug')
| Permissions per role are defined in config/permissions.php.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware(['auth', 'admin', 'not.blocked'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Customers & KYC
    Route::middleware('can:users.view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    });
    Route::middleware('can:users.manage')->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/block', [UserController::class, 'toggleBlock'])->name('users.block');
    });
    Route::middleware('can:kyc.review')->group(function () {
        Route::get('/kyc', [KycController::class, 'index'])->name('kyc.index');
        Route::get('/kyc/{document}', [KycController::class, 'show'])->name('kyc.show');
        Route::get('/kyc/{document}/file/{side}', [KycController::class, 'file'])->whereIn('side', ['front', 'back', 'selfie'])->name('kyc.file');
        Route::post('/kyc/{document}/review', [KycController::class, 'review'])->name('kyc.review');
    });

    // ---- Loan applications
    Route::middleware('can:loans.view')->group(function () {
        Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
        Route::get('/applications/{application}/pdf', [ApplicationController::class, 'pdf'])->name('applications.pdf');
        Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
        Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    });
    Route::post('/applications/{application}/review', [ApplicationController::class, 'startReview'])->middleware('can:loans.review')->name('applications.review');
    Route::post('/applications/{application}/request-documents', [ApplicationController::class, 'requestDocuments'])->middleware('can:loans.review')->name('applications.request-documents');
    Route::post('/applications/{application}/approve', [ApplicationController::class, 'approve'])->middleware('can:loans.approve')->name('applications.approve');
    Route::post('/applications/{application}/reject', [ApplicationController::class, 'reject'])->middleware('can:loans.approve')->name('applications.reject');
    Route::post('/applications/{application}/agreement', [ApplicationController::class, 'createAgreement'])->middleware('can:loans.agreement')->name('applications.agreement');
    Route::post('/applications/{application}/disburse', [ApplicationController::class, 'disburse'])->middleware('can:loans.disburse')->name('applications.disburse');
    Route::post('/loans/{loan}/repayments', [LoanController::class, 'recordRepayment'])->middleware('can:loans.repayments')->name('loans.repayments.store');

    // ---- Loan packages
    Route::resource('products', LoanProductController::class)->except('show')->middleware('can:products.manage');

    // ---- Payments
    Route::middleware('can:payments.view')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/reports', [PaymentController::class, 'reports'])->name('payments.reports');
        Route::get('/payments/{transaction}', [PaymentController::class, 'show'])->name('payments.show');
    });
    Route::get('/payments-export', [PaymentController::class, 'export'])->middleware('can:payments.export')->name('payments.export');
    Route::post('/payments/{transaction}/verify', [PaymentController::class, 'verify'])->middleware(['can:payments.verify', 'throttle:20,1'])->name('payments.verify');
    Route::post('/payments/{transaction}/refund', [PaymentController::class, 'refund'])->middleware('can:payments.refund')->name('payments.refund');

    // ---- Settings & website customization
    Route::middleware('can:settings.loan')->group(function () {
        Route::get('/settings/loan', [SettingController::class, 'loan'])->name('settings.loan');
        Route::put('/settings/loan', [SettingController::class, 'updateLoan'])->name('settings.loan.update');
    });
    Route::middleware('can:settings.website')->group(function () {
        Route::get('/settings/website', [SettingController::class, 'website'])->name('settings.website');
        Route::put('/settings/website', [SettingController::class, 'updateWebsite'])->name('settings.website.update');
    });
    Route::middleware('can:system.manage')->group(function () {
        Route::get('/settings/system', [SettingController::class, 'system'])->name('settings.system');
        Route::put('/settings/system', [SettingController::class, 'updateSystem'])->name('settings.system.update');
    });

    // ---- Content
    Route::middleware('can:reviews.manage')->group(function () {
        Route::resource('reviews', ReviewController::class)->except('show');
        Route::post('/reviews/{review}/toggle/{field}', [ReviewController::class, 'toggle'])->whereIn('field', ['is_published', 'is_featured', 'is_verified'])->name('reviews.toggle');
    });
    Route::middleware('can:content.manage')->group(function () {
        Route::resource('faqs', FaqController::class)->except('show');
        Route::resource('announcements', AnnouncementController::class)->except('show');
    });

    // ---- Support
    Route::middleware('can:tickets.manage')->group(function () {
        Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('tickets.reply');
        Route::post('/tickets/{ticket}/status', [SupportTicketController::class, 'status'])->name('tickets.status');
    });

    // ---- Staff & roles
    Route::middleware('can:admins.manage')->group(function () {
        Route::resource('admins', AdminUserController::class)->except('show')->parameters(['admins' => 'admin']);
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    // ---- Logs & backups
    Route::middleware('can:logs.view')->group(function () {
        Route::get('/logs/activity', [LogController::class, 'activity'])->name('logs.activity');
        Route::get('/logs/logins', [LogController::class, 'logins'])->name('logs.logins');
        Route::get('/logs/payments', [LogController::class, 'payments'])->name('logs.payments');
    });
    Route::middleware('can:backups.manage')->group(function () {
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'store'])->middleware('throttle:3,1')->name('backups.store');
        Route::get('/backups/{name}', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{name}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });
});
