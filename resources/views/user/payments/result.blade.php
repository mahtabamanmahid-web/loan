{{-- Shown after returning from UddoktaPay. Status comes from SERVER verification, not from the browser redirect. --}}
@extends('layouts.user')
@section('title', $success ? __('Payment successful') : __('Payment status'))

@php($s = $tx->status->value)

@section('content')
<div class="mx-auto max-w-lg py-6 text-center">
    @if($success)
        {{-- Animated success tick --}}
        <div class="mx-auto grid h-28 w-28 place-items-center rounded-full bg-emerald-100 animate-pop dark:bg-emerald-500/15">
            <svg viewBox="0 0 52 52" class="h-16 w-16 text-emerald-600" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path class="check-draw" d="M14 27l8 8 16-16"/></svg>
        </div>
        <h1 class="mt-6 text-3xl font-extrabold text-emerald-600">{{ __('Payment successful') }}</h1>
        <p class="mt-2 text-slate-500">{{ __('We have verified your payment with the gateway.') }}</p>
    @elseif(in_array($s, ['pending', 'initiated']))
        <div class="mx-auto grid h-28 w-28 place-items-center rounded-full bg-amber-100 animate-pop dark:bg-amber-500/15"><x-icon name="clock" class="h-14 w-14 text-amber-600" /></div>
        <h1 class="mt-6 text-3xl font-extrabold text-amber-600">{{ __('Payment pending') }}</h1>
        <p class="mt-2 text-slate-500">{{ __('Your payment is being processed. We will update it automatically once confirmed.') }}</p>
    @else
        <div class="mx-auto grid h-28 w-28 place-items-center rounded-full bg-rose-100 animate-shake dark:bg-rose-500/15"><x-icon name="circle-x" class="h-14 w-14 text-rose-600" /></div>
        <h1 class="mt-6 text-3xl font-extrabold text-rose-600">{{ $s === 'cancelled' ? __('Payment cancelled') : __('Payment failed') }}</h1>
        <p class="mt-2 text-slate-500">{{ $tx->failure_reason ?: __('No money was taken. You can try again.') }}</p>
    @endif

    <div class="card card-pad mt-8 text-left">
        <dl class="space-y-2.5 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Amount') }}</dt><dd class="font-bold">{{ money($tx->amount) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Payment Purpose') }}</dt><dd class="font-semibold">{{ $tx->purposeLabel() }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Payment Reference') }}</dt><dd class="font-mono text-xs">{{ $tx->reference }}</dd></div>
            @if($tx->gateway_transaction_id)<div class="flex justify-between"><dt class="text-slate-500">{{ __('Transaction ID') }}</dt><dd class="font-mono text-xs">{{ $tx->gateway_transaction_id }}</dd></div>@endif
            @if($tx->payment_method)<div class="flex justify-between"><dt class="text-slate-500">{{ __('Payment Method') }}</dt><dd>{{ ucfirst($tx->payment_method) }}</dd></div>@endif
        </dl>
        @if($tx->purpose === 'processing_fee' && $success)
            <p class="mt-4 rounded-xl bg-sky-50 p-3 text-xs text-sky-800 dark:bg-sky-500/10 dark:text-sky-200">{{ __('Note: a successful fee payment confirms only this payment. It is not a loan approval or disbursement.') }}</p>
        @endif
    </div>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
        @if($success)<a href="{{ route('user.transactions.receipt', $tx) }}" class="btn btn-outline flex-1" data-no-loading><x-icon name="download" />{{ __('Receipt') }}</a>@endif
        @if($tx->loan_id)
            <a href="{{ route('user.loans.show', $tx->loan_id) }}" class="btn btn-primary flex-1">{{ __('Back to loan') }}</a>
        @elseif($tx->loan_application_id)
            <a href="{{ route('user.applications.show', $tx->loan_application_id) }}" class="btn btn-primary flex-1">{{ __('Back to application') }}</a>
        @else
            <a href="{{ route('user.dashboard') }}" class="btn btn-primary flex-1">{{ __('Dashboard') }}</a>
        @endif
    </div>
</div>
@endsection
