@extends('layouts.admin')
@section('title', __('Admin User'))

@section('content')
<x-page-header :title="$admin->exists ? __('Edit Admin') : __('Add Admin')" :back="route('admin.admins.index')" />
<form method="POST" action="{{ $admin->exists ? route('admin.admins.update', $admin) : route('admin.admins.store') }}" class="card card-pad grid max-w-3xl gap-5 sm:grid-cols-2">
    @csrf
    @if($admin->exists) @method('PUT') @endif
    <x-input name="name" :label="__('Full Name')" :value="$admin->name" required />
    <x-select name="admin_role_id" :label="__('Role')" :options="$roles->pluck('name', 'id')->all()" :selected="$admin->admin_role_id" required />
    <x-input name="email" type="email" :label="__('Email')" :value="$admin->email" required />
    <x-input name="phone" :label="__('Mobile Number')" :value="$admin->phone" required />
    <x-input name="password" type="password" :label="__('Password')" autocomplete="new-password" :required="! $admin->exists" :hint="$admin->exists ? __('Leave empty to keep the current password.') : __('Min 10 characters with upper & lower case letters and numbers.')" />
    @if($admin->exists && $admin->id !== auth()->id())
        <label class="flex items-center gap-2 self-end pb-3 text-sm"><input type="checkbox" name="is_blocked" value="1" class="checkbox" @checked($admin->is_blocked)>{{ __('Block this admin') }}</label>
    @endif
    <div class="sm:col-span-2"><button class="btn btn-primary"><x-icon name="save" />{{ __('Save') }}</button></div>
</form>
@endsection
