@extends('layouts.user')
@section('title', __('Transactions'))

@section('content')
<x-page-header :title="__('Transaction History')" />

<div class="mb-5 flex gap-2 overflow-x-auto pb-1">
    @foreach(['all' => 'All', 'completed' => 'Successful', 'pending' => 'Pending', 'failed' => 'Failed'] as $key => $label)
        <a href="{{ route('user.transactions.index', ['status' => $key]) }}" class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition {{ $tab === $key ? 'bg-primary text-white shadow-lg shadow-primary/25' : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-900 dark:text-slate-300' }}">{{ __($label) }}</a>
    @endforeach
</div>

<div class="card overflow-hidden">
    @forelse($transactions as $tx)
        <a href="{{ route('user.transactions.show', $tx) }}" class="flex items-center gap-3 border-b border-slate-100 p-4 last:border-0 hover:bg-slate-50 sm:gap-4 sm:p-5 dark:border-slate-800 dark:hover:bg-slate-800/50">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-{{ $tx->status->color() }}-100 text-{{ $tx->status->color() }}-600 dark:bg-{{ $tx->status->color() }}-500/15"><x-icon :name="$tx->status->icon()" /></span>
            <div class="min-w-0 flex-1">
                <p class="truncate font-semibold">{{ $tx->purposeLabel() }}</p>
                <p class="truncate text-xs text-slate-500">{{ $tx->gateway_transaction_id ?? $tx->reference }} · {{ $tx->payment_method ? ucfirst($tx->payment_method).' · ' : '' }}{{ bn_digits($tx->created_at->format('d M Y, h:i A')) }}</p>
            </div>
            <div class="text-right">
                <p class="font-bold">{{ money($tx->amount) }}</p>
                <x-status-badge :status="$tx->status" class="mt-1" />
            </div>
        </a>
    @empty
        <x-empty icon="receipt-text" :title="__('No transactions found')" />
    @endforelse
</div>
<div class="mt-6">{{ $transactions->links() }}</div>
@endsection
