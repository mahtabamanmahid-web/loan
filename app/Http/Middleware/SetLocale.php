<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Picks the language: session choice → user preference → default (bn). */
class SetLocale
{
    public const SUPPORTED = ['bn', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale')
            ?? $request->user()?->locale
            ?? config('app.locale');

        app()->setLocale(in_array($locale, self::SUPPORTED, true) ? $locale : 'bn');

        return $next($request);
    }
}
