@extends('layouts.admin')
@section('title', __('Payment Method'))

@section('content')
<x-page-header :title="$method->exists ? __('Edit Payment Method') : __('Add Payment Method')" :back="route('admin.payment-methods.index')" />

<form method="POST" action="{{ $method->exists ? route('admin.payment-methods.update', $method) : route('admin.payment-methods.store') }}" enctype="multipart/form-data" class="card card-pad grid max-w-3xl gap-5 sm:grid-cols-2">
    @csrf
    @if($method->exists) @method('PUT') @endif

    <x-input name="name" :label="__('Name')" :value="$method->name" required placeholder="bKash" />
    <x-select name="type" :label="__('Type')" :selected="$method->type" :options="[
        'bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'upay' => 'Upay', 'bank' => __('Bank'), 'other' => __('Other'),
    ]" />
    <x-input name="account_number" :label="__('Number / Account')" :value="$method->account_number" placeholder="01XXXXXXXXX" />
    <x-select name="account_kind" :label="__('Account type')" :selected="$method->account_kind" :placeholder="'—'" :options="[
        'personal' => __('Personal'), 'agent' => __('Agent'), 'merchant' => __('Merchant'),
    ]" />
    <x-input name="account_name" :label="__('Account name')" :value="$method->account_name" />
    <x-input name="sort_order" type="number" min="0" :label="__('Display order')" :value="$method->sort_order" />
    <div class="sm:col-span-2"><x-textarea name="instructions" :label="__('Instructions (how to pay)')" :value="$method->instructions" rows="3" :placeholder="__('e.g. Send Money to this number, then enter the Transaction ID below.')" /></div>

    <div class="field sm:col-span-2">
        <label class="label">{{ __('QR code image (optional)') }}</label>
        @if($method->qrUrl())<img src="{{ $method->qrUrl() }}" alt="QR" class="mb-2 h-28 w-28 rounded-lg border border-slate-200 object-contain dark:border-slate-700">@endif
        <input type="file" name="qr" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:font-semibold file:text-primary">
        @if($method->qr_path)<label class="mt-1 flex items-center gap-2 text-xs text-rose-600"><input type="checkbox" name="remove_qr" value="1" class="checkbox h-4 w-4">{{ __('Remove') }}</label>@endif
        @error('qr')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $method->is_active))>{{ __('Active (shown to customers)') }}</label>
    <div class="sm:col-span-2"><button class="btn btn-primary"><x-icon name="save" />{{ __('Save') }}</button></div>
</form>
@endsection
