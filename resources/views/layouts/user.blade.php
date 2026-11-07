{{-- Customer panel layout: sidebar (desktop), top bar, mobile bottom navigation. --}}
@extends('layouts.base')

@php
    $user = auth()->user();
    $unread = $user->unreadNotifications()->count();
    $menu = [
        ['user.dashboard', 'Dashboard', 'layout-dashboard', 'user.dashboard'],
        ['user.applications.create', 'Apply for a Loan', 'circle-plus', 'user.applications.create'],
        ['user.applications.index', 'My Applications', 'file-text', 'user.applications.index|user.applications.show|user.applications.agreement'],
        ['user.loans.index', 'My Loans', 'wallet', 'user.loans.*'],
        ['user.kyc.show', 'NID Verification', 'id-card', 'user.kyc.*'],
        ['user.transactions.index', 'Transactions', 'receipt-text', 'user.transactions.*'],
        ['user.tickets.index', 'Support', 'life-buoy', 'user.tickets.*'],
        ['user.notifications.index', 'Notifications', 'bell', 'user.notifications.*'],
        ['user.profile.edit', 'My Profile', 'circle-user-round', 'user.profile.*'],
    ];
    $isActive = fn ($patterns) => collect(explode('|', $patterns))->contains(fn ($p) => request()->routeIs($p));
@endphp

@section('body')
<div x-data="{ sidebar: false }" class="min-h-screen">
    {{-- Sidebar (desktop fixed, mobile drawer) --}}
    <div x-show="sidebar" x-transition.opacity x-cloak class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" @click="sidebar = false"></div>
    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-slate-200 bg-white transition-transform duration-300 lg:translate-x-0 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex h-16 items-center justify-between px-5"><x-brand /><button class="lg:hidden" @click="sidebar=false" aria-label="{{ __('Close') }}"><x-icon name="x" /></button></div>
        <div class="mx-4 mb-4 flex items-center gap-3 rounded-2xl bg-gradient-to-br from-primary/10 to-secondary/10 p-3">
            <img src="{{ $user->avatarUrl() }}" alt="" class="h-11 w-11 rounded-xl object-cover">
            <div class="min-w-0">
                <p class="truncate font-bold text-slate-900 dark:text-white">{{ $user->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ $user->phone }}</p>
            </div>
        </div>
        <nav class="flex-1 space-y-1 overflow-y-auto px-3">
            @foreach($menu as [$route, $label, $icon, $pattern])
                <a href="{{ route($route) }}" class="nav-link {{ $isActive($pattern) ? 'active' : '' }}">
                    <x-icon :name="$icon" />
                    <span class="flex-1">{{ __($label) }}</span>
                    @if($route === 'user.notifications.index' && $unread)<span class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ bn_digits($unread) }}</span>@endif
                </a>
            @endforeach
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="p-3">
            @csrf
            <button class="nav-link w-full text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-500/10"><x-icon name="log-out" />{{ __('Logout') }}</button>
        </form>
    </aside>

    <div class="lg:pl-72">
        {{-- Top bar --}}
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/85 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/85">
            <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6">
                <div class="flex items-center gap-2">
                    <button class="grid h-10 w-10 place-items-center rounded-xl hover:bg-slate-100 lg:hidden dark:hover:bg-slate-800" @click="sidebar = true" aria-label="{{ __('Menu') }}"><x-icon name="menu" /></button>
                    <a href="{{ route('home') }}" class="hidden items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-primary sm:flex"><x-icon name="globe" class="h-4 w-4" />{{ __('Website') }}</a>
                </div>
                <div class="flex items-center gap-1">
                    @include('partials.toolbar')
                    <a href="{{ route('user.notifications.index') }}" class="relative grid h-10 w-10 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" aria-label="{{ __('Notifications') }}">
                        <x-icon name="bell" class="h-[18px] w-[18px]" />
                        @if($unread)<span class="absolute right-1.5 top-1.5 grid h-4 min-w-4 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">{{ $unread > 9 ? '9+' : bn_digits($unread) }}</span>@endif
                    </a>
                    <a href="{{ route('user.profile.edit') }}" class="ml-1"><img src="{{ $user->avatarUrl() }}" alt="{{ __('My Profile') }}" class="h-9 w-9 rounded-xl object-cover ring-2 ring-primary/20"></a>
                </div>
            </div>
        </header>

        @include('partials.announcements', ['audience' => 'customers'])

        <main class="page-enter mx-auto max-w-6xl px-4 pb-28 pt-6 sm:px-6 lg:pb-10">
            @yield('content')
        </main>
    </div>

    {{-- Mobile bottom navigation --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-xl lg:hidden dark:border-slate-800 dark:bg-slate-950/95">
        <div class="grid grid-cols-5">
            @foreach([
                ['user.dashboard', 'Home', 'house', 'user.dashboard'],
                ['user.applications.index', 'Applications', 'file-text', 'user.applications.index|user.applications.show'],
                ['user.applications.create', 'Apply', 'plus', 'user.applications.create'],
                ['user.transactions.index', 'Payments', 'receipt-text', 'user.transactions.*'],
                ['user.profile.edit', 'Profile', 'circle-user-round', 'user.profile.*'],
            ] as [$route, $label, $icon, $pattern])
                @if($route === 'user.applications.create')
                    <a href="{{ route($route) }}" class="flex flex-col items-center gap-1 py-2 text-[11px] font-semibold text-slate-500">
                        <span class="-mt-6 grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg shadow-primary/40"><x-icon :name="$icon" /></span>{{ __($label) }}
                    </a>
                @else
                    <a href="{{ route($route) }}" class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold {{ $isActive($pattern) ? 'text-primary' : 'text-slate-500' }}"><x-icon :name="$icon" />{{ __($label) }}</a>
                @endif
            @endforeach
        </div>
    </nav>
</div>
@endsection
