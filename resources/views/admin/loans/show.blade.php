@extends('layouts.admin')
@section('title', $loan->loan_number)

@section('content')
<x-page-header :title="__('Loan').' '.$loan->loan_number" :subtitle="$loan->user->name.' · '.$loan->user->phone" :back="route('admin.loans.index')">
    <x-status-badge :status="$loan->status" size="lg" />
    <a href="{{ route('admin.applications.show', $loan->application) }}" class="btn btn-outline btn-sm">{{ __('Application') }}</a>
</x-page-header>

<div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
    <x-stat-card icon="banknote" :label="__('Principal')" :value="money_plain($loan->principal)" />
    <x-stat-card icon="send" :label="__('Disbursed')" :value="money_plain($loan->disbursed_amount)" color="violet" :hint="$loan->disbursed_at->format('d M Y').' · '.$loan->disbursement_method" />
    <x-stat-card icon="calculator" :label="__('Total Payable')" :value="money_plain((float) $loan->total_payable + (float) $loan->total_late_fee)" color="sky" :hint="__('Late fees').': '.money_plain($loan->total_late_fee)" />
    <x-stat-card icon="circle-check" :label="__('Total Paid')" :value="money_plain($loan->total_paid)" color="emerald" />
    <x-stat-card icon="wallet" :label="__('Outstanding')" :value="money_plain($loan->outstanding())" color="rose" />
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="card overflow-hidden">
            <h3 class="border-b border-slate-100 p-5 font-bold dark:border-slate-800">{{ __('Installment Schedule') }}</h3>
            <div class="overflow-x-auto"><table class="table">
                <thead><tr><th>#</th><th>{{ __('Due date') }}</th><th>{{ __('Principal') }}</th><th>{{ __('Interest') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Late fee') }}</th><th>{{ __('Paid') }}</th><th>{{ __('Status') }}</th></tr></thead>
                <tbody>
                @foreach($loan->installments as $i)
                    <tr>
                        <td>{{ $i->installment_number }}</td><td>{{ $i->due_date->format('d M Y') }}</td>
                        <td>{{ number_format($i->principal_component, 2) }}</td><td>{{ number_format($i->interest_component, 2) }}</td>
                        <td class="font-semibold">{{ number_format($i->amount, 2) }}</td><td>{{ number_format($i->late_fee, 2) }}</td>
                        <td>{{ number_format($i->paid_amount, 2) }}</td><td><x-status-badge :status="$i->status" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </div>

        <div class="card overflow-hidden">
            <h3 class="border-b border-slate-100 p-5 font-bold dark:border-slate-800">{{ __('Repayment History') }}</h3>
            <div class="overflow-x-auto"><table class="table">
                <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Method') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Recorded by') }}</th></tr></thead>
                <tbody>
                @forelse($loan->repayments as $r)
                    <tr><td>{{ $r->paid_at->format('d M Y, h:i A') }}</td><td class="font-semibold">{{ money_plain($r->amount) }}</td><td>{{ $r->method }}</td><td class="font-mono text-xs">{{ $r->reference }}</td><td>{{ $r->recorder?->name ?? __('Customer (online)') }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center text-slate-500">{{ __('No repayments yet') }}</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card card-pad text-sm">
            <h3 class="mb-3 font-bold">{{ __('Disbursement') }}</h3>
            <p>{{ __('Method') }}: <strong>{{ $loan->disbursement_method }}</strong></p>
            <p>{{ __('Account') }}: {{ $loan->disbursement_account ?? '—' }}</p>
            <p>{{ __('Reference') }}: <span class="font-mono">{{ $loan->disbursement_reference }}</span></p>
            <p>{{ __('Recorded by') }}: {{ $loan->disbursedBy?->name }}</p>
            @if($loan->note)<p class="mt-2 text-slate-500">{{ $loan->note }}</p>@endif
        </div>

        @if($loan->status === \App\Enums\LoanStatus::Active)
            @can('loans.repayments')
                <form method="POST" action="{{ route('admin.loans.repayments.store', $loan) }}" class="card card-pad space-y-4" data-confirm="{{ __('Record this repayment?') }}">
                    @csrf
                    <h3 class="font-bold">{{ __('Record Manual Repayment') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('For money received by cash, bank or MFS outside the online gateway.') }}</p>
                    <x-input name="amount" type="number" step="0.01" :label="__('Amount')" :value="$loan->nextInstallment()?->dueAmount()" :max="$loan->outstanding()" required />
                    <x-select name="method" :label="__('Method')" :options="['cash' => 'Cash', 'bank' => 'Bank', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'cheque' => 'Cheque']" required />
                    <x-input name="reference" :label="__('Receipt / TrxID')" required />
                    <x-textarea name="note" :label="__('Note')" rows="2" />
                    <button class="btn btn-success w-full"><x-icon name="hand-coins" />{{ __('Record Repayment') }}</button>
                </form>
            @endcan
        @endif
    </div>
</div>
@endsection
