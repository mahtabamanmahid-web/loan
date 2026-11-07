<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\LoginHistory;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function activity(Request $request): View
    {
        $q = AdminActivityLog::with('admin')->latest('id');
        if ($s = $request->query('search')) {
            $q->where(fn ($q) => $q->where('action', 'like', "%$s%")->orWhere('description', 'like', "%$s%"));
        }

        return view('admin.logs.activity', ['logs' => $q->paginate(30)->withQueryString()]);
    }

    public function logins(Request $request): View
    {
        $q = LoginHistory::with('user')->latest('id');
        if ($request->query('failed')) {
            $q->where('successful', false);
        }
        if ($s = $request->query('search')) {
            $q->where(fn ($q) => $q->where('login_identifier', 'like', "%$s%")->orWhere('ip_address', 'like', "%$s%"));
        }

        return view('admin.logs.logins', ['logs' => $q->paginate(30)->withQueryString()]);
    }

    public function payments(Request $request): View
    {
        $q = PaymentLog::with('transaction')->latest('id');
        if ($level = $request->query('level')) {
            $q->where('level', $level);
        }

        return view('admin.logs.payments', ['logs' => $q->paginate(30)->withQueryString()]);
    }
}
