@extends('layouts.auth')
@section('title', __('Forgot Password'))

@section('content')
<div class="mb-8">
    <span class="mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg shadow-primary/30"><x-icon name="key-round" class="h-7 w-7" /></span>
    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('Forgot your password?') }}</h1>
    <p class="mt-1 text-slate-500">{{ __('Contact our support team with your registered mobile number. After checking your identity, we will set a new password for you.') }}</p>
</div>

<div class="space-y-3">
    @if(setting('whatsapp_number'))
        <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp_number')) }}" target="_blank" rel="noopener" class="btn btn-success btn-lg w-full"><x-icon name="message-circle" />WhatsApp: {{ setting('whatsapp_number') }}</a>
    @endif
    @if(setting('contact_phone'))
        <a href="tel:{{ setting('contact_phone') }}" class="btn btn-outline btn-lg w-full"><x-icon name="phone" />{{ __('Call') }}: {{ setting('contact_phone') }}</a>
    @endif
    @if(! setting('whatsapp_number') && ! setting('contact_phone'))
        <x-alert type="info">{{ __('Please contact our office to reset your password.') }}</x-alert>
    @endif
    <x-alert type="warning" icon="shield-alert">{{ __('We will never ask for your password. Never share it with anyone.') }}</x-alert>
</div>

<p class="mt-8 text-center text-sm"><a href="{{ route('login') }}" class="inline-flex items-center gap-1 font-semibold text-primary"><x-icon name="arrow-left" class="h-4 w-4" />{{ __('Back to login') }}</a></p>
@endsection
