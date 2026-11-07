@extends('layouts.admin')
@section('title', __('Payments'))

@section('content')
<x-page-header :title="__('Payments')" :subtitle="__(':count total', ['count' => $payments->total()])">
    @can('payments.export')
        <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn btn-outline btn-sm" data-no-loading><x-icon name="file-spreadsheet" class="h-4 w-4" />{{ __('Export CSV / Excel') }}</a>
    @endcan
    <a href="{{ route('admin.payments.reports') }}" class="btn btn-outline btn-sm"><x-icon name="chart-column" class="h-4 w-4" />{{ __('Reports') }}</a>
</x-page-header>

@include('admin.partials.filters', [
    'placeholder' => __('Reference, TrxID, customer...'),
    'selects' => [
        'status' => ['' => __('All')] + collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all(),
        'purpose' => ['' => __('All'), 'processing_fee' => __('Processing Fee'), 'installment' => __('Loan Installment')],
    ],
    'dates' => true,
])

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Purpose') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Method') }}</th><th>{{ __('Transaction ID') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($payments as $p)
            <tr>
                <td class="font-mono text-xs">{{ $p->reference }}</td>
                <td><p class="font-semibold">{{ $p->user?->name }}</p><p class="text-xs text-slate-500">{{ $p->user?->phone }}</p></td>
                <td>{{ $p->purposeLabel() }}</td>
                <td class="font-semibold">{{ money_plain($p->amount) }}</td>
                <td>{{ $p->payment_method ?? '—' }}</td>
                <td class="font-mono text-xs">{{ $p->gateway_transaction_id ?? '—' }}</td>
                <td><x-status-badge :status="$p->status" /></td>
                <td>{{ $p->created_at->format('d M Y, h:i A') }}</td>
                <td class="text-right"><a href="{{ route('admin.payments.show', $p) }}" class="btn btn-outline btn-sm">{{ __('Details') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="9"><x-empty icon="credit-card" :title="__('No payments found')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $payments->links() }}</div>
@endsection
