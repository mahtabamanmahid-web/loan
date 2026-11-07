<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Immediately logs out a user that an admin has blocked. */
class EnsureNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_blocked) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', __('Your account has been blocked. Please contact support.'));
        }

        return $next($request);
    }
}
