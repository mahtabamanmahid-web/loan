@extends('layouts.auth')
@section('title', __('Reset Password'))

@section('content')
<h1 class="mb-8 text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('Set a new password') }}</h1>

<form method="POST" action="{{ route('password.update') }}" class="space-y-5">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <x-input name="email" type="email" :label="__('Email')" icon="mail" :value="$email" required />
    <x-input name="password" type="password" :label="__('New Password')" icon="lock" autocomplete="new-password" required />
    <x-input name="password_confirmation" type="password" :label="__('Confirm Password')" icon="lock-keyhole" autocomplete="new-password" required />
    <button type="submit" class="btn btn-primary btn-lg w-full"><x-icon name="check" />{{ __('Change Password') }}</button>
</form>
@endsection
