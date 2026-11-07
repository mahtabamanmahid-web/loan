<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\LocaleController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\User\ApplicationController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\KycController;
use App\Http\Controllers\User\LoanController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\ReviewController;
use App\Http\Controllers\User\SupportTicketController;
use App\Http\Controllers\User\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/calculator', [HomeController::class, 'calculator'])->name('calculator');
Route::get('/loan-terms', [PageController::class, 'loanTerms'])->name('loan-terms');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/locale/{locale}', LocaleController::class)->whereIn('locale', ['bn', 'en'])->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Authentication (guests only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');

    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'send'])->middleware('throttle:otp')->name('password.send');
    Route::get('/reset-password/otp', [PasswordResetController::class, 'otpForm'])->name('password.otp');
    Route::post('/reset-password/otp', [PasswordResetController::class, 'resetWithOtp'])->middleware('throttle:10,1')->name('password.otp.update');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:10,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/verify-phone', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/verify-phone', [OtpController::class, 'verify'])->middleware('throttle:10,1')->name('otp.verify');
    Route::post('/verify-phone/resend', [OtpController::class, 'resend'])->middleware('throttle:otp')->name('otp.resend');
});

/*
|--------------------------------------------------------------------------
| Payment gateway return URLs
|--------------------------------------------------------------------------
| The callback does not trust the browser: it verifies the invoice with
| UddoktaPay's server API before showing anything as successful.
*/
Route::match(['get', 'post'], '/payment/callback', [PaymentController::class, 'callback'])->middleware('throttle:30,1')->name('payment.callback');
Route::middleware(['auth', 'not.blocked'])->group(function () {
    Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
    Route::get('/payment/result/{transaction:reference}', [PaymentController::class, 'result'])->name('payment.result');
});

/*
|--------------------------------------------------------------------------
| Customer panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'not.blocked', 'customer', 'phone.verified'])->name('user.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Loan application wizard + status
    Route::get('/apply', [ApplicationController::class, 'create'])->name('applications.create');
    Route::post('/apply', [ApplicationController::class, 'store'])->middleware('throttle:5,1')->name('applications.store');
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/cancel', [ApplicationController::class, 'cancel'])->name('applications.cancel');
    Route::get('/applications/{application}/pdf', [ApplicationController::class, 'pdf'])->name('applications.pdf');
    Route::get('/applications/{application}/agreement', [ApplicationController::class, 'agreement'])->name('applications.agreement');
    Route::post('/applications/{application}/agreement', [ApplicationController::class, 'acceptAgreement'])->name('applications.agreement.accept');
    Route::get('/applications/{application}/agreement/pdf', [ApplicationController::class, 'agreementPdf'])->name('applications.agreement.pdf');
    Route::post('/applications/{application}/pay-fee', [PaymentController::class, 'payFee'])->middleware('throttle:payments')->name('applications.pay-fee');

    // NID / KYC
    Route::get('/kyc', [KycController::class, 'show'])->name('kyc.show');
    Route::post('/kyc', [KycController::class, 'store'])->middleware('throttle:uploads')->name('kyc.store');

    // Loans & installments
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    Route::post('/installments/{installment}/pay', [PaymentController::class, 'payInstallment'])->middleware('throttle:payments')->name('installments.pay');

    // Transactions & receipts
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'avatar'])->middleware('throttle:uploads')->name('profile.avatar');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('/profile/notifications', [ProfileController::class, 'notifications'])->name('profile.notifications');
    Route::get('/profile/login-history', [ProfileController::class, 'loginHistory'])->name('profile.logins');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');

    // Support tickets
    Route::get('/support', [SupportTicketController::class, 'index'])->name('tickets.index');
    Route::get('/support/new', [SupportTicketController::class, 'create'])->name('tickets.create');
    Route::post('/support', [SupportTicketController::class, 'store'])->middleware('throttle:5,1')->name('tickets.store');
    Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');
    Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->middleware('throttle:10,1')->name('tickets.reply');
    Route::post('/support/{ticket}/close', [SupportTicketController::class, 'close'])->name('tickets.close');

    // Customer review (only for customers with a disbursed loan)
    Route::post('/review', [ReviewController::class, 'store'])->middleware('throttle:3,1')->name('reviews.store');
});
