@extends('layouts.user')
@section('title', __('My Loans'))

@section('content')
<x-page-header :title="__('My Loans')" />

<div class="grid gap-4 md:grid-cols-2">
    @forelse($loans as $loan)
        <a href="{{ route('user.loans.show', $loan) }}" class="card card-pad block transition hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm text-slate-500">{{ $loan->loan_number }} · {{ $loan->application->product->displayName() }}</p>
                    <p class="mt-1 text-2xl font-extrabold">{{ money($loan->principal) }}</p>
                </div>
                <x-status-badge :status="$loan->status" />
            </div>
            <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-gradient-to-r from-primary to-secondary" style="width: {{ $loan->progressPercent() }}%"></div></div>
            <div class="mt-2 flex justify-between text-xs text-slate-500">
                <span>{{ __('Paid') }} {{ money($loan->total_paid) }}</span><span>{{ bn_digits($loan->progressPercent()) }}%</span><span>{{ __('Remaining') }} {{ money($loan->outstanding()) }}</span>
            </div>
        </a>
    @empty
        <div class="card md:col-span-2"><x-empty icon="wallet" :title="__('No loans yet')" :text="__('Loans appear here after disbursement.')" /></div>
    @endforelse
</div>
@endsection
