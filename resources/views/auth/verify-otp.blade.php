@extends('layouts.auth')
@section('title', __('Verify Mobile'))

@section('content')
<div class="mb-8 text-center">
    <span class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg shadow-primary/30 animate-float"><x-icon name="message-square-lock" class="h-8 w-8" /></span>
    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('Verify your mobile number') }}</h1>
    <p class="mt-2 text-slate-500">{{ __('We sent a 6-digit code to') }} <strong class="text-slate-800 dark:text-white">{{ bn_digits($phone) }}</strong></p>
</div>

<form method="POST" action="{{ route('otp.verify') }}" class="space-y-6">
    @csrf
    @include('auth.partials.otp-input')
    <button type="submit" class="btn btn-primary btn-lg w-full"><x-icon name="shield-check" />{{ __('Verify') }}</button>
</form>

<div class="mt-6 flex items-center justify-between text-sm" x-data="{ wait: 60 }" x-init="let t = setInterval(() => { wait--; if (wait <= 0) clearInterval(t) }, 1000)">
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="font-semibold text-slate-500 hover:text-rose-600">{{ __('Logout') }}</button></form>
    <form method="POST" action="{{ route('otp.resend') }}">
        @csrf
        <button type="submit" class="font-semibold text-primary disabled:text-slate-400" :disabled="wait > 0">
            <span x-show="wait > 0">{{ __('Resend code in') }} <span x-text="digits(wait)"></span>s</span>
            <span x-show="wait <= 0" x-cloak>{{ __('Resend code') }}</span>
        </button>
    </form>
</div>
@endsection
