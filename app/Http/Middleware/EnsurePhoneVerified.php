<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Customers must verify their mobile number (OTP) before using the panel. */
class EnsurePhoneVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isPhoneVerified() && sys_setting('otp_enabled') == '1') {
            return redirect()->route('otp.show');
        }

        return $next($request);
    }
}
