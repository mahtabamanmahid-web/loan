<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register', ['enabled' => sys_setting('registration_enabled') == '1']);
    }

    /** Create the account and log the customer straight in (no OTP step). */
    public function register(RegisterRequest $request): RedirectResponse
    {
        abort_unless(sys_setting('registration_enabled') == '1', 403, __('Registration is currently closed.'));

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => $request->password,     // hashed automatically by the model cast
            'locale' => app()->getLocale(),
            'terms_accepted_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')->with('success', __('Account created successfully!'));
    }
}
