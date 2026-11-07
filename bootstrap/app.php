<?php

use App\Exceptions\LoanWorkflowException;
use App\Exceptions\PaymentGatewayException;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureCustomer;
use App\Http\Middleware\EnsureNotBlocked;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Admin panel lives in its own route file under /admin
            Route::middleware('web')->prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            CheckMaintenanceMode::class,
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'customer' => EnsureCustomer::class,
            'not.blocked' => EnsureNotBlocked::class,
        ]);

        // UddoktaPay may redirect back with POST; the request is verified server-side anyway.
        $middleware->validateCsrfTokens(except: ['payment/callback']);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*') ? route('admin.login') : route('login'));
        $middleware->redirectUsersTo(fn (Request $request) => $request->user()?->isAdmin() ? route('admin.dashboard') : route('user.dashboard'));

        // Behind Cloudflare / a load balancer? Uncomment and list your proxy IPs so that
        // client IPs (used for rate limiting & audit logs) and HTTPS are detected correctly:
        // $middleware->trustProxies(at: ['10.0.0.0/8', '173.245.48.0/20']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Business-rule errors: go back with a friendly message instead of a 500 page.
        $exceptions->render(function (LoanWorkflowException|PaymentGatewayException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        });
    })->create();
