@extends('layouts.admin')
@section('title', __('System & Maintenance'))

@section('content')
<x-page-header :title="__('System & Maintenance')" />

<form method="POST" action="{{ route('admin.settings.system.update') }}" class="max-w-2xl space-y-6">
    @csrf @method('PUT')
    <div class="card card-pad space-y-4" x-data="{ on: {{ $s['maintenance_mode'] == '1' ? 'true' : 'false' }} }">
        <label class="flex items-center justify-between gap-4">
            <span><span class="block font-bold">{{ __('System Maintenance Mode') }}</span><span class="text-sm text-slate-500">{{ __('Customers see a maintenance page. Admin panel and payment callbacks keep working.') }}</span></span>
            <input type="checkbox" name="maintenance_mode" value="1" class="checkbox h-6 w-6" x-model="on">
        </label>
        <x-textarea name="maintenance_message" :label="__('Message shown to visitors')" :value="$s['maintenance_message']" rows="2" />
    </div>
    <div class="card card-pad space-y-4">
        <label class="flex items-center justify-between gap-4"><span class="font-semibold">{{ __('Allow new registrations') }}</span><input type="checkbox" name="registration_enabled" value="1" class="checkbox h-6 w-6" @checked($s['registration_enabled'] == '1')></label>
        <label class="flex items-center justify-between gap-4"><span><span class="block font-semibold">{{ __('Require OTP mobile verification') }}</span><span class="text-sm text-slate-500">{{ __('Turn off only if your SMS gateway is not configured yet.') }}</span></span><input type="checkbox" name="otp_enabled" value="1" class="checkbox h-6 w-6" @checked($s['otp_enabled'] == '1')></label>
    </div>
    <div class="card card-pad text-sm">
        <h3 class="mb-2 font-bold">{{ __('Environment') }}</h3>
        <dl class="grid grid-cols-2 gap-2">
            <dt class="text-slate-500">Laravel</dt><dd>{{ app()->version() }}</dd>
            <dt class="text-slate-500">PHP</dt><dd>{{ PHP_VERSION }}</dd>
            <dt class="text-slate-500">{{ __('Environment') }}</dt><dd>{{ app()->environment() }} {{ config('app.debug') ? '(DEBUG ON!)' : '' }}</dd>
            <dt class="text-slate-500">UddoktaPay</dt><dd>{{ config('uddoktapay.mode') }} · {{ app(\App\Services\UddoktaPayService::class)->isConfigured() ? __('configured') : __('NOT configured') }}</dd>
            <dt class="text-slate-500">SMS</dt><dd>{{ config('sms.driver') }}</dd>
            <dt class="text-slate-500">Mail</dt><dd>{{ config('mail.default') }}</dd>
        </dl>
    </div>
    <button class="btn btn-primary btn-lg"><x-icon name="save" />{{ __('Save Settings') }}</button>
</form>
@endsection
