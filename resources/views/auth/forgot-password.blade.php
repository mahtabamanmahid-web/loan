@extends('layouts.auth')
@section('title', __('Forgot Password'))

@section('content')
<div class="mb-8">
    <span class="mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg shadow-primary/30"><x-icon name="key-round" class="h-7 w-7" /></span>
    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('Forgot your password?') }}</h1>
    <p class="mt-1 text-slate-500">{{ __('Enter your mobile number to get a code by SMS, or your email to get a reset link.') }}</p>
</div>

<form method="POST" action="{{ route('password.send') }}" class="space-y-5">
    @csrf
    <x-input name="login" :label="__('Mobile number or email')" icon="smartphone" placeholder="01XXXXXXXXX" required autofocus />
    <button type="submit" class="btn btn-primary btn-lg w-full"><x-icon name="send" />{{ __('Send') }}</button>
</form>

<p class="mt-8 text-center text-sm"><a href="{{ route('login') }}" class="inline-flex items-center gap-1 font-semibold text-primary"><x-icon name="arrow-left" class="h-4 w-4" />{{ __('Back to login') }}</a></p>
@endsection
