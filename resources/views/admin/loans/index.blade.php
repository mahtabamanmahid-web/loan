@extends('layouts.admin')
@section('title', __('Loans'))

@section('content')
<x-page-header :title="__('Loans & Installments')">
    <a href="{{ route('admin.loans.index', ['overdue' => 1]) }}" class="btn btn-outline btn-sm text-rose-600"><x-icon name="alarm-clock" class="h-4 w-4" />{{ __('With overdue installments') }}</a>
</x-page-header>

@include('admin.partials.filters', ['selects' => ['status' => ['' => __('All'), 'active' => __('Active Loan'), 'completed' => __('Completed'), 'defaulted' => __('Defaulted')]]])

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Loan ID') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Principal') }}</th><th>{{ __('Paid') }}</th><th>{{ __('Outstanding') }}</th><th>{{ __('Progress') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($loans as $l)
            <tr>
                <td class="font-mono text-xs">{{ $l->loan_number }}</td>
                <td><p class="font-semibold">{{ $l->user->name }}</p><p class="text-xs text-slate-500">{{ $l->user->phone }}</p></td>
                <td>{{ money_plain($l->principal) }}</td>
                <td>{{ money_plain($l->total_paid) }}</td>
                <td class="font-semibold">{{ money_plain($l->outstanding()) }}</td>
                <td class="w-32"><div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-2 rounded-full bg-gradient-to-r from-primary to-secondary" style="width: {{ $l->progressPercent() }}%"></div></div></td>
                <td><x-status-badge :status="$l->status" /></td>
                <td class="text-right"><a href="{{ route('admin.loans.show', $l) }}" class="btn btn-primary btn-sm">{{ __('Open') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="8"><x-empty icon="wallet" :title="__('No loans found')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $loans->links() }}</div>
@endsection
