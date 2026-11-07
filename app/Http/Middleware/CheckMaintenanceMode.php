<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin-controlled maintenance mode (Admin → System → Maintenance).
 * Admin pages, admin users and the payment webhook keep working.
 */
class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (sys_setting('maintenance_mode') != '1') {
            return $next($request);
        }

        $exempt = $request->is('admin', 'admin/*', 'api/v1/payments/*', 'payment/callback', 'up', 'locale/*');

        if ($exempt || $request->user()?->isAdmin()) {
            return $next($request);
        }

        return response()->view('errors.maintenance', ['message' => sys_setting('maintenance_message')], 503);
    }
}
