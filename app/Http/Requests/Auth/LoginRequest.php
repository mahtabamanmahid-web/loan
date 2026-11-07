<?php

namespace App\Http\Requests\Auth;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login with mobile number OR e-mail + password.
 * - 5 failed attempts per identifier+IP → locked for 60 s
 * - every attempt is written to login_histories
 * - blocked accounts cannot log in
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'max:200'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['login' => __('Mobile Number')];
    }

    /**
     * Customers log in with mobile number only; staff (/admin/login) may also use e-mail.
     *
     * @param  bool  $adminOnly  true for /admin/login
     */
    public function authenticate(bool $adminOnly = false): User
    {
        $this->ensureIsNotRateLimited();

        $login = trim((string) $this->input('login'));
        $field = $adminOnly && filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $value = $field === 'phone' ? preg_replace('/\D/', '', $login) : Str::lower($login);
        // Accept +8801XXXXXXXXX / 8801XXXXXXXXX as 01XXXXXXXXX
        if ($field === 'phone' && str_starts_with($value, '880')) {
            $value = substr($value, 2);
        }

        $user = User::where($field, $value)->first();
        $valid = $user && (! $adminOnly || $user->isAdmin())
            && Auth::validate([$field => $value, 'password' => $this->input('password')]);

        if (! $valid) {
            RateLimiter::hit($this->throttleKey(), 60);
            $this->record($user?->id, $login, false);

            throw ValidationException::withMessages(['login' => __('Mobile number or password is incorrect.')]);
        }

        if ($user->is_blocked) {
            $this->record($user->id, $login, false);
            throw ValidationException::withMessages(['login' => __('Your account has been blocked. Please contact support.')]);
        }

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());

        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $this->ip()])->save();
        $this->record($user->id, $login, true);

        return $user;
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => __('Too many login attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]),
        ]);
    }

    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->input('login')).'|'.$this->ip());
    }

    private function record(?int $userId, string $login, bool $ok): void
    {
        LoginHistory::create([
            'user_id' => $userId,
            'login_identifier' => Str::limit($login, 150, ''),
            'successful' => $ok,
            'ip_address' => $this->ip(),
            'user_agent' => Str::limit((string) $this->userAgent(), 500, ''),
        ]);
    }
}
