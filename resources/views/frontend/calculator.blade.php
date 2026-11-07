@extends('layouts.app')
@section('title', __('Loan Calculator'))

@section('content')
<div class="mx-auto max-w-7xl space-y-12 px-4 py-10 sm:px-6 lg:px-8">
    <x-page-header :title="__('Loan Calculator')" :subtitle="__('Estimate your installment, total cost and eligibility.')" />

    {{-- 1. Loan / installment calculator --}}
    @include('frontend.partials.calculator')

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- 2. Smart eligibility calculator (server side via API) --}}
        <div class="card card-pad" x-data="eligibility({ url: '{{ route('api.eligibility') }}', rate: {{ (float) sys_setting('default_interest_rate') }} })">
            <div class="mb-5 flex items-center gap-3">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15"><x-icon name="gauge" /></span>
                <div><h2 class="text-lg font-bold">{{ __('Eligibility Calculator') }}</h2><p class="text-sm text-slate-500">{{ __('How much can you borrow?') }}</p></div>
            </div>
            <form @submit.prevent="check()" data-no-loading class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="field"><label class="label" for="el-income">{{ __('Monthly Income') }} (৳)</label><input id="el-income" type="number" min="0" class="input" x-model.number="income" required></div>
                    <div class="field"><label class="label" for="el-obl">{{ __('Other monthly EMIs') }} (৳)</label><input id="el-obl" type="number" min="0" class="input" x-model.number="obligations" placeholder="0"></div>
                    <div class="field"><label class="label" for="el-amt">{{ __('Desired amount') }} (৳)</label><input id="el-amt" type="number" min="0" class="input" x-model.number="amount" required></div>
                    <div class="field"><label class="label" for="el-m">{{ __('Duration (months)') }}</label><input id="el-m" type="number" min="1" max="120" class="input" x-model.number="months" required></div>
                </div>
                <button type="submit" class="btn btn-success w-full" :class="loading && 'is-loading'"><x-icon name="search-check" />{{ __('Check Eligibility') }}</button>
            </form>
            <template x-if="result">
                <div class="mt-5 animate-fade-up rounded-2xl p-4" :class="result.eligible ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-amber-50 dark:bg-amber-500/10'">
                    <p class="flex items-center gap-2 font-bold" :class="result.eligible ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300'">
                        <span x-text="result.eligible ? @js(__('You may be eligible')) : @js(__('You may not be eligible for this amount'))"></span>
                    </p>
                    <p class="mt-2 text-sm">{{ __('Estimated maximum amount') }}: <strong x-text="money(result.max_amount)"></strong></p>
                    <p class="text-sm">{{ __('Comfortable installment up to') }}: <strong x-text="money(result.max_installment)"></strong></p>
                    <ul class="mt-2 list-disc pl-5 text-sm text-slate-600 dark:text-slate-300"><template x-for="r in result.reasons"><li x-text="r"></li></template></ul>
                    <p class="mt-3 text-xs text-slate-500">{{ __('This is only an estimate and not a loan offer or guarantee.') }}</p>
                </div>
            </template>
            <p x-show="error" x-cloak class="error-text" x-text="error"></p>
        </div>

        {{-- 3. Repayment calculator with any rate --}}
        <div class="card card-pad" x-data="repaymentCalc({ rate: {{ (float) sys_setting('default_interest_rate') }} })">
            <div class="mb-5 flex items-center gap-3">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-500/15"><x-icon name="calendar-range" /></span>
                <div><h2 class="text-lg font-bold">{{ __('Repayment Calculator') }}</h2><p class="text-sm text-slate-500">{{ __('Try any amount, rate and duration.') }}</p></div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="field"><label class="label" for="rc-a">{{ __('Amount') }} (৳)</label><input id="rc-a" type="number" min="0" class="input" x-model.number="amount"></div>
                <div class="field"><label class="label" for="rc-r">{{ __('Rate % / year') }}</label><input id="rc-r" type="number" min="0" step="0.1" class="input" x-model.number="rate"></div>
                <div class="field"><label class="label" for="rc-m">{{ __('Months') }}</label><input id="rc-m" type="number" min="1" class="input" x-model.number="months"></div>
            </div>
            <dl class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800"><dt class="text-xs text-slate-500">{{ __('Monthly installment') }}</dt><dd class="text-xl font-extrabold text-primary" x-text="money(r.installment)"></dd></div>
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800"><dt class="text-xs text-slate-500">{{ __('Total Interest') }}</dt><dd class="text-xl font-extrabold" x-text="money(r.totalInterest)"></dd></div>
                <div class="col-span-2 rounded-xl bg-slate-50 p-4 dark:bg-slate-800"><dt class="text-xs text-slate-500">{{ __('Total Repayment') }}</dt><dd class="text-xl font-extrabold" x-text="money(r.totalPayable)"></dd></div>
            </dl>
        </div>
    </div>
</div>
@endsection
