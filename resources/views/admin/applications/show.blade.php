{{-- Application review screen: details on the left, the next allowed action(s) on the right. --}}
@extends('layouts.admin')
@section('title', $application->reference)

@php
    use App\Enums\ApplicationStatus as S;
    $st = $application->status;
    $doc = $application->user->latestDocument;
    $agreement = $application->agreement;
@endphp

@section('content')
<x-page-header :title="$application->reference" :subtitle="$application->applicant_name.' · '.$application->product->name" :back="route('admin.applications.index')">
    <x-status-badge :status="$st" size="lg" />
    <a href="{{ route('admin.applications.pdf', $application) }}" class="btn btn-outline btn-sm" data-no-loading><x-icon name="download" class="h-4 w-4" />PDF</a>
</x-page-header>

<div class="grid gap-6 xl:grid-cols-3">
    {{-- ================= Details ================= --}}
    <div class="space-y-6 xl:col-span-2">
        <div class="grid gap-4 sm:grid-cols-3">
            <x-stat-card icon="banknote" :label="__('Requested')" :value="money_plain($application->requested_amount)" :hint="$application->requested_months.' '.__('months').' · '.(float) $application->interest_rate.'%'" />
            <x-stat-card icon="calendar-days" :label="__('Installment')" :value="money_plain($application->installment_amount)" color="violet" :hint="__('Total').' '.money_plain($application->total_payable)" />
            <x-stat-card icon="gauge" :label="__('Eligibility estimate')" :value="money_plain($eligibility['max_amount'])" :color="$eligibility['eligible'] ? 'emerald' : 'amber'" :hint="$eligibility['eligible'] ? __('Within limit') : __('Above estimate')" />
        </div>

        <div class="card card-pad">
            <h3 class="mb-4 font-bold">{{ __('Applicant Information') }}</h3>
            <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                @foreach([
                    __('Applicant Name') => $application->applicant_name,
                    __('Mobile Number') => $application->phone,
                    __('Email') => $application->email,
                    __('Occupation') => $application->occupation,
                    __('Monthly Income') => money_plain($application->monthly_income),
                    __('Loan Purpose') => $application->purpose,
                    __('Present Address') => $application->present_address,
                    __('Permanent Address') => $application->permanent_address,
                    __('Emergency Contact') => trim($application->emergency_contact_name.' '.$application->emergency_contact_phone) ?: '—',
                    __('Terms accepted') => $application->terms_accepted_at?->format('d M Y, h:i A').' · '.$application->terms_accepted_ip,
                ] as $k => $v)
                    <div><dt class="text-slate-500">{{ $k }}</dt><dd class="font-medium">{{ $v }}</dd></div>
                @endforeach
            </dl>
            <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                <a href="{{ route('admin.users.show', $application->user) }}" class="btn btn-outline btn-sm"><x-icon name="user" class="h-4 w-4" />{{ __('Customer profile') }}</a>
                @if($doc)<a href="{{ route('admin.kyc.show', $doc) }}" class="btn btn-outline btn-sm"><x-icon name="id-card" class="h-4 w-4" />{{ __('NID') }}: {{ $doc->status->label() }}</a>@endif
            </div>
        </div>

        @if($application->approved_amount)
            <div class="card card-pad">
                <h3 class="mb-3 font-bold">{{ __('Approved terms') }}</h3>
                <p class="text-sm">{{ money_plain($application->approved_amount) }} · {{ $application->approved_months }} {{ __('months') }} · {{ (float) $application->approved_interest_rate }}% · {{ __('by') }} {{ $application->reviewer?->name }} {{ $application->approved_at?->format('d M Y') }}</p>
                @if($application->admin_note)<p class="mt-2 text-sm text-slate-500">{{ __('Note') }}: {{ $application->admin_note }}</p>@endif
            </div>
        @endif

        @if($agreement)
            <div class="card card-pad">
                <h3 class="mb-2 font-bold">{{ __('Agreement') }} {{ $agreement->agreement_number }}</h3>
                <p class="text-sm">{{ __('Fee') }}: {{ money_plain($agreement->processing_fee) }} ({{ $agreement->fee_collection === 'deduct' ? __('deducted at disbursement') : __('paid upfront') }})</p>
                <p class="mt-1 text-sm">
                    @if($agreement->isAccepted())
                        <x-badge color="emerald" icon="check">{{ __('Accepted') }} {{ $agreement->accepted_at->format('d M Y, h:i A') }} · IP {{ $agreement->accepted_ip }}</x-badge>
                    @else
                        <x-badge color="amber" icon="clock">{{ __('Waiting for customer to accept') }}</x-badge>
                    @endif
                    @if($agreement->fee_collection === 'upfront' && (float) $agreement->processing_fee > 0)
                        @if($application->isFeePaid())<x-badge color="emerald" icon="circle-check">{{ __('Fee paid') }}</x-badge>@else<x-badge color="amber">{{ __('Fee not paid') }}</x-badge>@endif
                    @endif
                </p>
                <details class="mt-3"><summary class="cursor-pointer text-sm font-semibold text-primary">{{ __('Show agreement text') }}</summary><div class="prose-content mt-3 text-sm">{!! $agreement->content !!}</div></details>
            </div>
        @endif

        @if($application->payments->isNotEmpty())
            <div class="card overflow-hidden">
                <h3 class="border-b border-slate-100 p-5 font-bold dark:border-slate-800">{{ __('Payments') }}</h3>
                @foreach($application->payments as $tx)
                    <a href="{{ route('admin.payments.show', $tx) }}" class="flex justify-between border-b border-slate-100 p-4 text-sm last:border-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"><span>{{ $tx->reference }} · {{ money_plain($tx->amount) }}</span><x-status-badge :status="$tx->status" /></a>
                @endforeach
            </div>
        @endif

        @if($previousApplications->isNotEmpty())
            <div class="card card-pad">
                <h3 class="mb-3 font-bold">{{ __('Previous applications') }}</h3>
                @foreach($previousApplications as $p)
                    <a href="{{ route('admin.applications.show', $p) }}" class="flex justify-between py-1.5 text-sm"><span>{{ $p->reference }} · {{ money_plain($p->finalAmount()) }}</span><x-status-badge :status="$p->status" /></a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ================= Actions ================= --}}
    <div class="space-y-4">
        @if($application->loan)
            <a href="{{ route('admin.loans.show', $application->loan) }}" class="card card-pad flex items-center justify-between border-2 border-emerald-300 hover:shadow-md"><span class="font-bold">{{ __('Loan') }} {{ $application->loan->loan_number }}</span><x-icon name="chevron-right" /></a>
        @endif

        {{-- Start review / request documents --}}
        @if(in_array($st, [S::Submitted, S::NidPending, S::DocumentsRequested], true))
            @can('loans.review')
                <form method="POST" action="{{ route('admin.applications.review', $application) }}" class="card card-pad">@csrf
                    <button class="btn btn-secondary w-full"><x-icon name="search" />{{ __('Start Review') }}</button>
                </form>
            @endcan
        @endif

        @if(in_array($st, [S::Submitted, S::UnderReview, S::DocumentsRequested], true))
            @can('loans.approve')
                <form method="POST" action="{{ route('admin.applications.approve', $application) }}" class="card card-pad space-y-4"
                      data-confirm="{{ __('Approve this application with these terms?') }}">
                    @csrf
                    <h3 class="flex items-center gap-2 font-bold"><x-icon name="badge-check" class="text-emerald-600" />{{ __('Approve') }}</h3>
                    @unless($application->user->isKycApproved())
                        <x-alert type="warning">{{ __('NID is not verified yet. Approval is blocked until NID is approved.') }}</x-alert>
                    @endunless
                    <x-input name="approved_amount" type="number" step="0.01" :label="__('Set Approved Amount')" :value="(float) $application->requested_amount" required />
                    <x-select name="approved_months" :label="__('Set Loan Duration')" :options="$application->product->terms->mapWithKeys(fn ($t) => [$t->months => $t->months.' '.__('months').' ('.(float) $t->interest_rate.'%)'])->all()" :selected="$application->requested_months" required />
                    <x-input name="approved_interest_rate" type="number" step="0.01" :label="__('Set Interest Rate (%/year)')" :value="(float) $application->interest_rate" required />
                    <x-textarea name="note" :label="__('Note to customer (optional)')" rows="2" />
                    <button class="btn btn-success w-full" @disabled(! $application->user->isKycApproved())><x-icon name="check" />{{ __('Approve Application') }}</button>
                </form>
            @endcan
        @endif

        @if(in_array($st, [S::Submitted, S::UnderReview, S::NidPending], true))
            @can('loans.review')
                <button type="button" @click="$dispatch('open-modal', 'docs')" class="btn btn-warning w-full"><x-icon name="file-warning" />{{ __('Request Additional Documents') }}</button>
            @endcan
        @endif

        @if($st === S::Approved)
            @can('loans.agreement')
                <form method="POST" action="{{ route('admin.applications.agreement', $application) }}" class="card card-pad space-y-3">@csrf
                    <h3 class="flex items-center gap-2 font-bold"><x-icon name="file-signature" class="text-violet-600" />{{ __('Create Loan Agreement') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('Generates the agreement with all fees and terms and sends it to the customer for acceptance.') }}</p>
                    <button class="btn btn-primary w-full"><x-icon name="file-plus" />{{ __('Create Agreement') }}</button>
                </form>
            @endcan
        @endif

        {{-- Disbursement – only after customer accepted (and paid upfront fee, if any) --}}
        @if($st === S::AwaitingAgreement)
            @can('loans.disburse')
                @if(! $agreement?->isAccepted())
                    <x-alert type="info">{{ __('Waiting for the customer to accept the agreement.') }}</x-alert>
                @elseif($application->requiresUpfrontFee())
                    <x-alert type="info">{{ __('Waiting for the processing fee payment.') }}</x-alert>
                @else
                    <form method="POST" action="{{ route('admin.applications.disburse', $application) }}" class="card card-pad space-y-4 border-2 border-emerald-300 dark:border-emerald-500/40"
                          data-confirm="{{ __('Record this disbursement? Only do this AFTER the money has actually been sent.') }}">
                        @csrf
                        <h3 class="flex items-center gap-2 font-bold"><x-icon name="banknote" class="text-emerald-600" />{{ __('Record Loan Disbursement') }}</h3>
                        <p class="rounded-xl bg-emerald-50 p-3 text-sm dark:bg-emerald-500/10">{{ __('Amount to send') }}: <strong>{{ money_plain($agreement->fee_collection === 'deduct' ? $agreement->principal - $agreement->processing_fee : $agreement->principal) }}</strong></p>
                        <x-select name="method" :label="__('Method')" :options="['bank' => 'Bank transfer', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'cheque' => 'Cheque', 'cash' => 'Cash']" required />
                        <x-input name="account" :label="__('Customer account / wallet number')" />
                        <x-input name="reference" :label="__('Transfer reference / TrxID')" required />
                        <x-input name="disbursed_at" type="datetime-local" :label="__('Sent at')" :value="now()->format('Y-m-d\TH:i')" required />
                        <x-input name="first_due_date" type="date" :label="__('First installment due date')" :value="now()->addMonth()->format('Y-m-d')" required />
                        <x-textarea name="note" :label="__('Note')" rows="2" />
                        <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="confirm" value="1" class="checkbox mt-0.5" required>{{ __('I confirm the money has actually been transferred to the customer.') }}</label>
                        <button class="btn btn-success w-full"><x-icon name="send" />{{ __('Record Disbursement') }}</button>
                    </form>
                @endif
            @endcan
        @endif

        @if(in_array($st, [S::Submitted, S::UnderReview, S::NidPending, S::DocumentsRequested, S::Approved, S::AwaitingAgreement], true))
            @can('loans.approve')
                <button type="button" @click="$dispatch('open-modal', 'reject')" class="btn btn-outline w-full text-rose-600"><x-icon name="circle-x" />{{ __('Reject Application') }}</button>
            @endcan
        @endif

        @if($application->internal_note)
            <div class="card card-pad text-sm"><strong>{{ __('Internal note') }}:</strong> {{ $application->internal_note }}</div>
        @endif
    </div>
</div>

<x-modal name="reject" :title="__('Reject Application')">
    <form method="POST" action="{{ route('admin.applications.reject', $application) }}" class="space-y-4">@csrf
        <x-textarea name="reason" :label="__('Reason (shown to customer)')" rows="3" required />
        <button class="btn btn-danger w-full">{{ __('Reject') }}</button>
    </form>
</x-modal>
<x-modal name="docs" :title="__('Request Additional Documents')">
    <form method="POST" action="{{ route('admin.applications.request-documents', $application) }}" class="space-y-4">@csrf
        <x-textarea name="message" :label="__('What do you need from the customer?')" rows="3" required />
        <button class="btn btn-warning w-full">{{ __('Send Request') }}</button>
    </form>
</x-modal>
@endsection
