<?php

namespace App\Providers;

use App\Models\User;
use App\Services\SettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One SettingService instance per request (settings are cached inside)
        $this->app->singleton(SettingService::class);
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        // Strong password rules everywhere: Password::defaults()
        Password::defaults(fn () => app()->isProduction()
            ? Password::min(8)->letters()->numbers()->uncompromised()
            : Password::min(8)->letters()->numbers());

        // RBAC: any ability that looks like "group.action" is an admin permission.
        // Enables ->middleware('can:loans.approve') and @can('loans.approve').
        Gate::before(function (User $user, string $ability) {
            if (str_contains($ability, '.') && $user->isAdmin()) {
                return $user->hasPermission($ability) ? true : null;
            }

            return null;
        });

        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('payments', fn (Request $r) => Limit::perMinute(6)->by('pay:'.($r->user()?->id ?? $r->ip())));

        RateLimiter::for('webhook', fn (Request $r) => Limit::perMinute(60)->by('hook:'.$r->ip()));

        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(60)->by($r->user()?->id ?: $r->ip()));

        RateLimiter::for('uploads', fn (Request $r) => Limit::perHour(10)->by('up:'.($r->user()?->id ?? $r->ip())));
    }
}
