@extends('layouts.admin')
@section('title', __('Loan Settings'))

@section('content')
<x-page-header :title="__('Loan Settings')" :subtitle="__('Global rules applied to every loan package.')" />

<form method="POST" action="{{ route('admin.settings.loan.update') }}" class="grid gap-6 lg:grid-cols-2">
    @csrf @method('PUT')
    <div class="card card-pad grid gap-5 sm:grid-cols-2">
        <h3 class="font-bold sm:col-span-2">{{ __('Amounts & rates') }}</h3>
        <x-input name="min_loan_amount" type="number" :label="__('Minimum Loan Amount')" :value="$s['min_loan_amount']" required />
        <x-input name="max_loan_amount" type="number" :label="__('Maximum Loan Amount')" :value="$s['max_loan_amount']" required />
        <div class="sm:col-span-2"><x-input name="loan_amount_presets" :label="__('Amount buttons in calculator')" :value="$s['loan_amount_presets']" :hint="__('Comma separated, e.g. 5000,10000,20000')" /></div>
        <x-input name="default_interest_rate" type="number" step="0.01" :label="__('Default Interest Rate (%/year)')" :value="$s['default_interest_rate']" required :hint="__('Used by the public repayment & eligibility calculators.')" />
        <x-input name="reminder_days_before" type="number" :label="__('Send reminder (days before due)')" :value="$s['reminder_days_before']" required />
    </div>

    <div class="card card-pad grid gap-5 sm:grid-cols-2">
        <h3 class="font-bold sm:col-span-2">{{ __('Eligible Customer Rules') }}</h3>
        <x-input name="min_monthly_income" type="number" :label="__('Minimum monthly income')" :value="$s['min_monthly_income']" required />
        <x-input name="max_income_multiplier" type="number" step="0.1" :label="__('Max loan = income ×')" :value="$s['max_income_multiplier']" required />
        <x-input name="min_age" type="number" :label="__('Minimum age')" :value="$s['min_age']" required />
        <x-input name="max_open_applications" type="number" :label="__('Max applications in progress')" :value="$s['max_open_applications']" required />
        <h3 class="mt-2 font-bold sm:col-span-2">{{ __('Application Rules') }}</h3>
        <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" name="require_selfie" value="1" class="checkbox" @checked($s['require_selfie'] == '1')>{{ __('Require selfie with NID') }}</label>
    </div>

    <div class="card card-pad grid gap-5 sm:grid-cols-2 lg:col-span-2">
        <h3 class="font-bold sm:col-span-2">{{ __('Lender details (shown in fee disclosure, agreements & receipts)') }}</h3>
        <x-input name="lender_legal_name" :label="__('Registered institution name')" :value="$s['lender_legal_name']" />
        <x-input name="lender_license_number" :label="__('License / registration number')" :value="$s['lender_license_number']" />
        <x-alert type="warning" class="sm:col-span-2">{{ __('Lending to the public in Bangladesh requires a licence or registration (e.g. MRA, Bangladesh Bank). Do not operate without the required approval.') }}</x-alert>
    </div>

    <div class="lg:col-span-2"><button class="btn btn-primary btn-lg"><x-icon name="save" />{{ __('Save Settings') }}</button></div>
</form>
@endsection
