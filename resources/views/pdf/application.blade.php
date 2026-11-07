@extends('pdf.layout')
@section('title', 'Loan Application '.$application->reference)

@section('content')
<p class="title">LOAN APPLICATION FORM</p>
<p class="muted">Reference: {{ $application->reference }} · Submitted: {{ $application->created_at->format('d M Y, h:i A') }} · Status: {{ str_replace('_', ' ', ucfirst($application->status->value)) }}</p>

<h2>Applicant</h2>
<table class="kv">
    <tr><td>Name</td><td>{{ $application->applicant_name }}</td></tr>
    <tr><td>Mobile</td><td>{{ $application->phone }}</td></tr>
    @if($application->email)<tr><td>Email</td><td>{{ $application->email }}</td></tr>@endif
    <tr><td>Occupation</td><td>{{ $application->occupationLabel() }}</td></tr>
    <tr><td>Loan purpose</td><td>{{ $application->purposeLabel() }}</td></tr>
</table>

<h2>Requested Loan</h2>
<table class="kv">
    <tr><td>Package</td><td>{{ $application->product->name }}</td></tr>
    <tr><td>Amount</td><td>{{ money_plain($application->requested_amount) }}</td></tr>
    <tr><td>Duration</td><td>{{ $application->requested_months }} months</td></tr>
    <tr><td>Interest rate</td><td>{{ (float) $application->interest_rate }}% per year (flat)</td></tr>
    <tr><td>Total interest</td><td>{{ money_plain($application->total_interest) }}</td></tr>
    <tr><td>Total repayment</td><td>{{ money_plain($application->total_payable) }}</td></tr>
    <tr><td>Installment</td><td>{{ money_plain($application->installment_amount) }}</td></tr>
    <tr><td>Processing fee</td><td>{{ money_plain($application->processing_fee) }}</td></tr>
    @if($application->approved_amount)
        <tr><td>Approved amount</td><td>{{ money_plain($application->approved_amount) }} · {{ $application->approved_months }} months · {{ (float) $application->approved_interest_rate }}%</td></tr>
    @endif
</table>

<p class="muted" style="margin-top:14px">Terms accepted electronically on {{ optional($application->terms_accepted_at)->format('d M Y, h:i A') }} from IP {{ $application->terms_accepted_ip }}.</p>
@endsection
