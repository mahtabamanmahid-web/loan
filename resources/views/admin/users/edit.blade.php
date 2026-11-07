@extends('layouts.admin')
@section('title', __('Edit Customer'))

@section('content')
<x-page-header :title="__('Edit Customer')" :subtitle="$user->name" :back="route('admin.users.show', $user)" />
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="card card-pad max-w-2xl space-y-5">
    @csrf @method('PUT')
    <x-alert type="warning">{{ __('Change name or mobile only after checking the customer\'s NID. All changes are logged.') }}</x-alert>
    <x-input name="name" :label="__('Full Name')" :value="$user->name" required />
    <div class="grid gap-5 sm:grid-cols-2">
        <x-input name="phone" :label="__('Mobile Number')" :value="$user->phone" required />
        <x-input name="email" type="email" :label="__('Email')" :value="$user->email" :hint="__('Optional')" />
    </div>
    <x-input name="password" type="password" :label="__('New Password')" :hint="__('Use this when a customer forgot the password. Leave empty to keep the current password.')" autocomplete="new-password" />
    <button class="btn btn-primary"><x-icon name="save" />{{ __('Save Changes') }}</button>
</form>
@endsection
