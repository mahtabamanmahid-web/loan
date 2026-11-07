{{--
    Loan application wizard (one form, 4 steps, Alpine "applyWizard"):
      1. Amount & Duration (interactive calculator)
      2. Personal information
      3. Review
      4. Terms & Fee Disclosure → Submit
    Everything is validated again on the server (StoreLoanApplicationRequest).
--}}
@extends('layouts.user')
@section('title', __('Apply for a Loan'))

@php
    $profile = $user->profile;
    // Jump to the step that contains the first validation error
    $errorStep = 1;
    if ($errors->hasAny(['applicant_name', 'present_address', 'permanent_address', 'occupation', 'monthly_income', 'purpose', 'emergency_contact_name', 'emergency_contact_phone'])) { $errorStep = 2; }
    if ($errors->hasAny(['accept_terms', 'accept_fees', 'confirm_info'])) { $errorStep = 4; }
    if ($errors->hasAny(['loan_product_id', 'loan_term_id', 'amount'])) { $errorStep = 1; }
@endphp

@section('content')
<x-page-header :title="__('Apply for a Loan')" :subtitle="__('Fill in 4 simple steps. It is free to apply.')" :back="route('user.dashboard')" />

@if($user->latestDocument?->status?->value === 'pending')
    <x-alert type="info" class="mb-5">{{ __('Your NID is still being verified. You can apply now; review starts after verification.') }}</x-alert>
@endif

@if(empty($calculatorProducts))
    <div class="card"><x-empty icon="package" :title="__('No loan packages available right now')" /></div>
@else
<form method="POST" action="{{ route('user.applications.store') }}" novalidate
      x-data="applyWizard({ products: @js($calculatorProducts), presets: @js($presets), productId: @js(old('loan_product_id', $initial['productId'])), termId: @js(old('loan_term_id', $initial['termId'])), amount: @js(old('amount', $initial['amount'])), step: {{ $errors->any() ? $errorStep : 1 }} })"
      @submit="if (step < total) { $event.preventDefault(); next(); } else if (!validateStep()) { $event.preventDefault(); }">
    @csrf
    <input type="hidden" name="loan_product_id" :value="productId">
    <input type="hidden" name="loan_term_id" :value="termId">
    <input type="hidden" name="amount" :value="amount">

    {{-- Step indicator --}}
    <ol class="mb-6 grid grid-cols-4 gap-2">
        @foreach(['Amount & Duration', 'Your Details', 'Review', 'Terms & Submit'] as $i => $label)
            <li>
                <button type="button" @click="go({{ $i + 1 }})" class="w-full text-left">
                    <span class="block h-1.5 rounded-full transition" :class="step >= {{ $i + 1 }} ? 'bg-gradient-to-r from-primary to-secondary' : 'bg-slate-200 dark:bg-slate-800'"></span>
                    <span class="mt-2 flex items-center gap-1.5 text-xs font-semibold" :class="step >= {{ $i + 1 }} ? 'text-primary' : 'text-slate-400'">
                        <span class="grid h-5 w-5 place-items-center rounded-full text-[10px]" :class="step > {{ $i + 1 }} ? 'bg-primary text-white' : 'border border-current'">{{ bn_digits($i + 1) }}</span>
                        <span class="hidden sm:inline">{{ __($label) }}</span>
                    </span>
                </button>
            </li>
        @endforeach
    </ol>

    @if($errors->any())
        <x-alert type="danger" class="mb-5" :title="__('Please fix the following')">
            <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </x-alert>
    @endif

    {{-- ========== STEP 1: Amount & duration ========== --}}
    <section data-step="1" x-show="step === 1" x-transition.opacity class="grid gap-6 lg:grid-cols-5">
        <div class="card card-pad space-y-6 lg:col-span-3">
            <div>
                <p class="label">{{ __('Loan package') }}</p>
                <div class="grid gap-2 sm:grid-cols-2">
                    <template x-for="p in products" :key="p.id">
                        <button type="button" @click="productId = p.id" :class="productId == p.id ? 'border-primary bg-primary/5 ring-4 ring-primary/10' : 'border-slate-200 dark:border-slate-700'" class="flex items-center gap-3 rounded-xl border-2 p-3 text-left transition">
                            <span class="grid h-10 w-10 place-items-center rounded-lg bg-primary/10 text-primary"><i :data-lucide="p.icon" class="h-5 w-5"></i></span>
                            <span class="text-sm font-bold" x-text="p.name"></span>
                        </button>
                    </template>
                </div>
            </div>
            <div>
                <div class="mb-2 flex items-end justify-between"><label class="label mb-0" for="w-amount">{{ __('How much do you need?') }}</label><span class="text-2xl font-extrabold text-primary" x-text="money(amount)"></span></div>
                <input type="range" class="range" :min="min" :max="max" step="500" x-model.number="amount" aria-label="{{ __('Loan amount') }}">
                <div class="mt-1 flex justify-between text-xs text-slate-500"><span x-text="money(min)"></span><span x-text="money(max)"></span></div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <template x-for="p in visiblePresets" :key="p">
                        <button type="button" @click="amount = p" :class="amount == p ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700'" class="rounded-lg border px-3 py-1.5 text-sm font-semibold" x-text="money(p)"></button>
                    </template>
                </div>
                <div class="relative mt-3">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 font-bold text-slate-400">৳</span>
                    <input id="w-amount" type="number" class="input pl-9" x-model.number="amount" @change="clampAmount()" :min="min" :max="max" placeholder="{{ __('Custom amount') }}">
                </div>
                <p class="error-text" x-show="!amountValid" x-cloak>{{ __('Amount must be between') }} <span x-text="money(min)"></span> – <span x-text="money(max)"></span></p>
            </div>
            <div>
                <p class="label">{{ __('For how many months?') }}</p>
                <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                    <template x-for="t in terms" :key="t.id">
                        <button type="button" @click="termId = t.id" :class="termId == t.id ? 'border-primary bg-primary text-white shadow-lg shadow-primary/25' : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800'" class="rounded-xl border-2 px-2 py-3 text-center transition">
                            <span class="block text-lg font-extrabold" x-text="digits(t.months)"></span>
                            <span class="block text-xs opacity-80">{{ __('months') }} · <span x-text="digits(t.interest_rate) + '%'"></span></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
        <div class="lg:col-span-2">
            <div class="rounded-3xl bg-gradient-to-br from-primary to-secondary p-6 text-white shadow-xl lg:sticky lg:top-24">
                <p class="text-sm text-white/80">{{ __('Monthly installment') }}</p>
                <p class="text-4xl font-extrabold" x-text="money(result.installment)"></p>
                <dl class="mt-5 space-y-2 rounded-2xl bg-white/10 p-4 text-sm">
                    <div class="flex justify-between"><dt class="text-white/80">{{ __('Interest Rate (flat, yearly)') }}</dt><dd x-text="digits(result.rate) + '%'"></dd></div>
                    <div class="flex justify-between"><dt class="text-white/80">{{ __('Total Interest') }}</dt><dd x-text="money(result.totalInterest)"></dd></div>
                    <div class="flex justify-between"><dt class="text-white/80">{{ __('Processing Fee') }}</dt><dd x-text="money(result.fee)"></dd></div>
                    <div class="flex justify-between border-t border-white/20 pt-2 font-bold"><dt>{{ __('Total Repayment') }}</dt><dd x-text="money(result.totalPayable)"></dd></div>
                </dl>
            </div>
        </div>
    </section>

    {{-- ========== STEP 2: Personal details ========== --}}
    <section data-step="2" x-show="step === 2" x-cloak x-transition.opacity class="card card-pad">
        <h2 class="mb-1 text-lg font-bold">{{ __('Your Details') }}</h2>
        <p class="mb-6 text-sm text-slate-500">{{ __('Your mobile number is taken from your account.') }}</p>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-input name="applicant_name" :label="__('Applicant Name')" icon="user" :value="$user->name" required minlength="3" :hint="__('As written on your NID')" />
            <div class="field"><label class="label">{{ __('Mobile Number') }}</label><input class="input bg-slate-50 dark:bg-slate-800" value="{{ $user->phone }}" disabled></div>
            <x-textarea name="present_address" :label="__('Present Address')" :value="$profile?->present_address" rows="2" required minlength="5" />
            <div x-data="{ same: false }">
                <x-textarea name="permanent_address" :label="__('Permanent Address')" :value="$profile?->permanent_address" rows="2" required minlength="5" x-ref="perm" />
                <label class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="checkbox" class="checkbox" x-model="same" @change="if (same) $refs.perm.value = document.getElementById('present_address').value"> {{ __('Same as present address') }}
                </label>
            </div>
            <x-input name="occupation" :label="__('Occupation')" icon="briefcase" :value="$profile?->occupation" required :placeholder="__('e.g. Shop owner, Teacher')" />
            <x-input name="monthly_income" type="number" :label="__('Monthly Income (৳)')" icon="banknote" :value="$profile?->monthly_income ? (int) $profile->monthly_income : null" min="0" required inputmode="numeric" />
            <div class="sm:col-span-2"><x-textarea name="purpose" :label="__('Loan Purpose')" rows="2" required minlength="3" :placeholder="__('What will you use the money for?')" /></div>
            <x-input name="emergency_contact_name" :label="__('Emergency Contact Name')" icon="user-round" :value="$profile?->emergency_contact_name" :hint="__('Optional')" />
            <x-input name="emergency_contact_phone" type="tel" :label="__('Emergency Contact Mobile')" icon="phone" :value="$profile?->emergency_contact_phone" pattern="01[3-9][0-9]{8}" inputmode="numeric" :hint="__('Optional')" />
        </div>
    </section>

    {{-- ========== STEP 3: Review ========== --}}
    <section data-step="3" x-show="step === 3" x-cloak x-transition.opacity class="card card-pad">
        <h2 class="mb-5 text-lg font-bold">{{ __('Please check your loan details') }}</h2>
        <dl class="grid gap-3 sm:grid-cols-2">
            @foreach([
                ['Loan package', 'product?.name'], ['Loan Amount', 'money(result.amount)'], ['Duration', "digits(result.months) + ' ' + ".json_encode(__('months'))],
                ['Interest Rate (flat, yearly)', "digits(result.rate) + '%'"], ['Total Interest', 'money(result.totalInterest)'], ['Processing Fee', 'money(result.fee)'],
                ['Total Repayment', 'money(result.totalPayable)'], ['Monthly installment', 'money(result.installment)'], ['Number of installments', 'digits(result.count)'],
            ] as [$label, $expr])
                <div class="flex justify-between gap-3 rounded-xl bg-slate-50 p-3.5 dark:bg-slate-800"><dt class="text-sm text-slate-500">{{ __($label) }}</dt><dd class="text-right font-bold" x-text="{{ $expr }}"></dd></div>
            @endforeach
            <div class="flex justify-between gap-3 rounded-xl bg-emerald-50 p-3.5 sm:col-span-2 dark:bg-emerald-500/10" x-show="result.collection === 'deduct' && result.fee > 0">
                <dt class="text-sm text-emerald-700 dark:text-emerald-300">{{ __('Amount you will receive (fee deducted)') }}</dt><dd class="font-bold text-emerald-700 dark:text-emerald-300" x-text="money(result.netDisbursement)"></dd>
            </div>
        </dl>
        <p class="hint mt-4">{{ __('The admin may approve a different amount or duration. You will see the final terms in the agreement before you accept.') }}</p>
    </section>

    {{-- ========== STEP 4: Terms & fee disclosure ========== --}}
    <section data-step="4" x-show="step === 4" x-cloak x-transition.opacity class="space-y-5">
        <div class="card card-pad">
            <h2 class="mb-4 flex items-center gap-2 text-lg font-bold"><x-icon name="receipt-text" class="text-primary" />{{ __('Fee Disclosure') }}</h2>
            @foreach($disclosures as $id => $p)
                <div x-show="productId == {{ $id }}" class="space-y-3 text-sm">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-3.5 dark:bg-slate-800"><p class="text-slate-500">{{ __('Processing fee') }}</p><p class="font-bold" x-text="money(result.fee) + ' ({{ bn_digits($p->feeLabel()) }})'"></p></div>
                        <div class="rounded-xl bg-slate-50 p-3.5 dark:bg-slate-800"><p class="text-slate-500">{{ __('How the fee is collected') }}</p><p class="font-bold">{{ $p->fee_collection === 'deduct' ? __('Deducted from the disbursed amount') : __('Paid online after approval, before disbursement') }}</p></div>
                        <div class="rounded-xl bg-slate-50 p-3.5 dark:bg-slate-800"><p class="text-slate-500">{{ __('Refundable?') }}</p><p class="font-bold">{{ $p->fee_refundable ? __('Yes') : __('No') }}</p></div>
                        <div class="rounded-xl bg-slate-50 p-3.5 dark:bg-slate-800"><p class="text-slate-500">{{ __('Paid to') }}</p><p class="font-bold">{{ sys_setting('lender_legal_name') ?: site_name() }}</p></div>
                    </div>
                    @if($p->fee_purpose)<p><strong>{{ __('Why this fee?') }}</strong> {{ $p->fee_purpose }}</p>@endif
                    @if($p->fee_refund_policy)<p><strong>{{ __('Refund conditions') }}:</strong> {{ $p->fee_refund_policy }}</p>@endif
                    @if((float) $p->late_fee_per_installment > 0)<p><strong>{{ __('Late fee per installment') }}:</strong> {{ money($p->late_fee_per_installment) }} ({{ __(':days days grace', ['days' => bn_digits($p->grace_days)]) }})</p>@endif
                </div>
            @endforeach
            <x-alert type="info" class="mt-5">{{ __('Nothing is charged now. Paying any fee never guarantees approval. We never ask for a security deposit.') }}</x-alert>
            <a href="{{ route('loan-terms') }}" target="_blank" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-primary">{{ __('Read full fee & terms disclosure') }}<x-icon name="external-link" class="h-3.5 w-3.5" /></a>
        </div>

        <div class="card card-pad space-y-4">
            @foreach([
                'confirm_info' => __('I confirm that all the information I provided is true and correct.'),
                'accept_fees' => __('I have read and understood the interest, fees and total repayment amount.'),
            ] as $field => $text)
                <label class="field flex items-start gap-3 text-sm"><input type="checkbox" name="{{ $field }}" value="1" class="checkbox mt-0.5" required @checked(old($field))><span>{{ $text }}</span></label>
            @endforeach
            <label class="field flex items-start gap-3 text-sm">
                <input type="checkbox" name="accept_terms" value="1" class="checkbox mt-0.5" required @checked(old('accept_terms'))>
                <span>{!! __('I agree to the :terms and :privacy.', [
                    'terms' => '<a href="'.route('terms').'" target="_blank" class="font-semibold text-primary underline">'.e(__('Terms & Conditions')).'</a>',
                    'privacy' => '<a href="'.route('privacy').'" target="_blank" class="font-semibold text-primary underline">'.e(__('Privacy Policy')).'</a>',
                ]) !!}</span>
            </label>
        </div>
    </section>

    {{-- Navigation buttons --}}
    <div class="sticky bottom-20 z-10 mt-6 flex gap-3 rounded-2xl bg-white/90 p-3 shadow-lg backdrop-blur lg:bottom-4 dark:bg-slate-900/90">
        <button type="button" x-show="step > 1" @click="prev()" class="btn btn-outline"><x-icon name="arrow-left" class="h-4 w-4" />{{ __('Back') }}</button>
        <button type="button" x-show="step < total" @click="next()" class="btn btn-primary flex-1">{{ __('Next step') }}<x-icon name="arrow-right" class="h-4 w-4" /></button>
        <button type="submit" x-show="step === total" x-cloak class="btn btn-success flex-1"><x-icon name="send" />{{ __('Submit Application') }}</button>
    </div>
</form>
@endif
@endsection
