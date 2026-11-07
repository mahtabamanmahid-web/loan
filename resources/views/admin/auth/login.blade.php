@extends('layouts.base')
@section('title', __('Admin Login'))
@section('robots')<meta name="robots" content="noindex, nofollow">@endsection

@section('body')
<div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 px-4">
    <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-primary/30 blur-3xl animate-blob"></div>
    <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-secondary/30 blur-3xl animate-blob" style="animation-delay:-5s"></div>

    <div class="page-enter relative w-full max-w-md rounded-3xl border border-white/10 bg-white p-8 shadow-2xl dark:bg-slate-900">
        <div class="mb-8 flex flex-col items-center text-center">
            <x-brand />
            <span class="mt-5 inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-slate-600 dark:bg-slate-800 dark:text-slate-300"><x-icon name="shield" class="h-3.5 w-3.5" />{{ __('Admin Panel') }}</span>
            <h1 class="mt-3 text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('Staff Login') }}</h1>
        </div>
        <form method="POST" action="{{ route('admin.login') }}" class="space-y-5">
            @csrf
            <x-input name="login" :label="__('Email or mobile')" icon="mail" autocomplete="username" required autofocus />
            <x-input name="password" type="password" :label="__('Password')" icon="lock" autocomplete="current-password" required />
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300"><input type="checkbox" name="remember" value="1" class="checkbox">{{ __('Remember me') }}</label>
            <button type="submit" class="btn btn-primary btn-lg w-full"><x-icon name="log-in" />{{ __('Login') }}</button>
        </form>
        <p class="mt-6 text-center text-xs text-slate-400">{{ __('All admin activity is logged.') }}</p>
    </div>
</div>
@endsection
