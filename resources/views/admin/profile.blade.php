@extends('layouts.admin')
@section('title', __('My Account'))

@section('content')
<x-page-header :title="__('My Account')" :subtitle="__('Change your own email and password.')" />

<div class="grid max-w-3xl gap-6">
    <form method="POST" action="{{ route('admin.profile.update') }}" class="card card-pad grid gap-5 sm:grid-cols-2">
        @csrf @method('PUT')
        <h3 class="font-bold sm:col-span-2">{{ __('Profile') }}</h3>
        <x-input name="name" :label="__('Full Name')" :value="$admin->name" required />
        <x-input name="email" type="email" :label="__('Email')" :value="$admin->email" required />
        <div class="field"><label class="label">{{ __('Mobile Number') }}</label><input class="input bg-slate-50 dark:bg-slate-800" value="{{ $admin->phone }}" disabled></div>
        <div class="field"><label class="label">{{ __('Role') }}</label><input class="input bg-slate-50 dark:bg-slate-800" value="{{ $admin->adminRole?->name }}" disabled></div>
        <div class="sm:col-span-2"><button class="btn btn-primary"><x-icon name="save" />{{ __('Save Changes') }}</button></div>
    </form>

    <form method="POST" action="{{ route('admin.profile.password') }}" class="card card-pad grid gap-5 sm:grid-cols-3">
        @csrf @method('PUT')
        <h3 class="font-bold sm:col-span-3">{{ __('Change Password') }}</h3>
        <x-input name="current_password" type="password" :label="__('Current Password')" autocomplete="current-password" required />
        <x-input name="password" type="password" :label="__('New Password')" autocomplete="new-password" required :hint="__('At least 6 characters.')" />
        <x-input name="password_confirmation" type="password" :label="__('Confirm Password')" autocomplete="new-password" required />
        <div class="sm:col-span-3"><button class="btn btn-secondary"><x-icon name="key-round" />{{ __('Change Password') }}</button></div>
    </form>
</div>
@endsection
