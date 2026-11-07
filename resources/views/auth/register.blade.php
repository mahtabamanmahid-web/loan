@extends('layouts.auth')
@section('title', __('Open Account'))

@section('content')
<div class="mb-8">
    <span class="mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg shadow-primary/30"><x-icon name="user-plus" class="h-7 w-7" /></span>
    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('Open a free account') }}</h1>
    <p class="mt-1 text-slate-500">{{ __('It takes 1 minute. We will send a code to verify your mobile.') }}</p>
</div>

@if(! $enabled)
    <x-alert type="warning" :title="__('Registration closed')">{{ __('New registrations are temporarily closed. Please try again later.') }}</x-alert>
@else
<form method="POST" action="{{ route('register') }}" class="space-y-5"
      x-data="{ pw: '', get score() { const p = this.pw; return (p.length >= 8) + (/[A-Za-z]/.test(p) && /\d/.test(p)) + /[^A-Za-z0-9]/.test(p) + (p.length >= 12) } }">
    @csrf
    <x-input name="name" :label="__('Full Name')" icon="user" autocomplete="name" :hint="__('As written on your NID')" required />
    <x-input name="phone" type="tel" :label="__('Mobile Number')" icon="smartphone" autocomplete="tel" inputmode="numeric" maxlength="14" placeholder="01XXXXXXXXX" required />
    <x-input name="email" type="email" :label="__('Email')" icon="mail" autocomplete="email" required />
    <div>
        <x-input name="password" type="password" :label="__('Password')" icon="lock" autocomplete="new-password" x-model="pw" required />
        {{-- Password strength meter --}}
        <div class="mt-2 flex gap-1" aria-hidden="true">
            <template x-for="i in 4">
                <span class="h-1.5 flex-1 rounded-full transition"
                      :class="score >= i ? ['bg-rose-500', 'bg-amber-500', 'bg-sky-500', 'bg-emerald-500'][score - 1] : 'bg-slate-200 dark:bg-slate-700'"></span>
            </template>
        </div>
        <p class="hint">{{ __('At least 8 characters with letters and numbers.') }}</p>
    </div>
    <x-input name="password_confirmation" type="password" :label="__('Confirm Password')" icon="lock-keyhole" autocomplete="new-password" required />

    <label class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300">
        <input type="checkbox" name="terms" value="1" class="checkbox mt-0.5" @checked(old('terms')) required>
        <span>{!! __('I agree to the :terms and :privacy.', [
            'terms' => '<a href="'.route('terms').'" target="_blank" class="font-semibold text-primary underline">'.e(__('Terms & Conditions')).'</a>',
            'privacy' => '<a href="'.route('privacy').'" target="_blank" class="font-semibold text-primary underline">'.e(__('Privacy Policy')).'</a>',
        ]) !!}</span>
    </label>
    @error('terms')<p class="error-text">{{ $message }}</p>@enderror

    <button type="submit" class="btn btn-primary btn-lg w-full"><x-icon name="user-plus" />{{ __('Create Account') }}</button>
</form>
@endif

<p class="mt-8 text-center text-sm text-slate-500">
    {{ __('Already have an account?') }}
    <a href="{{ route('login') }}" class="font-bold text-primary hover:underline">{{ __('Login') }}</a>
</p>
@endsection
