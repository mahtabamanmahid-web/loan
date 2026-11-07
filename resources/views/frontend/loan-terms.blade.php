@extends('layouts.app')
@section('title', __('Fees & Terms Disclosure'))

@section('content')
<div class="mx-auto max-w-5xl space-y-8 px-4 py-10 sm:px-6">
    <x-page-header :title="__('Loan Terms & Fee Disclosure')" :subtitle="__('Every charge is listed here and in your agreement before you sign or pay anything.')" />

    <x-alert type="info" :title="__('Important')">
        <ul class="list-disc space-y-1 pl-5">
            <li>{{ __('Applying is free. No fee is ever collected before your application is approved.') }}</li>
            <li>{{ __('Paying any fee does not guarantee loan approval or disbursement.') }}</li>
            <li>{{ __('We never ask for a "security deposit" or advance payment to release a loan.') }}</li>
            <li>{{ __('Only pay through your dashboard. Never send money to a personal number.') }}</li>
        </ul>
    </x-alert>

    @if(sys_setting('lender_legal_name'))
        <div class="card card-pad flex items-start gap-4">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-primary/10 text-primary"><x-icon name="building-2" /></span>
            <div>
                <p class="text-sm text-slate-500">{{ __('Lender / fee recipient') }}</p>
                <p class="font-bold text-slate-900 dark:text-white">{{ sys_setting('lender_legal_name') }}</p>
                @if(sys_setting('lender_license_number'))<p class="text-sm text-slate-500">{{ __('License No.') }}: {{ sys_setting('lender_license_number') }}</p>@endif
            </div>
        </div>
    @endif

    @foreach($products as $p)
        <div class="card overflow-hidden">
            <div class="flex items-center gap-3 border-b border-slate-200 p-5 dark:border-slate-800">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-primary to-secondary text-white"><x-icon :name="$p->icon" /></span>
                <h2 class="text-lg font-bold">{{ $p->displayName() }}</h2>
            </div>
            <div class="grid gap-6 p-5 md:grid-cols-2">
                <div>
                    <h3 class="mb-3 font-bold">{{ __('Durations & interest rates') }}</h3>
                    <div class="table-wrap">
                        <table class="table">
                            <thead><tr><th>{{ __('Duration') }}</th><th>{{ __('Flat rate / year') }}</th><th>{{ __('Interest on ৳10,000') }}</th></tr></thead>
                            <tbody>
                            @foreach($p->activeTerms as $t)
                                <tr><td>{{ bn_digits($t->months) }} {{ __('months') }}</td><td>{{ bn_digits((float) $t->interest_rate) }}%</td><td>{{ money(10000 * $t->interest_rate / 100 * $t->months / 12) }}</td></tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="hint mt-2">{{ __('Interest = Amount × Rate × Months ÷ 12 (flat).') }}</p>
                </div>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4 rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-slate-500">{{ __('Loan amount') }}</dt><dd class="font-semibold">{{ money(max($p->min_amount, sys_setting('min_loan_amount'))) }} – {{ money(min($p->max_amount, sys_setting('max_loan_amount'))) }}</dd></div>
                    <div class="flex justify-between gap-4 rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-slate-500">{{ __('Processing fee') }}</dt><dd class="font-semibold">{{ bn_digits($p->feeLabel()) }} {{ $p->processing_fee_type === 'percent' ? __('of loan amount') : '' }}</dd></div>
                    <div class="flex justify-between gap-4 rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-slate-500">{{ __('How the fee is collected') }}</dt><dd class="text-right font-semibold">{{ $p->fee_collection === 'deduct' ? __('Deducted from the disbursed amount') : __('Paid online after approval, before disbursement') }}</dd></div>
                    <div class="flex justify-between gap-4 rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-slate-500">{{ __('Refundable?') }}</dt><dd class="font-semibold">{{ $p->fee_refundable ? __('Yes') : __('No') }}</dd></div>
                    @if($p->fee_purpose)<div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-slate-500">{{ __('Why this fee?') }}</dt><dd class="mt-1">{{ $p->fee_purpose }}</dd></div>@endif
                    @if($p->fee_refund_policy)<div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-slate-500">{{ __('Refund conditions') }}</dt><dd class="mt-1">{{ $p->fee_refund_policy }}</dd></div>@endif
                    <div class="flex justify-between gap-4 rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-slate-500">{{ __('Late fee per installment') }}</dt><dd class="font-semibold">{{ (float) $p->late_fee_per_installment > 0 ? money($p->late_fee_per_installment) : __('None') }} ({{ __(':days days grace', ['days' => bn_digits($p->grace_days)]) }})</dd></div>
                </dl>
            </div>
        </div>
    @endforeach

    @if($content)
        <div class="card card-pad prose-content">{!! safe_markdown($content) !!}</div>
    @endif
</div>
@endsection
