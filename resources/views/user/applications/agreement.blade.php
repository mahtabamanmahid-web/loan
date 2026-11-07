@extends('layouts.user')
@section('title', __('Loan Agreement'))

@php($agreement = $application->agreement)

@section('content')
<x-page-header :title="__('Loan Agreement')" :subtitle="$agreement->agreement_number" :back="route('user.applications.show', $application)">
    <button type="button" onclick="window.print()" class="btn btn-outline btn-sm no-print"><x-icon name="printer" class="h-4 w-4" />{{ __('Print') }}</button>
    <a href="{{ route('user.applications.agreement.pdf', $application) }}" class="btn btn-outline btn-sm no-print" data-no-loading><x-icon name="download" class="h-4 w-4" />{{ __('PDF') }}</a>
</x-page-header>

<article class="card card-pad prose-content max-w-none">
    {{-- Agreement text was generated from a safe template when the agreement was created and is frozen. --}}
    {!! $agreement->content !!}
</article>

@if(! $agreement->isAccepted() && $application->status === \App\Enums\ApplicationStatus::AwaitingAgreement)
    <form method="POST" action="{{ route('user.applications.agreement.accept', $application) }}" class="card card-pad no-print mt-6 space-y-4"
          data-confirm="{{ __('Do you accept this agreement? This acts as your electronic signature.') }}">
        @csrf
        <label class="flex items-start gap-3 text-sm">
            <input type="checkbox" name="accept_agreement" value="1" class="checkbox mt-0.5" required>
            <span>{{ __('I have read and understood this agreement, including the interest, fees, installment schedule and late payment terms, and I agree to it.') }}</span>
        </label>
        @error('accept_agreement')<p class="error-text">{{ $message }}</p>@enderror
        <x-input name="signature_name" :label="__('Type your full name as signature')" icon="pen-line" :placeholder="$application->applicant_name" required />
        <button class="btn btn-success w-full sm:w-auto"><x-icon name="file-check-2" />{{ __('Accept & Sign Agreement') }}</button>
    </form>
@elseif($agreement->isAccepted())
    <x-alert type="success" class="mt-6">{{ __('Accepted on :date', ['date' => bn_digits($agreement->accepted_at->format('d M Y, h:i A'))]) }}</x-alert>
@endif
@endsection
