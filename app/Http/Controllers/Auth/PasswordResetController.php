<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * Forgot password:
 *   - by MOBILE: 6-digit OTP by SMS, then new password
 *   - by EMAIL : signed reset link by e-mail
 * Responses never reveal whether an account exists (prevents user enumeration).
 */
class PasswordResetController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function request(): View
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate(['login' => ['required', 'string', 'max:150']]);
        $login = trim($request->login);

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            Password::sendResetLink(['email' => Str::lower($login)]);

            return back()->with('success', __('If an account exists for this email, a reset link has been sent.'));
        }

        $phone = preg_replace('/\D/', '', $login);
        if (str_starts_with($phone, '880')) {
            $phone = substr($phone, 2);
        }
        if (User::where('phone', $phone)->exists()) {
            $this->otp->send($phone, 'password_reset');
        }
        $request->session()->put('reset_phone', $phone);

        return redirect()->route('password.otp')->with('success', __('If an account exists for this number, a code has been sent by SMS.'));
    }

    public function otpForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('reset_phone')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password-otp', ['phone' => $request->session()->get('reset_phone')]);
    }

    public function resetWithOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $phone = (string) $request->session()->get('reset_phone');
        $user = User::where('phone', $phone)->first();

        if (! $user || ! $this->otp->verify($phone, 'password_reset', $request->code)) {
            return back()->withErrors(['code' => __('The code is incorrect or has expired.')]);
        }

        $user->forceFill(['password' => $request->password, 'remember_token' => Str::random(60)])->save();
        $request->session()->forget('reset_phone');
        event(new PasswordReset($user));

        return redirect()->route('login')->with('success', __('Password changed. Please log in with your new password.'));
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('success', __('Password changed. Please log in with your new password.'))
            : back()->withErrors(['email' => __('This reset link is invalid or has expired.')]);
    }
}
