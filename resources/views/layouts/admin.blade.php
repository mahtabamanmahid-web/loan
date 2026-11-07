{{-- Admin panel layout. Menu items are shown only when the admin's role has the permission. --}}
@extends('layouts.base')

@section('robots')<meta name="robots" content="noindex, nofollow">@endsection

@php
    $admin = auth()->user();
    $pendingKyc = $admin->can('kyc.review') ? \App\Models\UserDocument::where('status', 'pending')->count() : 0;
    $pendingApps = $admin->can('loans.view') ? \App\Models\LoanApplication::whereIn('status', ['submitted', 'under_review'])->count() : 0;
    $openTickets = $admin->can('tickets.manage') ? \App\Models\SupportTicket::whereIn('status', ['open', 'customer_reply'])->count() : 0;
    $sections = [
        '' => [
            ['admin.dashboard', 'Dashboard', 'layout-dashboard', 'dashboard.view', 'admin.dashboard'],
        ],
        'Loans' => [
            ['admin.applications.index', 'Applications', 'file-text', 'loans.view', 'admin.applications.*', $pendingApps],
            ['admin.loans.index', 'Loans & Installments', 'wallet', 'loans.view', 'admin.loans.*'],
            ['admin.products.index', 'Loan Packages', 'package', 'products.manage', 'admin.products.*'],
            ['admin.settings.loan', 'Loan Settings', 'sliders-horizontal', 'settings.loan', 'admin.settings.loan'],
        ],
        'Customers' => [
            ['admin.users.index', 'Customers', 'users', 'users.view', 'admin.users.*'],
            ['admin.kyc.index', 'NID Verification', 'id-card', 'kyc.review', 'admin.kyc.*', $pendingKyc],
            ['admin.tickets.index', 'Support Tickets', 'life-buoy', 'tickets.manage', 'admin.tickets.*', $openTickets],
        ],
        'Payments' => [
            ['admin.payments.index', 'Payments', 'credit-card', 'payments.view', 'admin.payments.index|admin.payments.show'],
            ['admin.payments.reports', 'Payment Reports', 'chart-column', 'payments.view', 'admin.payments.reports'],
        ],
        'Website' => [
            ['admin.settings.website', 'Website Customization', 'palette', 'settings.website', 'admin.settings.website'],
            ['admin.reviews.index', 'Reviews', 'star', 'reviews.manage', 'admin.reviews.*'],
            ['admin.faqs.index', 'FAQ', 'circle-help', 'content.manage', 'admin.faqs.*'],
            ['admin.announcements.index', 'Announcements', 'megaphone', 'content.manage', 'admin.announcements.*'],
        ],
        'System' => [
            ['admin.admins.index', 'Admin Users', 'user-cog', 'admins.manage', 'admin.admins.*'],
            ['admin.roles.index', 'Roles & Permissions', 'shield', 'admins.manage', 'admin.roles.*'],
            ['admin.logs.activity', 'Activity Logs', 'history', 'logs.view', 'admin.logs.*'],
            ['admin.backups.index', 'Backups', 'database-backup', 'backups.manage', 'admin.backups.*'],
            ['admin.settings.system', 'System & Maintenance', 'settings', 'system.manage', 'admin.settings.system'],
        ],
    ];
    $isActive = fn ($patterns) => collect(explode('|', $patterns))->contains(fn ($p) => request()->routeIs($p));
@endphp

@section('body')
<div x-data="{ sidebar: false }" class="min-h-screen bg-slate-100/70 dark:bg-slate-950">
    <div x-show="sidebar" x-transition.opacity x-cloak class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden" @click="sidebar = false"></div>

    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-slate-900 text-slate-300 transition-transform duration-300 lg:translate-x-0">
        <div class="flex h-16 items-center justify-between border-b border-white/5 px-5">
            <x-brand :light="true" />
            <button class="text-slate-400 lg:hidden" @click="sidebar=false" aria-label="{{ __('Close') }}"><x-icon name="x" /></button>
        </div>
        <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4">
            @foreach($sections as $title => $items)
                @php($visible = collect($items)->filter(fn ($i) => $admin->can($i[3])))
                @if($visible->isNotEmpty())
                    <div>
                        @if($title)<p class="mb-1.5 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __($title) }}</p>@endif
                        @foreach($visible as $item)
                            <a href="{{ route($item[0]) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $isActive($item[4]) ? 'bg-gradient-to-r from-primary to-secondary text-white shadow-lg shadow-primary/20' : 'hover:bg-white/5 hover:text-white' }}">
                                <x-icon :name="$item[2]" class="h-[18px] w-[18px]" />
                                <span class="flex-1">{{ __($item[1]) }}</span>
                                @if(! empty($item[5]))<span class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $item[5] }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </nav>
        <div class="border-t border-white/5 p-3">
            <div class="mb-2 flex items-center gap-3 px-2">
                <img src="{{ $admin->avatarUrl() }}" class="h-9 w-9 rounded-lg" alt="">
                <div class="min-w-0"><p class="truncate text-sm font-semibold text-white">{{ $admin->name }}</p><p class="truncate text-xs text-slate-500">{{ $admin->adminRole?->name }}</p></div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf
                <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-400 hover:bg-rose-500/10"><x-icon name="log-out" class="h-[18px] w-[18px]" />{{ __('Logout') }}</button>
            </form>
        </div>
    </aside>

    <div class="lg:pl-72">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/85 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/85">
            <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button class="grid h-10 w-10 place-items-center rounded-xl hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800" @click="sidebar = true" aria-label="{{ __('Menu') }}"><x-icon name="menu" /></button>
                    <span class="hidden text-sm font-semibold text-slate-500 sm:inline">{{ site_name() }} · {{ __('Admin Panel') }}</span>
                    @if(sys_setting('maintenance_mode') == '1')
                        <x-badge color="amber" icon="construction">{{ __('Maintenance mode ON') }}</x-badge>
                    @endif
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ route('home') }}" target="_blank" class="grid h-10 w-10 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" title="{{ __('View website') }}"><x-icon name="external-link" class="h-[18px] w-[18px]" /></a>
                    @include('partials.toolbar')
                </div>
            </div>
        </header>
        <main class="page-enter mx-auto max-w-7xl px-4 py-6 sm:px-6">
            @yield('content')
        </main>
    </div>
</div>
@endsection
