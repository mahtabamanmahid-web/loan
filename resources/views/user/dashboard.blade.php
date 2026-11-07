@extends('layouts.user')
@section('title', __('Dashboard'))

@section('content')
{{-- Greeting --}}
<div class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-secondary p-6 text-white shadow-xl shadow-primary/20 sm:p-8">
    <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/10"></div>
    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-white/80">{{ __('Welcome') }},</p>
            <h1 class="text-2xl font-extrabold sm:text-3xl">{{ $user->name }}</h1>
            <p class="mt-1 text-sm text-white/80">{{ __('Current loan balance') }}: <strong class="text-white">{{ money($stats['current_balance']) }}</strong></p>
        </div>
        <a href="{{ route('user.applications.create') }}" class="btn btn-lg bg-white text-primary shadow-xl hover:bg-slate-50"><x-icon name="circle-plus" />{{ __('Apply for a Loan') }}</a>
    </div>
</div>

{{-- KYC prompt --}}
@php($doc = $user->latestDocument)
@if(! $doc || in_array($doc->status->value, ['rejected', 'resubmit']))
    <x-alert type="warning" :title="__('Verify your NID')" class="mb-6">
        {{ __('You need a verified NID to get a loan.') }}
        <a href="{{ route('user.kyc.show') }}" class="font-bold underline">{{ __('Upload NID now') }} →</a>
    </x-alert>
@elseif($doc->status->value === 'pending')
    <x-alert type="info" :title="__('NID under review')" class="mb-6">{{ __('We are checking your NID. You will be notified soon.') }}</x-alert>
@endif

{{-- Stats --}}
<div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    <x-stat-card icon="badge-check" :label="__('Total Approved Loan')" :value="money($stats['total_approved'])" color="indigo" />
    <x-stat-card icon="wallet" :label="__('Current Balance')" :value="money($stats['current_balance'])" color="rose" />
    <x-stat-card icon="circle-check" :label="__('Total Paid')" :value="money($stats['total_paid'])" color="emerald" />
    <x-stat-card icon="list-ordered" :label="__('Remaining Installments')" :value="bn_digits($stats['remaining_installments'])" color="amber" />
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        {{-- Next installment --}}
        @if($nextInstallment)
            <div class="card card-pad flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between {{ $nextInstallment->status->value === 'overdue' ? 'ring-2 ring-rose-400' : '' }}">
                <div class="flex items-center gap-4">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl {{ $nextInstallment->status->value === 'overdue' ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600' }}"><x-icon name="calendar-clock" class="h-7 w-7" /></span>
                    <div>
                        <p class="text-sm text-slate-500">{{ __('Next Installment') }} · {{ $nextInstallment->loan->loan_number }}</p>
                        <p class="text-2xl font-extrabold">{{ money($nextInstallment->dueAmount()) }}</p>
                        <p class="text-sm">{{ __('Due') }}: <strong>{{ bn_digits($nextInstallment->due_date->format('d M Y')) }}</strong>
                            <x-status-badge :status="$nextInstallment->status" class="ml-1" /></p>
                    </div>
                </div>
                <form method="POST" action="{{ route('user.installments.pay', $nextInstallment) }}">
                    @csrf
                    <button class="btn btn-primary w-full sm:w-auto"><x-icon name="credit-card" />{{ __('Pay Now') }}</button>
                </form>
            </div>
        @endif

        {{-- Active loans with progress --}}
        @foreach($activeLoans as $loan)
            <a href="{{ route('user.loans.show', $loan) }}" class="card card-pad block transition hover:shadow-lg">
                <div class="flex items-center justify-between gap-3">
                    <div><p class="text-sm text-slate-500">{{ __('Loan') }} {{ $loan->loan_number }}</p><p class="text-lg font-bold">{{ money($loan->principal) }}</p></div>
                    <x-status-badge :status="$loan->status" />
                </div>
                <div class="mt-4">
                    <div class="mb-1.5 flex justify-between text-sm"><span class="text-slate-500">{{ __('Repayment Progress') }}</span><span class="font-bold">{{ bn_digits($loan->progressPercent()) }}%</span></div>
                    <div class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-gradient-to-r from-primary to-secondary transition-all duration-700" style="width: {{ $loan->progressPercent() }}%"></div></div>
                    <div class="mt-2 flex justify-between text-xs text-slate-500"><span>{{ __('Paid') }}: {{ money($loan->total_paid) }}</span><span>{{ __('Remaining') }}: {{ money($loan->outstanding()) }}</span></div>
                </div>
            </a>
        @endforeach

        {{-- Applications in progress --}}
        <div class="card">
            <div class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800">
                <h2 class="font-bold">{{ __('Active Applications') }}</h2>
                <a href="{{ route('user.applications.index') }}" class="text-sm font-semibold text-primary">{{ __('View all') }}</a>
            </div>
            @forelse($openApplications as $app)
                <a href="{{ route('user.applications.show', $app) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 p-5 last:border-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50">
                    <div class="min-w-0">
                        <p class="font-semibold">{{ $app->product->displayName() }} · {{ money($app->finalAmount()) }}</p>
                        <p class="text-xs text-slate-500">{{ $app->reference }} · {{ bn_digits($app->created_at->format('d M Y')) }}</p>
                    </div>
                    <x-status-badge :status="$app->status" />
                </a>
            @empty
                <x-empty icon="file-text" :title="__('No active applications')" :text="__('Start a new application in 3 minutes.')">
                    <a href="{{ route('user.applications.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" />{{ __('Apply Now') }}</a>
                </x-empty>
            @endforelse
        </div>
    </div>

    <div class="space-y-6">
        {{-- Recent transactions --}}
        <div class="card">
            <div class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800">
                <h2 class="font-bold">{{ __('Recent Transactions') }}</h2>
                <a href="{{ route('user.transactions.index') }}" class="text-sm font-semibold text-primary">{{ __('View all') }}</a>
            </div>
            @forelse($recentTransactions as $tx)
                <a href="{{ route('user.transactions.show', $tx) }}" class="flex items-center gap-3 border-b border-slate-100 p-4 last:border-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-{{ $tx->status->color() }}-100 text-{{ $tx->status->color() }}-600 dark:bg-{{ $tx->status->color() }}-500/15"><x-icon :name="$tx->status->icon()" class="h-5 w-5" /></span>
                    <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $tx->purposeLabel() }}</p><p class="text-xs text-slate-500">{{ bn_digits($tx->created_at->format('d M, h:i A')) }}</p></div>
                    <p class="font-bold">{{ money($tx->amount) }}</p>
                </a>
            @empty
                <x-empty icon="receipt-text" :title="__('No transactions yet')" />
            @endforelse
        </div>

        {{-- Review (real customers only) --}}
        @if($canReview)
            <div class="card card-pad" x-data="{ rating: 5 }">
                <h2 class="font-bold">{{ __('Share your experience') }}</h2>
                <p class="mb-4 text-sm text-slate-500">{{ __('Your review will be shown after verification.') }}</p>
                <form method="POST" action="{{ route('user.reviews.store') }}" class="space-y-3">
                    @csrf
                    <div class="flex gap-1" role="radiogroup" aria-label="{{ __('Rating') }}">
                        @for($s = 1; $s <= 5; $s++)
                            <button type="button" @click="rating = {{ $s }}" :class="rating >= {{ $s }} ? 'text-amber-400' : 'text-slate-300'" aria-label="{{ $s }}"><x-icon name="star" class="h-7 w-7 fill-current" /></button>
                        @endfor
                        <input type="hidden" name="rating" :value="rating">
                    </div>
                    <x-textarea name="comment" rows="3" :placeholder="__('Write a few words...')" required />
                    <x-input name="location" :placeholder="__('Your district (optional)')" />
                    <button class="btn btn-primary btn-sm w-full">{{ __('Submit Review') }}</button>
                </form>
            </div>
        @endif

        <div class="card card-pad">
            <h2 class="mb-3 font-bold">{{ __('Need help?') }}</h2>
            <a href="{{ route('user.tickets.create') }}" class="btn btn-outline w-full"><x-icon name="life-buoy" />{{ __('Contact Support') }}</a>
        </div>
    </div>
</div>
@endsection
