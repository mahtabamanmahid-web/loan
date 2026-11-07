{{-- Shared error page. Settings fall back to defaults if the database is unavailable. --}}
@extends('layouts.base')
@section('robots')<meta name="robots" content="noindex">@endsection

@section('body')
<div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4">
    <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-primary/15 blur-3xl animate-blob"></div>
    <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-secondary/15 blur-3xl animate-blob" style="animation-delay:-5s"></div>
    <div class="page-enter relative max-w-lg text-center">
        <div class="mb-6 flex justify-center"><x-brand /></div>
        <p class="gradient-text text-8xl font-black tracking-tight sm:text-9xl">@yield('code')</p>
        <span class="mx-auto mt-4 grid h-14 w-14 place-items-center rounded-2xl bg-white shadow-lg dark:bg-slate-900"><x-icon :name="trim($__env->yieldContent('icon', 'circle-alert'))" class="h-7 w-7 text-primary" /></span>
        <h1 class="mt-5 text-2xl font-extrabold text-slate-900 sm:text-3xl dark:text-white">@yield('heading')</h1>
        <p class="mt-3 text-slate-600 dark:text-slate-400">@yield('message')</p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ url('/') }}" class="btn btn-primary"><x-icon name="house" />{{ __('Go to Homepage') }}</a>
            <button type="button" onclick="history.back()" class="btn btn-outline"><x-icon name="arrow-left" />{{ __('Go Back') }}</button>
        </div>
    </div>
</div>
@endsection
