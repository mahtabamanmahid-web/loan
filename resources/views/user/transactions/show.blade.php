@extends('layouts.user')
@section('title', __('Transaction'))

@section('content')
<x-page-header :title="__('Transaction Details')" :back="route('user.transactions.index')">
    @if(in_array($tx->status->value, ['completed', 'refunded']))
        <a href="{{ route('user.transactions.receipt', $tx) }}" class="btn btn-primary btn-sm" data-no-loading><x-icon name="download" class="h-4 w-4" />{{ __('Download Receipt PDF') }}</a>
    @endif
</x-page-header>

<div class="card mx-auto max-w-2xl overflow-hidden">
    <div class="bg-{{ $tx->status->color() }}-500 p-8 text-center text-white">
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-white/20 animate-pop"><x-icon :name="$tx->status->icon()" class="h-8 w-8" /></span>
        <p class="mt-4 text-4xl font-extrabold">{{ money($tx->amount) }}</p>
        <p class="mt-1 font-semibold">{{ $tx->status->label() }}</p>
    </div>
    <dl class="divide-y divide-slate-100 dark:divide-slate-800">
        @foreach(array_filter([
            __('Payment Purpose') => $tx->purposeLabel(),
            __('Description') => $tx->description,
            __('Payment Reference') => $tx->reference,
            __('Transaction ID') => $tx->gateway_transaction_id,
            __('Invoice ID') => $tx->gateway_invoice_id,
            __('Payment Method') => $tx->payment_method ? ucfirst($tx->payment_method) : null,
            __('Sender Number') => $tx->sender_number,
            __('Date & Time') => bn_digits($tx->created_at->format('d M Y, h:i A')),
            __('Paid at') => $tx->paid_at ? bn_digits($tx->paid_at->format('d M Y, h:i A')) : null,
            __('Failure reason') => $tx->status->value === 'failed' ? $tx->failure_reason : null,
            __('Refunded') => (float) $tx->refunded_amount > 0 ? money($tx->refunded_amount).' · '.$tx->refund_reason : null,
        ]) as $k => $v)
            <div class="flex justify-between gap-4 px-6 py-3.5 text-sm"><dt class="text-slate-500">{{ $k }}</dt><dd class="break-all text-right font-semibold">{{ $v }}</dd></div>
        @endforeach
    </dl>
</div>
@endsection
