<div class="mb-5 flex gap-2 overflow-x-auto">
    @foreach(['admin.logs.activity' => 'Admin Activity Logs', 'admin.logs.logins' => 'Login History', 'admin.logs.payments' => 'Payment Audit Logs'] as $route => $label)
        <a href="{{ route($route) }}" class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold {{ request()->routeIs($route) ? 'bg-primary text-white' : 'bg-white text-slate-600 dark:bg-slate-900 dark:text-slate-300' }}">{{ __($label) }}</a>
    @endforeach
</div>
