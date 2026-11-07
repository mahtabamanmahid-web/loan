@extends('layouts.user')
@section('title', $loan->loan_number)

@section('content')
<x-page-header :title="__('Loan').' '.$loan->loan_number" :subtitle="$loan->application->product->displayName()" :back="route('user.loans.index')">
    <x-status-badge :status="$loan->status" size="lg" />
</x-page-header>

<div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    <x-stat-card icon="banknote" :label="__('Loan Amount')" :value="money($loan->principal)" color="indigo" />
    <x-stat-card icon="calculator" :label="__('Total Payable')" :value="money((float) $loan->total_payable + (float) $loan->total_late_fee)" color="violet" />
    <x-stat-card icon="circle-check" :label="__('Total Paid')" :value="money($loan->total_paid)" color="emerald" />
    <x-stat-card icon="wallet" :label="__('Remaining')" :value="money($loan->outstanding())" color="rose" />
</div>

<div class="card card-pad mt-6">
    <div class="mb-2 flex justify-between text-sm"><span class="font-semibold">{{ __('Repayment Progress') }}</span><span class="font-bold">{{ bn_digits($loan->progressPercent()) }}%</span></div>
    <div class="h-4 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-gradient-to-r from-primary to-secondary transition-all duration-1000" style="width: {{ $loan->progressPercent() }}%"></div></div>
    <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
        <p><span class="text-slate-500">{{ __('Disbursed') }}:</span> <strong>{{ money($loan->disbursed_amount) }}</strong> · {{ bn_digits($loan->disbursed_at->format('d M Y')) }}</p>
        <p><span class="text-slate-500">{{ __('Interest Rate (flat, yearly)') }}:</span> <strong>{{ bn_digits((float) $loan->interest_rate) }}%</strong></p>
        <p><span class="text-slate-500">{{ __('Installment') }}:</span> <strong>{{ money($loan->installment_amount) }}</strong></p>
    </div>
</div>

{{-- Pay next installment --}}
@if($next && $loan->status === \App\Enums\LoanStatus::Active)
    <div class="card card-pad mt-6 border-2 border-primary/30" x-data="{ custom: false }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-500">{{ __('Next Installment') }} #{{ bn_digits($next->installment_number) }} · {{ __('Due') }} {{ bn_digits($next->due_date->format('d M Y')) }}</p>
                <p class="text-3xl font-extrabold">{{ money($next->dueAmount()) }}</p>
                @if((float) $next->late_fee > 0)<p class="text-xs text-rose-600">{{ __('Includes late fee') }} {{ money($next->late_fee) }}</p>@endif
            </div>
            <form method="POST" action="{{ route('user.installments.pay', $next) }}" class="flex flex-col gap-2 sm:items-end">
                @csrf
                <div x-show="custom" x-cloak class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">৳</span>
                    <input type="number" name="amount" min="1" max="{{ $loan->outstanding() }}" step="0.01" class="input w-44 pl-7" :disabled="!custom" placeholder="{{ __('Amount') }}">
                </div>
                <button class="btn btn-primary btn-lg"><x-icon name="credit-card" />{{ __('Pay Now') }}</button>
                <button type="button" @click="custom = !custom" class="text-xs font-semibold text-primary" x-text="custom ? @js(__('Pay full installment')) : @js(__('Pay a different amount'))"></button>
            </form>
        </div>
        <p class="mt-3 text-xs text-slate-500">{{ __('You will be taken to the secure UddoktaPay page (bKash, Nagad, Rocket, cards). Your payment is confirmed only after server verification.') }}</p>
    </div>
@endif

{{-- Installment schedule --}}
<div class="card mt-6">
    <h2 class="border-b border-slate-100 p-5 font-bold dark:border-slate-800">{{ __('Installment Schedule') }}</h2>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>#</th><th>{{ __('Due date') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Late fee') }}</th><th>{{ __('Paid') }}</th><th>{{ __('Status') }}</th></tr></thead>
            <tbody>
            @foreach($loan->installments as $i)
                <tr>
                    <td>{{ bn_digits($i->installment_number) }}</td>
                    <td>{{ bn_digits($i->due_date->format('d M Y')) }}</td>
                    <td class="font-semibold">{{ money($i->amount) }}</td>
                    <td>{{ (float) $i->late_fee > 0 ? money($i->late_fee) : '—' }}</td>
                    <td>{{ money($i->paid_amount) }}</td>
                    <td><x-status-badge :status="$i->status" /></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Repayment history --}}
<div class="card mt-6">
    <h2 class="border-b border-slate-100 p-5 font-bold dark:border-slate-800">{{ __('Repayment History') }}</h2>
    @forelse($loan->repayments as $r)
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-4 last:border-0 dark:border-slate-800">
            <div><p class="font-semibold">{{ money($r->amount) }}</p><p class="text-xs text-slate-500">{{ bn_digits($r->paid_at->format('d M Y, h:i A')) }} · {{ ucfirst($r->method) }}{{ $r->reference ? ' · '.$r->reference : '' }}</p></div>
            @if($r->payment_transaction_id)<a href="{{ route('user.transactions.show', $r->payment_transaction_id) }}" class="text-sm font-semibold text-primary">{{ __('Receipt') }}</a>@endif
        </div>
    @empty
        <x-empty icon="history" :title="__('No payments yet')" />
    @endforelse
</div>
@endsection
