@extends('pdf.layout')
@section('title', 'Payment Receipt '.$tx->reference)

@section('content')
<p class="title">PAYMENT RECEIPT</p>
<p class="muted">Receipt No: {{ $tx->reference }} &nbsp; <span class="badge">{{ strtoupper($tx->status->value === 'completed' ? 'PAID' : $tx->status->value) }}</span></p>

<div class="box" style="text-align:center">
    <div class="muted">Amount paid</div>
    <div style="font-size:26px;font-weight:bold">{{ money_plain($tx->amount) }}</div>
</div>

<table class="kv">
    <tr><td>Customer</td><td>{{ $tx->user->name }} ({{ $tx->user->phone }})</td></tr>
    <tr><td>Payment purpose</td><td>{{ $tx->purpose === 'processing_fee' ? 'Processing fee' : 'Loan installment' }}</td></tr>
    <tr><td>Description</td><td>{{ $tx->description }}</td></tr>
    @if($tx->application)<tr><td>Application</td><td>{{ $tx->application->reference }}</td></tr>@endif
    @if($tx->loan)<tr><td>Loan</td><td>{{ $tx->loan->loan_number }}</td></tr>@endif
    @if($tx->installment)<tr><td>Installment</td><td>#{{ $tx->installment->installment_number }} (due {{ $tx->installment->due_date->format('d M Y') }})</td></tr>@endif
    <tr><td>Transaction ID</td><td>{{ $tx->gateway_transaction_id }}</td></tr>
    <tr><td>Invoice ID</td><td>{{ $tx->gateway_invoice_id }}</td></tr>
    <tr><td>Payment method</td><td>{{ ucfirst((string) $tx->payment_method) }} {{ $tx->sender_number ? '('.$tx->sender_number.')' : '' }}</td></tr>
    <tr><td>Payment date</td><td>{{ optional($tx->paid_at)->format('d M Y, h:i A') }}</td></tr>
    <tr><td>Verified with gateway</td><td>{{ optional($tx->verified_at)->format('d M Y, h:i A') }}</td></tr>
    @if((float) $tx->refunded_amount > 0)<tr><td>Refunded</td><td>{{ money_plain($tx->refunded_amount) }} on {{ optional($tx->refunded_at)->format('d M Y') }} - {{ $tx->refund_reason }}</td></tr>@endif
    <tr><td>Received by</td><td>{{ sys_setting('lender_legal_name') ?: site_name() }}</td></tr>
</table>

@if($tx->purpose === 'processing_fee')
    <p class="muted" style="margin-top:14px">Note: This receipt confirms payment of a disclosed fee only. It is not a loan approval or disbursement.</p>
@endif
@endsection
