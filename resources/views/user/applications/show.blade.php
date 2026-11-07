{{-- Application status page: timeline, details, agreement, fee payment and loan link. --}}
@extends('layouts.user')
@section('title', $application->reference)

@php
    use App\Enums\ApplicationStatus as S;
    $status = $application->status;
    $order = array_map(fn ($s) => $s->value, S::timeline());
    $currentIndex = array_search(match ($status) {
        S::NidPending, S::DocumentsRequested => S::Submitted->value,
        default => $status->value,
    }, $order);
    $agreement = $application->agreement;
@endphp

@section('content')
<x-page-header :title="__('Application').' '.$application->reference" :subtitle="$application->product->displayName()" :back="route('user.applications.index')">
    <a href="{{ route('user.applications.pdf', $application) }}" class="btn btn-outline btn-sm" data-no-loading><x-icon name="download" class="h-4 w-4" />{{ __('PDF') }}</a>
</x-page-header>

{{-- Current status --}}
<div class="card card-pad mb-6 flex flex-col gap-4 sm:flex-row sm:items-center">
    <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-{{ $status->color() }}-100 text-{{ $status->color() }}-600 animate-pop dark:bg-{{ $status->color() }}-500/15"><x-icon :name="$status->icon()" class="h-8 w-8" /></span>
    <div class="flex-1">
        <p class="text-sm text-slate-500">{{ __('Current status') }}</p>
        <x-status-badge :status="$status" size="lg" class="mt-1" />
        @if($application->admin_note && in_array($status, [S::Rejected, S::DocumentsRequested, S::Approved], true))
            <p class="mt-3 rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-800"><strong>{{ __('Message from us') }}:</strong> {{ $application->admin_note }}</p>
        @endif
    </div>
    @if(in_array($status, [S::Submitted, S::NidPending, S::DocumentsRequested], true))
        <form method="POST" action="{{ route('user.applications.cancel', $application) }}" data-confirm="{{ __('Cancel this application?') }}">
            @csrf<button class="btn btn-ghost btn-sm text-rose-600"><x-icon name="x" class="h-4 w-4" />{{ __('Cancel application') }}</button>
        </form>
    @endif
</div>

{{-- Next action for the customer --}}
@if($status === S::NidPending)
    <x-alert type="warning" class="mb-6" :title="__('NID verification pending')">{{ __('Your application will be reviewed after your NID is verified.') }} <a class="font-bold underline" href="{{ route('user.kyc.show') }}">{{ __('Check NID status') }}</a></x-alert>
@elseif($status === S::DocumentsRequested)
    <x-alert type="warning" class="mb-6" :title="__('Action needed')">{{ __('Please send the requested information through a support ticket or upload your NID again.') }} <a class="font-bold underline" href="{{ route('user.tickets.create') }}">{{ __('Contact Support') }}</a></x-alert>
@elseif($status === S::AwaitingAgreement && $agreement)
    <div class="card card-pad mb-6 border-2 border-violet-300 dark:border-violet-500/40">
        <h2 class="flex items-center gap-2 text-lg font-bold"><x-icon name="file-signature" class="text-violet-600" />{{ __('Your loan agreement') }}</h2>
        @if(! $agreement->isAccepted())
            <p class="mt-1 text-sm text-slate-500">{{ __('Read all terms carefully. Accept only if you agree.') }}</p>
            <a href="{{ route('user.applications.agreement', $application) }}" class="btn btn-primary mt-4"><x-icon name="book-open-check" />{{ __('Read & Accept Agreement') }}</a>
        @else
            <p class="mt-1 flex items-center gap-1.5 text-sm text-emerald-600"><x-icon name="circle-check" class="h-4 w-4" />{{ __('Accepted on :date', ['date' => bn_digits($agreement->accepted_at->format('d M Y, h:i A'))]) }}</p>
            @if($application->requiresUpfrontFee())
                <div class="mt-4 rounded-2xl bg-amber-50 p-4 dark:bg-amber-500/10">
                    <p class="font-bold">{{ __('Processing fee due') }}: {{ money($agreement->processing_fee) }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('This fee is disclosed in your agreement. Paying it does not guarantee disbursement; it is refundable according to the stated policy.') }}</p>
                    <form method="POST" action="{{ route('user.applications.pay-fee', $application) }}" class="mt-3">@csrf
                        <button class="btn btn-primary"><x-icon name="credit-card" />{{ __('Pay :amount securely', ['amount' => money($agreement->processing_fee)]) }}</button>
                    </form>
                </div>
            @else
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ __('We are preparing your disbursement. You will be notified when the money is sent.') }}</p>
            @endif
            <a href="{{ route('user.applications.agreement', $application) }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-primary">{{ __('View agreement') }} →</a>
        @endif
    </div>
@elseif($application->loan)
    <a href="{{ route('user.loans.show', $application->loan) }}" class="card card-pad mb-6 flex items-center justify-between gap-4 border-2 border-emerald-300 transition hover:shadow-lg dark:border-emerald-500/40">
        <div class="flex items-center gap-3"><x-icon name="wallet" class="h-8 w-8 text-emerald-600" /><div><p class="font-bold">{{ __('Loan') }} {{ $application->loan->loan_number }}</p><p class="text-sm text-slate-500">{{ __('View installments and pay') }}</p></div></div>
        <x-icon name="chevron-right" />
    </a>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    {{-- Timeline --}}
    <div class="card card-pad">
        <h2 class="mb-5 font-bold">{{ __('Progress') }}</h2>
        @if(in_array($status, [S::Rejected, S::Cancelled], true))
            <x-alert type="danger">{{ $status->label() }}</x-alert>
        @else
            <ol class="relative space-y-5 border-l-2 border-slate-200 pl-6 dark:border-slate-700">
                @foreach(S::timeline() as $i => $step)
                    @php($done = $currentIndex !== false && $i <= $currentIndex)
                    <li class="relative">
                        <span class="absolute -left-[33px] grid h-6 w-6 place-items-center rounded-full ring-4 ring-white dark:ring-slate-900 {{ $done ? 'bg-gradient-to-br from-primary to-secondary text-white' : 'bg-slate-200 text-slate-400 dark:bg-slate-700' }}">
                            <x-icon :name="$done ? 'check' : $step->icon()" class="h-3.5 w-3.5" />
                        </span>
                        <p class="text-sm font-semibold {{ $done ? 'text-slate-900 dark:text-white' : 'text-slate-400' }}">{{ $step->label() }}</p>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

    {{-- Details --}}
    <div class="card card-pad lg:col-span-2">
        <h2 class="mb-4 font-bold">{{ __('Loan details') }}</h2>
        <dl class="grid gap-3 sm:grid-cols-2">
            @foreach([
                [__('Requested amount'), money($application->requested_amount)],
                [__('Approved amount'), $application->approved_amount ? money($application->approved_amount) : '—'],
                [__('Duration'), bn_digits($application->finalMonths()).' '.__('months')],
                [__('Interest Rate (flat, yearly)'), bn_digits((float) $application->finalRate()).'%'],
                [__('Monthly installment'), money($agreement?->installment_amount ?? $application->installment_amount)],
                [__('Total Repayment'), money($agreement?->total_payable ?? $application->total_payable)],
                [__('Processing Fee'), money($agreement?->processing_fee ?? $application->processing_fee)],
                [__('Submitted on'), bn_digits($application->created_at->format('d M Y, h:i A'))],
            ] as [$k, $v])
                <div class="flex justify-between gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-sm text-slate-500">{{ $k }}</dt><dd class="text-right text-sm font-bold">{{ $v }}</dd></div>
            @endforeach
        </dl>

        @if($application->payments->isNotEmpty())
            <h3 class="mb-3 mt-6 font-bold">{{ __('Payments') }}</h3>
            @foreach($application->payments as $tx)
                <a href="{{ route('user.transactions.show', $tx) }}" class="flex items-center justify-between rounded-xl border border-slate-100 p-3 text-sm hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
                    <span>{{ $tx->purposeLabel() }} · {{ money($tx->amount) }}</span><x-status-badge :status="$tx->status" />
                </a>
            @endforeach
        @endif
    </div>
</div>
@endsection
