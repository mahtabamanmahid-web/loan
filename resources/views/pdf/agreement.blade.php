@extends('pdf.layout')
@section('title', 'Loan Agreement '.$application->agreement->agreement_number)

@section('content')
<p class="muted">Agreement No: {{ $application->agreement->agreement_number }}</p>
{{-- Frozen agreement text (generated from a template with escaped values) --}}
{!! $application->agreement->content !!}

<div class="box">
    @if($application->agreement->accepted_at)
        <strong>Electronically accepted</strong> by {{ $application->applicant_name }} on {{ $application->agreement->accepted_at->format('d M Y, h:i A') }} from IP {{ $application->agreement->accepted_ip }}.
    @else
        <strong>Not yet accepted by the borrower.</strong>
    @endif
</div>
@endsection
