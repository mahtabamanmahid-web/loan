@extends('layouts.admin')
@section('title', $tx->reference)

@section('content')
<x-page-header :title="__('Payment').' '.$tx->reference" :subtitle="$tx->user?->name.' · '.$tx->user?->phone" :back="route('admin.payments.index')">
    <x-status-badge :status="$tx->status" size="lg" />
    @can('payments.verify')
        @if($tx->gateway === 'manual' && $tx->status->value === 'pending' && ! $tx->processed_at)
            <form method="POST" action="{{ route('admin.payments.approve-manual', $tx) }}" data-confirm="{{ __('Confirm you received this payment?') }}">@csrf<button class="btn btn-success btn-sm"><x-icon name="check" class="h-4 w-4" />{{ __('Approve payment') }}</button></form>
            <button type="button" @click="$dispatch('open-modal', 'reject-manual')" class="btn btn-outline btn-sm text-rose-600"><x-icon name="x" class="h-4 w-4" />{{ __('Reject') }}</button>
        @elseif($tx->gateway_invoice_id && ! $tx->processed_at)
            <form method="POST" action="{{ route('admin.payments.verify', $tx) }}">@csrf<button class="btn btn-primary btn-sm"><x-icon name="refresh-cw" class="h-4 w-4" />{{ __('Verify with gateway') }}</button></form>
        @endif
    @endcan
    @can('payments.refund')
        @if($tx->status->value === 'completed' && $tx->purpose === 'processing_fee')
            <button type="button" @click="$dispatch('open-modal', 'refund')" class="btn btn-outline btn-sm text-violet-600"><x-icon name="undo-2" class="h-4 w-4" />{{ __('Refund') }}</button>
        @endif
    @endcan
</x-page-header>

<div class="grid gap-6 xl:grid-cols-3">
    <div class="card card-pad">
        <h3 class="mb-4 font-bold">{{ __('Payment Details') }}</h3>
        <dl class="space-y-2.5 text-sm">
            @foreach([
                __('Amount') => money_plain($tx->amount),
                __('Purpose') => $tx->purposeLabel(),
                __('Description') => $tx->description,
                __('Gateway') => $tx->gateway,
                __('Invoice ID') => $tx->gateway_invoice_id,
                __('Transaction ID') => $tx->gateway_transaction_id,
                __('Method') => $tx->payment_method,
                __('Sender') => $tx->sender_number,
                __('Gateway fee') => $tx->gateway_fee !== null ? money_plain($tx->gateway_fee) : null,
                __('Charged amount') => $tx->charged_amount !== null ? money_plain($tx->charged_amount) : null,
                __('Created') => $tx->created_at->format('d M Y, h:i:s A'),
                __('Paid at') => $tx->paid_at?->format('d M Y, h:i:s A'),
                __('Verified at') => $tx->verified_at?->format('d M Y, h:i:s A'),
                __('Processed at') => $tx->processed_at?->format('d M Y, h:i:s A'),
                __('Failure reason') => $tx->failure_reason,
                __('Refunded') => (float) $tx->refunded_amount > 0 ? money_plain($tx->refunded_amount).' – '.$tx->refund_reason : null,
            ] as $k => $v)
                @if($v)<div class="flex justify-between gap-3"><dt class="text-slate-500">{{ $k }}</dt><dd class="break-all text-right font-medium">{{ $v }}</dd></div>@endif
            @endforeach
        </dl>
        <div class="mt-4 space-y-1 border-t border-slate-100 pt-4 text-sm dark:border-slate-800">
            @if($tx->application)<a href="{{ route('admin.applications.show', $tx->application) }}" class="block font-semibold text-primary">{{ __('Application') }} {{ $tx->application->reference }}</a>@endif
            @if($tx->loan)<a href="{{ route('admin.loans.show', $tx->loan) }}" class="block font-semibold text-primary">{{ __('Loan') }} {{ $tx->loan->loan_number }}</a>@endif
        </div>
    </div>

    <div class="card overflow-hidden xl:col-span-2">
        <h3 class="border-b border-slate-100 p-5 font-bold dark:border-slate-800">{{ __('Audit Log') }}</h3>
        <ol class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($tx->logs as $log)
                <li class="p-4 text-sm" x-data="{ open: false }">
                    <div class="flex items-center justify-between gap-3">
                        <span class="flex items-center gap-2">
                            <x-badge :color="['error' => 'rose', 'warning' => 'amber'][$log->level] ?? 'sky'">{{ $log->event }}</x-badge>
                            <span>{{ $log->message }}</span>
                        </span>
                        <span class="whitespace-nowrap text-xs text-slate-500">{{ $log->created_at->format('d M, h:i:s A') }} · {{ $log->ip_address }}</span>
                    </div>
                    @if($log->payload)
                        <button type="button" @click="open = !open" class="mt-1 text-xs font-semibold text-primary">{{ __('Payload') }}</button>
                        <pre x-show="open" x-cloak class="mt-2 max-h-64 overflow-auto rounded-lg bg-slate-900 p-3 text-xs text-slate-100">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    @endif
                </li>
            @empty
                <li class="p-5 text-sm text-slate-500">{{ __('No log entries') }}</li>
            @endforelse
        </ol>
    </div>
</div>

<x-modal name="reject-manual" :title="__('Reject payment')">
    <form method="POST" action="{{ route('admin.payments.reject-manual', $tx) }}" class="space-y-4">
        @csrf
        <x-textarea name="reason" :label="__('Reason (why no money was received)')" rows="2" required />
        <button class="btn btn-danger w-full">{{ __('Reject') }}</button>
    </form>
</x-modal>

<x-modal name="refund" :title="__('Refund payment')">
    <form method="POST" action="{{ route('admin.payments.refund', $tx) }}" class="space-y-4" data-confirm="{{ __('Send this refund through UddoktaPay?') }}">
        @csrf
        <x-input name="amount" type="number" step="0.01" :label="__('Amount')" :value="(float) $tx->amount" :max="(float) $tx->amount" required />
        <x-textarea name="reason" :label="__('Reason')" rows="2" required />
        <button class="btn btn-primary w-full">{{ __('Refund') }}</button>
    </form>
</x-modal>
@endsection
