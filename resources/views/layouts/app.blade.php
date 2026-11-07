{{-- Public website layout: header, footer, WhatsApp button and mobile bottom navigation. --}}
@extends('layouts.base')

@section('body')
<div class="flex min-h-screen flex-col pb-20 md:pb-0">
    @include('partials.announcements', ['audience' => 'public'])

    {{-- ================= Header ================= --}}
    <header x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 10"
            :class="scrolled ? 'bg-white/85 shadow-sm backdrop-blur-xl dark:bg-slate-950/85' : 'bg-transparent'"
            class="sticky top-0 z-50 transition-all duration-300">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <x-brand />

            <div class="hidden items-center gap-1 lg:flex">
                @foreach([
                    ['home', 'Home', 'house'],
                    ['home', 'Loan Packages', 'package', '#products'],
                    ['calculator', 'Calculator', 'calculator'],
                    ['loan-terms', 'Fees & Terms', 'file-text'],
                    ['home', 'FAQ', 'circle-help', '#faq'],
                ] as $item)
                    <a href="{{ route($item[0]) }}{{ $item[3] ?? '' }}" class="rounded-xl px-3.5 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">{{ __($item[1]) }}</a>
                @endforeach
            </div>

            <div class="flex items-center gap-1 sm:gap-2">
                @include('partials.toolbar')
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard') }}" class="btn btn-primary btn-sm hidden sm:inline-flex">
                        <x-icon name="layout-dashboard" class="h-4 w-4" /> {{ __('Dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm hidden sm:inline-flex">{{ __('Login') }}</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm hidden sm:inline-flex">{{ __('Open Account') }}</a>
                @endauth
                <button type="button" @click="open = !open" class="grid h-10 w-10 place-items-center rounded-xl text-slate-700 hover:bg-slate-100 lg:hidden dark:text-slate-200 dark:hover:bg-slate-800" :aria-expanded="open" aria-label="{{ __('Menu') }}">
                    <span x-show="!open"><x-icon name="menu" /></span>
                    <span x-show="open" x-cloak><x-icon name="x" /></span>
                </button>
            </div>
        </nav>

        {{-- Mobile menu --}}
        <div x-show="open" x-collapse x-cloak class="border-t border-slate-200 bg-white lg:hidden dark:border-slate-800 dark:bg-slate-950">
            <div class="space-y-1 px-4 py-4">
                <a href="{{ route('home') }}#products" @click="open=false" class="nav-link"><x-icon name="package" />{{ __('Loan Packages') }}</a>
                <a href="{{ route('calculator') }}" class="nav-link"><x-icon name="calculator" />{{ __('Calculator') }}</a>
                <a href="{{ route('loan-terms') }}" class="nav-link"><x-icon name="file-text" />{{ __('Fees & Terms') }}</a>
                <a href="{{ route('home') }}#faq" @click="open=false" class="nav-link"><x-icon name="circle-help" />{{ __('FAQ') }}</a>
                <a href="{{ route('home') }}#contact" @click="open=false" class="nav-link"><x-icon name="phone" />{{ __('Contact') }}</a>
                <div class="grid grid-cols-2 gap-2 pt-3">
                    @auth
                        <a href="{{ route('user.dashboard') }}" class="btn btn-primary col-span-2">{{ __('Dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline">{{ __('Login') }}</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">{{ __('Open Account') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="page-enter flex-1">
        @yield('content')
    </main>

    {{-- ================= Footer ================= --}}
    <footer class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
            <div class="space-y-4">
                <x-brand />
                <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ setting('site_description') }}</p>
                <div class="flex gap-2">
                    @foreach(['facebook_url' => 'facebook', 'youtube_url' => 'youtube', 'linkedin_url' => 'linkedin'] as $key => $icon)
                        @if(setting($key))
                            <a href="{{ setting($key) }}" target="_blank" rel="noopener" class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-slate-600 transition hover:bg-primary hover:text-white dark:bg-slate-800 dark:text-slate-300" aria-label="{{ ucfirst($icon) }}"><x-icon :name="$icon" class="h-[18px] w-[18px]" /></a>
                        @endif
                    @endforeach
                </div>
            </div>
            <div>
                <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('Quick Links') }}</h4>
                <ul class="space-y-2.5 text-sm text-slate-500 dark:text-slate-400">
                    <li><a href="{{ route('calculator') }}" class="hover:text-primary">{{ __('Loan Calculator') }}</a></li>
                    <li><a href="{{ route('home') }}#products" class="hover:text-primary">{{ __('Loan Packages') }}</a></li>
                    <li><a href="{{ route('user.applications.create') }}" class="hover:text-primary">{{ __('Apply for a Loan') }}</a></li>
                    <li><a href="{{ route('home') }}#faq" class="hover:text-primary">{{ __('FAQ') }}</a></li>
                </ul>
            </div>
            <div>
                <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('Legal') }}</h4>
                <ul class="space-y-2.5 text-sm text-slate-500 dark:text-slate-400">
                    <li><a href="{{ route('loan-terms') }}" class="hover:text-primary">{{ __('Fees & Terms Disclosure') }}</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-primary">{{ __('Terms & Conditions') }}</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-primary">{{ __('Privacy Policy') }}</a></li>
                </ul>
            </div>
            <div>
                <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">{{ __('Contact') }}</h4>
                <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    @if(setting('contact_phone'))<li class="flex items-center gap-2"><x-icon name="phone" class="h-4 w-4 text-primary" /><a href="tel:{{ setting('contact_phone') }}" class="hover:text-primary">{{ setting('contact_phone') }}</a></li>@endif
                    @if(setting('support_email'))<li class="flex items-center gap-2"><x-icon name="mail" class="h-4 w-4 text-primary" /><a href="mailto:{{ setting('support_email') }}" class="hover:text-primary">{{ setting('support_email') }}</a></li>@endif
                    @if(setting('office_address'))<li class="flex items-start gap-2"><x-icon name="map-pin" class="mt-0.5 h-4 w-4 text-primary" /><span>{{ setting('office_address') }}</span></li>@endif
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-200 dark:border-slate-800">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>© {{ bn_digits(date('Y')) }} {{ site_name() }}. {{ setting('footer_text') ?: __('All rights reserved.') }}</p>
                @if(setting('license_info'))<p>{{ setting('license_info') }}</p>@endif
            </div>
        </div>
    </footer>

    {{-- WhatsApp support button --}}
    @if(setting('whatsapp_number'))
        <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp_number')) }}" target="_blank" rel="noopener"
           class="fixed bottom-24 right-4 z-40 grid h-14 w-14 place-items-center rounded-full bg-[#25D366] text-white shadow-xl shadow-emerald-600/30 transition hover:scale-110 md:bottom-6"
           aria-label="{{ __('WhatsApp Support') }}">
            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="currentColor" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.04 21.5h-.01a9.4 9.4 0 0 1-4.8-1.31l-.34-.2-3.57.93.95-3.48-.22-.36a9.43 9.43 0 1 1 8 4.42zm8.03-17.47A11.33 11.33 0 0 0 12.04.5C5.78.5.68 5.6.68 11.87c0 2 .52 3.96 1.52 5.68L.58 23.5l6.08-1.6a11.34 11.34 0 0 0 5.37 1.37h.01c6.26 0 11.36-5.1 11.36-11.37 0-3.03-1.18-5.89-3.33-8.03z"/></svg>
        </a>
    @endif

    {{-- Mobile bottom navigation --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-xl md:hidden dark:border-slate-800 dark:bg-slate-950/95">
        <div class="grid grid-cols-4">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold {{ request()->routeIs('home') ? 'text-primary' : 'text-slate-500' }}"><x-icon name="house" />{{ __('Home') }}</a>
            <a href="{{ route('calculator') }}" class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold {{ request()->routeIs('calculator') ? 'text-primary' : 'text-slate-500' }}"><x-icon name="calculator" />{{ __('Calculator') }}</a>
            <a href="{{ route('user.applications.create') }}" class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold text-slate-500"><span class="-mt-6 grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg shadow-primary/40"><x-icon name="plus" /></span>{{ __('Apply') }}</a>
            <a href="{{ auth()->check() ? route('user.dashboard') : route('login') }}" class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold text-slate-500"><x-icon name="circle-user-round" />{{ auth()->check() ? __('Account') : __('Login') }}</a>
        </div>
    </nav>
</div>
@endsection
