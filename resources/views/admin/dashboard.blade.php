@extends('layouts.admin')
@section('title', __('Admin Dashboard'))

@section('content')
<x-page-header :title="__('Dashboard')" :subtitle="__('Overview of your loan business (live data).')" />

<div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
    <x-stat-card icon="users" :label="__('Total Users')" :value="number_format($stats['users'])" color="indigo" :href="route('admin.users.index')" />
    <x-stat-card icon="file-text" :label="__('Total Applications')" :value="number_format($stats['applications'])" color="sky" :href="route('admin.applications.index')" />
    <x-stat-card icon="hourglass" :label="__('Pending Applications')" :value="number_format($stats['pending'])" color="amber" :href="route('admin.applications.index', ['status' => 'submitted'])" />
    <x-stat-card icon="badge-check" :label="__('Approved Loans')" :value="number_format($stats['approved'])" color="emerald" />
    <x-stat-card icon="circle-x" :label="__('Rejected Applications')" :value="number_format($stats['rejected'])" color="rose" :href="route('admin.applications.index', ['status' => 'rejected'])" />
    <x-stat-card icon="activity" :label="__('Active Loans')" :value="number_format($stats['active_loans'])" color="teal" :href="route('admin.loans.index', ['status' => 'active'])" />
    <x-stat-card icon="banknote" :label="__('Total Disbursed')" :value="money_plain($stats['disbursed'])" color="violet" />
    <x-stat-card icon="hand-coins" :label="__('Total Repayment')" :value="money_plain($stats['repaid'])" color="emerald" />
    <x-stat-card icon="clock" :label="__('Pending Payments')" :value="number_format($stats['pending_payments'])" color="orange" :href="route('admin.payments.index', ['status' => 'pending'])" />
    <x-stat-card icon="circle-check" :label="__('Successful Payments')" :value="number_format($stats['successful_payments'])" :hint="money_plain($stats['successful_amount'])" color="emerald" :href="route('admin.payments.index', ['status' => 'completed'])" />
</div>

@if($stats['pending_kyc'] || $stats['overdue'] || $awaitingDisbursement->isNotEmpty())
<div class="mt-6 grid gap-4 md:grid-cols-3">
    @if($stats['pending_kyc'])
        <a href="{{ route('admin.kyc.index') }}" class="card card-pad flex items-center gap-3 border-l-4 border-l-amber-500 hover:shadow-md"><x-icon name="id-card" class="text-amber-500" /><span><strong>{{ $stats['pending_kyc'] }}</strong> {{ __('NID documents waiting for review') }}</span></a>
    @endif
    @if($stats['overdue'])
        <a href="{{ route('admin.loans.index', ['overdue' => 1]) }}" class="card card-pad flex items-center gap-3 border-l-4 border-l-rose-500 hover:shadow-md"><x-icon name="alarm-clock" class="text-rose-500" /><span><strong>{{ $stats['overdue'] }}</strong> {{ __('overdue installments') }}</span></a>
    @endif
    @foreach($awaitingDisbursement as $a)
        <a href="{{ route('admin.applications.show', $a) }}" class="card card-pad flex items-center gap-3 border-l-4 border-l-violet-500 hover:shadow-md"><x-icon name="send" class="text-violet-500" /><span>{{ $a->reference }} {{ __('signed – ready to disburse') }}</span></a>
    @endforeach
</div>
@endif

<div class="mt-6 grid gap-6 xl:grid-cols-2">
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800"><h2 class="font-bold">{{ __('Recent Applications') }}</h2><a href="{{ route('admin.applications.index') }}" class="text-sm font-semibold text-primary">{{ __('View all') }}</a></div>
        <div class="overflow-x-auto"><table class="table">
            <tbody>
            @forelse($recentApplications as $a)
                <tr class="cursor-pointer" onclick="location.href='{{ route('admin.applications.show', $a) }}'">
                    <td><p class="font-semibold">{{ $a->applicant_name }}</p><p class="text-xs text-slate-500">{{ $a->reference }}</p></td>
                    <td>{{ money_plain($a->finalAmount()) }}</td>
                    <td><x-status-badge :status="$a->status" /></td>
                </tr>
            @empty
                <tr><td><x-empty icon="file-text" :title="__('No applications yet')" /></td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800"><h2 class="font-bold">{{ __('Recent Payments') }}</h2><a href="{{ route('admin.payments.index') }}" class="text-sm font-semibold text-primary">{{ __('View all') }}</a></div>
        <div class="overflow-x-auto"><table class="table">
            <tbody>
            @forelse($recentPayments as $p)
                <tr class="cursor-pointer" onclick="location.href='{{ route('admin.payments.show', $p) }}'">
                    <td><p class="font-semibold">{{ $p->user?->name }}</p><p class="text-xs text-slate-500">{{ $p->reference }}</p></td>
                    <td>{{ money_plain($p->amount) }}</td>
                    <td><x-status-badge :status="$p->status" /></td>
                </tr>
            @empty
                <tr><td><x-empty icon="credit-card" :title="__('No payments yet')" /></td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
</div>
@endsection
