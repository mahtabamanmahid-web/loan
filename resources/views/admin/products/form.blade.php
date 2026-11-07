@extends('layouts.admin')
@section('title', $product->exists ? __('Edit Package') : __('Add Package'))

@php
    $terms = old('terms', $product->exists ? $product->terms->map(fn ($t) => ['months' => $t->months, 'interest_rate' => (float) $t->interest_rate, 'is_active' => $t->is_active])->all() : [['months' => 3, 'interest_rate' => 12, 'is_active' => true], ['months' => 6, 'interest_rate' => 12, 'is_active' => true]]);
@endphp

@section('content')
<x-page-header :title="$product->exists ? __('Edit Package') : __('Add Package')" :back="route('admin.products.index')" />

<form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" class="grid gap-6 xl:grid-cols-3">
    @csrf
    @if($product->exists) @method('PUT') @endif

    <div class="space-y-6 xl:col-span-2">
        <div class="card card-pad grid gap-5 sm:grid-cols-2">
            <h3 class="font-bold sm:col-span-2">{{ __('Basic information') }}</h3>
            <x-input name="name" :label="__('Name (English)')" :value="$product->name" required />
            <x-input name="name_bn" :label="__('Name (Bangla)')" :value="$product->name_bn" />
            <x-textarea name="description" :label="__('Description (English)')" :value="$product->description" rows="2" />
            <x-textarea name="description_bn" :label="__('Description (Bangla)')" :value="$product->description_bn" rows="2" />
            <x-input name="icon" :label="__('Icon name')" :value="$product->icon" required :hint="__('Any Lucide icon name, e.g. wallet, store, graduation-cap')" />
            <x-input name="sort_order" type="number" :label="__('Display order')" :value="$product->sort_order" min="0" />
            <x-input name="min_amount" type="number" :label="__('Minimum Loan Amount')" :value="$product->min_amount ? (float) $product->min_amount : null" min="1" required />
            <x-input name="max_amount" type="number" :label="__('Maximum Loan Amount')" :value="$product->max_amount ? (float) $product->max_amount : null" min="1" required />
            <x-select name="repayment_frequency" :label="__('Repayment Frequency')" :options="['monthly' => __('Monthly'), 'weekly' => __('Weekly')]" :selected="$product->repayment_frequency" />
            <label class="flex items-center gap-2 self-end pb-3 text-sm"><input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $product->is_active))>{{ __('Active (visible to customers)') }}</label>
        </div>

        {{-- Durations & interest rates --}}
        <div class="card card-pad" x-data="{ rows: @js(array_values($terms)) }">
            <div class="mb-4 flex items-center justify-between"><h3 class="font-bold">{{ __('Durations & interest rates') }}</h3>
                <button type="button" @click="rows.push({ months: '', interest_rate: '', is_active: true })" class="btn btn-outline btn-sm"><x-icon name="plus" class="h-4 w-4" />{{ __('Add duration') }}</button></div>
            <p class="hint mb-4">{{ __('Interest rate is a flat yearly percentage. Each duration can have its own rate.') }}</p>
            @error('terms')<p class="error-text mb-2">{{ $message }}</p>@enderror
            <template x-for="(row, i) in rows" :key="i">
                <div class="mb-3 grid grid-cols-12 items-center gap-3">
                    <div class="col-span-4"><input type="number" min="1" max="120" class="input" :name="`terms[${i}][months]`" x-model="row.months" placeholder="{{ __('Months') }}" required></div>
                    <div class="col-span-4"><input type="number" min="0" max="100" step="0.01" class="input" :name="`terms[${i}][interest_rate]`" x-model="row.interest_rate" placeholder="{{ __('Rate %') }}" required></div>
                    <label class="col-span-3 flex items-center gap-2 text-sm"><input type="checkbox" value="1" class="checkbox" :name="`terms[${i}][is_active]`" x-model="row.is_active">{{ __('Active') }}</label>
                    <button type="button" class="col-span-1 text-rose-500" @click="rows.splice(i, 1)" x-show="rows.length > 1" aria-label="{{ __('Remove') }}"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                </div>
            </template>
            @foreach($errors->get('terms.*') as $msgs)@foreach($msgs as $m)<p class="error-text">{{ $m }}</p>@endforeach @endforeach
        </div>
    </div>

    <div class="space-y-6">
        {{-- Fee disclosure --}}
        <div class="card card-pad space-y-4">
            <h3 class="font-bold">{{ __('Processing fee & disclosure') }}</h3>
            <div class="grid grid-cols-2 gap-3">
                <x-select name="processing_fee_type" :label="__('Fee type')" :options="['percent' => '%', 'fixed' => '৳ '.__('Fixed')]" :selected="$product->processing_fee_type" />
                <x-input name="processing_fee_value" type="number" step="0.01" min="0" :label="__('Fee value')" :value="(float) $product->processing_fee_value" required />
            </div>
            <x-select name="fee_collection" :label="__('How the fee is collected')" :options="['deduct' => __('Deducted from the disbursed amount'), 'upfront' => __('Paid online after approval, before disbursement')]" :selected="$product->fee_collection" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="fee_refundable" value="1" class="checkbox" @checked(old('fee_refundable', $product->fee_refundable))>{{ __('Fee is refundable') }}</label>
            <x-textarea name="fee_purpose" :label="__('Why this fee is charged')" :value="$product->fee_purpose" rows="2" />
            <x-textarea name="fee_refund_policy" :label="__('Refund conditions')" :value="$product->fee_refund_policy" rows="3" />
            <x-alert type="info">{{ __('Fees are never collected before approval. Everything entered here is shown to customers before they sign.') }}</x-alert>
        </div>
        <div class="card card-pad grid grid-cols-2 gap-3">
            <h3 class="col-span-2 font-bold">{{ __('Late payment') }}</h3>
            <x-input name="late_fee_per_installment" type="number" step="0.01" min="0" :label="__('Late fee (৳)')" :value="(float) $product->late_fee_per_installment" required />
            <x-input name="grace_days" type="number" min="0" max="60" :label="__('Grace days')" :value="$product->grace_days" required />
        </div>
        <button class="btn btn-primary btn-lg w-full"><x-icon name="save" />{{ __('Save Package') }}</button>
    </div>
</form>
@endsection
