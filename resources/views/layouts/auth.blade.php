{{-- Login / registration / OTP layout: brand panel on the left (desktop), form card on the right. --}}
@extends('layouts.base')

@section('body')
<div class="flex min-h-screen">
    {{-- Brand panel --}}
    <div class="relative hidden w-1/2 overflow-hidden bg-gradient-to-br from-primary to-secondary lg:flex lg:flex-col lg:justify-between lg:p-12">
        <div class="absolute -left-20 -top-20 h-96 w-96 rounded-full bg-white/10 blur-3xl animate-blob"></div>
        <div class="absolute -bottom-32 -right-20 h-[28rem] w-[28rem] rounded-full bg-white/10 blur-3xl animate-blob" style="animation-delay:-6s"></div>
        <div class="relative"><x-brand :light="true" /></div>
        <div class="relative space-y-6 text-white">
            <h2 class="text-4xl font-extrabold leading-tight">{{ localized_setting('hero_heading') }}</h2>
            <ul class="space-y-3 text-white/90">
                <li class="flex items-center gap-3"><x-icon name="shield-check" />{{ __('Your data is encrypted and protected') }}</li>
                <li class="flex items-center gap-3"><x-icon name="receipt-text" />{{ __('All fees shown before you sign') }}</li>
                <li class="flex items-center gap-3"><x-icon name="smartphone" />{{ __('Track every step from your phone') }}</li>
            </ul>
        </div>
        <p class="relative text-sm text-white/70">© {{ bn_digits(date('Y')) }} {{ site_name() }}</p>
    </div>

    {{-- Form side --}}
    <div class="flex w-full flex-col lg:w-1/2">
        <div class="flex items-center justify-between p-4 sm:p-6">
            <div class="lg:hidden"><x-brand /></div>
            <a href="{{ route('home') }}" class="hidden items-center gap-2 text-sm font-semibold text-slate-500 hover:text-primary lg:flex"><x-icon name="arrow-left" class="h-4 w-4" />{{ __('Back to home') }}</a>
            @include('partials.toolbar')
        </div>
        <div class="flex flex-1 items-center justify-center px-4 pb-10 sm:px-6">
            <div class="page-enter w-full max-w-md">
                @yield('content')
            </div>
        </div>
    </div>
</div>
@endsection
