<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register', ['enabled' => sys_setting('registration_enabled') == '1']);
    }

    public function register(RegisterRequest $request, OtpService $otp): RedirectResponse
    {
        abort_unless(sys_setting('registration_enabled') == '1', 403, __('Registration is currently closed.'));

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => $request->password,     // hashed automatically by the model cast
            'locale' => app()->getLocale(),
            'terms_accepted_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if (sys_setting('otp_enabled') == '1') {
            $otp->send($user->phone, 'register');

            return redirect()->route('otp.show')->with('success', __('Account created! Enter the code sent to your mobile.'));
        }

        $user->forceFill(['phone_verified_at' => now()])->save();

        return redirect()->route('user.dashboard')->with('success', __('Account created successfully!'));
    }
}
