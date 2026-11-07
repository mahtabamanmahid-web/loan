@extends('layouts.auth')
@section('title', __('Reset Password'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('Set a new password') }}</h1>
    <p class="mt-1 text-slate-500">{{ __('Enter the code sent to') }} <strong>{{ bn_digits($phone) }}</strong></p>
</div>

<form method="POST" action="{{ route('password.otp.update') }}" class="space-y-5">
    @csrf
    @include('auth.partials.otp-input')
    <x-input name="password" type="password" :label="__('New Password')" icon="lock" autocomplete="new-password" required />
    <x-input name="password_confirmation" type="password" :label="__('Confirm Password')" icon="lock-keyhole" autocomplete="new-password" required />
    <button type="submit" class="btn btn-primary btn-lg w-full"><x-icon name="check" />{{ __('Change Password') }}</button>
</form>
@endsection
