@extends('layouts.user')
@section('title', __('Pay Security Deposit'))

@section('content')
<x-page-header :title="__('Pay Security Deposit')" :subtitle="$tx->description" :back="route('user.dashboard')" />

<div class="mx-auto max-w-2xl space-y-6">
    <div class="card card-pad text-center">
        <p class="text-sm text-slate-500">{{ __('Amount to pay') }}</p>
        <p class="text-4xl font-extrabold text-primary">{{ money($tx->amount) }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ __('Reference') }}: {{ $tx->reference }}</p>
    </div>

    @if($methods->isEmpty())
        <x-alert type="warning">{{ __('No payment method is available right now. Please contact support.') }}</x-alert>
    @else
        <div class="card card-pad">
            <h2 class="mb-1 font-bold">{{ __('Step 1: Send the money') }}</h2>
            <p class="mb-4 text-sm text-slate-500">{{ __('Send the exact amount to any one of the numbers below, then note the Transaction ID (TrxID).') }}</p>
            <div class="space-y-3">
                @foreach($methods as $m)
                    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/10 text-primary"><x-icon :name="$m->icon()" /></span>
                                <div>
                                    <p class="font-bold">{{ $m->name }} @if($m->account_kind)<span class="text-xs font-normal text-slate-500">({{ __(ucfirst($m->account_kind)) }})</span>@endif</p>
                                    @if($m->account_number)<p class="select-all font-mono text-lg font-bold text-primary">{{ $m->account_number }}</p>@endif
                                </div>
                            </div>
                            @if($m->qrUrl())<img src="{{ $m->qrUrl() }}" alt="QR" class="h-20 w-20 rounded-lg border border-slate-200 object-contain dark:border-slate-700">@endif
                        </div>
                        @if($m->instructions)<p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $m->instructions }}</p>@endif
                    </div>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('payment.manual.submit', $tx) }}" class="card card-pad space-y-4">
            @csrf
            <h2 class="font-bold">{{ __('Step 2: Submit the Transaction ID') }}</h2>
            <x-select name="payment_method" :label="__('Which method did you use?')" required :placeholder="__('Select')"
                      :options="$methods->mapWithKeys(fn ($m) => [$m->name => $m->name])->all()" />
            <x-input name="sender_number" type="tel" :label="__('Your number you sent from')" icon="smartphone" inputmode="numeric" maxlength="14" placeholder="01XXXXXXXXX" required />
            <x-input name="trx_id" :label="__('Transaction ID (TrxID)')" icon="hash" required :hint="__('The TrxID from the bKash/Nagad/Rocket SMS.')" />
            <x-alert type="info">{{ __('After you submit, our team will check and confirm your payment. Your loan is disbursed only after the deposit is confirmed.') }}</x-alert>
            <button class="btn btn-primary btn-lg w-full"><x-icon name="send" />{{ __('Submit for Verification') }}</button>
        </form>
    @endif
</div>
@endsection
