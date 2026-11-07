@extends('layouts.auth')
@section('title', __('Login'))

@section('content')
<div class="mb-8">
    <span class="mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg shadow-primary/30"><x-icon name="log-in" class="h-7 w-7" /></span>
    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('Log in to your account') }}</h1>
    <p class="mt-1 text-slate-500">{{ __('Use your mobile number or email and password.') }}</p>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf
    <x-input name="login" :label="__('Mobile number or email')" icon="smartphone" autocomplete="username" inputmode="email" placeholder="01XXXXXXXXX" required autofocus />
    <x-input name="password" type="password" :label="__('Password')" icon="lock" autocomplete="current-password" required />

    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
            <input type="checkbox" name="remember" value="1" class="checkbox"> {{ __('Remember me') }}
        </label>
        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary hover:underline">{{ __('Forgot password?') }}</a>
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-full"><x-icon name="log-in" />{{ __('Login') }}</button>
</form>

<p class="mt-8 text-center text-sm text-slate-500">
    {{ __("Don't have an account?") }}
    <a href="{{ route('register') }}" class="font-bold text-primary hover:underline">{{ __('Open a free account') }}</a>
</p>
@endsection
