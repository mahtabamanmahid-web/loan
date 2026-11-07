@extends('layouts.user')
@section('title', __('My Profile'))

@php($p = $user->profile)

@section('content')
<x-page-header :title="__('My Profile')" />

<div class="grid gap-6 lg:grid-cols-3">
    {{-- Left: photo + account info --}}
    <div class="space-y-6">
        <div class="card card-pad text-center">
            <form method="POST" action="{{ route('user.profile.avatar') }}" enctype="multipart/form-data" x-data x-ref="f">
                @csrf
                <label class="group relative mx-auto block h-28 w-28 cursor-pointer">
                    <img src="{{ $user->avatarUrl() }}" alt="" class="h-28 w-28 rounded-3xl object-cover ring-4 ring-primary/20">
                    <span class="absolute inset-0 grid place-items-center rounded-3xl bg-slate-900/50 text-white opacity-0 transition group-hover:opacity-100"><x-icon name="camera" /></span>
                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="$refs.f.submit()">
                </label>
                @error('avatar')<p class="error-text justify-center">{{ $message }}</p>@enderror
            </form>
            <h2 class="mt-4 text-lg font-bold">{{ $user->name }}</h2>
            <p class="text-sm text-slate-500">{{ $user->phone }}</p>
            <div class="mt-3">
                @if($user->latestDocument)
                    <x-status-badge :status="$user->latestDocument->status" />
                @else
                    <x-badge color="amber" icon="id-card">{{ __('NID not submitted') }}</x-badge>
                @endif
            </div>
        </div>

        <div class="card card-pad">
            <h3 class="mb-3 font-bold">{{ __('Account Information') }}</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Member since') }}</dt><dd>{{ bn_digits($user->created_at->format('d M Y')) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Mobile verified') }}</dt><dd>{{ $user->phone_verified_at ? __('Yes') : __('No') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Last login') }}</dt><dd>{{ $user->last_login_at ? bn_digits($user->last_login_at->format('d M, h:i A')) : '—' }}</dd></div>
            </dl>
            <a href="{{ route('user.profile.logins') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary"><x-icon name="history" class="h-4 w-4" />{{ __('Login History') }}</a>
        </div>

        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="btn btn-outline w-full text-rose-600"><x-icon name="log-out" />{{ __('Logout') }}</button>
        </form>
    </div>

    <div class="space-y-6 lg:col-span-2">
        {{-- Personal info --}}
        <form method="POST" action="{{ route('user.profile.update') }}" class="card card-pad space-y-5">
            @csrf @method('PUT')
            <h3 class="font-bold">{{ __('Personal Information') }}</h3>
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="field"><label class="label">{{ __('Full Name') }}</label><input class="input bg-slate-50 dark:bg-slate-800" value="{{ $user->name }}" disabled><p class="hint">{{ __('Contact support to change your name or mobile.') }}</p></div>
                <div class="field"><label class="label">{{ __('Mobile Number') }}</label><input class="input bg-slate-50 dark:bg-slate-800" value="{{ $user->phone }}" disabled></div>
                <x-input name="email" type="email" :label="__('Email')" icon="mail" :value="$user->email" required />
                <x-input name="date_of_birth" type="date" :label="__('Date of Birth')" :value="$p?->date_of_birth?->format('Y-m-d')" />
                <x-select name="gender" :label="__('Gender')" :options="['' => '—', 'male' => __('Male'), 'female' => __('Female'), 'other' => __('Other')]" :selected="$p?->gender" />
                <x-input name="occupation" :label="__('Occupation')" icon="briefcase" :value="$p?->occupation" />
                <x-input name="monthly_income" type="number" :label="__('Monthly Income (৳)')" :value="$p?->monthly_income ? (int) $p->monthly_income : null" min="0" />
                <div></div>
                <x-textarea name="present_address" :label="__('Present Address')" :value="$p?->present_address" rows="2" />
                <x-textarea name="permanent_address" :label="__('Permanent Address')" :value="$p?->permanent_address" rows="2" />
                <x-input name="emergency_contact_name" :label="__('Emergency Contact Name')" :value="$p?->emergency_contact_name" />
                <x-input name="emergency_contact_phone" :label="__('Emergency Contact Mobile')" :value="$p?->emergency_contact_phone" />
            </div>
            <button class="btn btn-primary"><x-icon name="save" />{{ __('Save Changes') }}</button>
        </form>

        {{-- Password --}}
        <form method="POST" action="{{ route('user.profile.password') }}" class="card card-pad space-y-5">
            @csrf @method('PUT')
            <h3 class="font-bold">{{ __('Change Password') }}</h3>
            <div class="grid gap-5 sm:grid-cols-3">
                <x-input name="current_password" type="password" :label="__('Current Password')" autocomplete="current-password" required />
                <x-input name="password" type="password" :label="__('New Password')" autocomplete="new-password" required />
                <x-input name="password_confirmation" type="password" :label="__('Confirm Password')" autocomplete="new-password" required />
            </div>
            <button class="btn btn-secondary"><x-icon name="key-round" />{{ __('Change Password') }}</button>
        </form>

        {{-- Notification settings --}}
        <form method="POST" action="{{ route('user.profile.notifications') }}" class="card card-pad space-y-4">
            @csrf @method('PUT')
            <h3 class="font-bold">{{ __('Notification Settings') }}</h3>
            <p class="text-sm text-slate-500">{{ __('In-app notifications are always on.') }}</p>
            <label class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800"><span class="flex items-center gap-3"><x-icon name="mail" />{{ __('Email notifications') }}</span><input type="checkbox" name="email" value="1" class="checkbox" @checked($user->wantsNotification('email'))></label>
            <label class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800"><span class="flex items-center gap-3"><x-icon name="message-square" />{{ __('SMS notifications') }}</span><input type="checkbox" name="sms" value="1" class="checkbox" @checked($user->wantsNotification('sms'))></label>
            <button class="btn btn-outline"><x-icon name="save" />{{ __('Save') }}</button>
        </form>
    </div>
</div>
@endsection
