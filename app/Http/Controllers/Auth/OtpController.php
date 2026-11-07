<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Mobile number verification with a 6-digit OTP after registration. */
class OtpController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->isPhoneVerified()) {
            return redirect()->route('user.dashboard');
        }

        return view('auth.verify-otp', ['phone' => $request->user()->phone]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $user = $request->user();

        if (! $this->otp->verify($user->phone, 'register', $request->code)) {
            return back()->withErrors(['code' => __('The code is incorrect or has expired.')]);
        }

        $user->forceFill(['phone_verified_at' => now()])->save();

        return redirect()->route('user.dashboard')->with('success', __('Mobile number verified! Welcome to :site.', ['site' => site_name()]));
    }

    public function resend(Request $request): RedirectResponse
    {
        if (! $request->user()->isPhoneVerified()) {
            $this->otp->send($request->user()->phone, 'register');
        }

        return back()->with('success', __('A new code has been sent.'));
    }
}
