{{--
    Loan agreement template. Rendered ONCE when the admin creates the agreement and
    stored in loan_agreements.content, so later setting changes never alter a signed agreement.
    All dynamic values are escaped with {{ }}.
    Variables: $application (LoanApplication), $calc (LoanCalculator::calculate result)
--}}
@php
    $product = $application->product;
    $lender = sys_setting('lender_legal_name') ?: site_name();
@endphp
<h2>{{ __('Loan Agreement') }}</h2>
<p>{{ __('This agreement is made on :date between :lender ("Lender") and :borrower ("Borrower").', ['date' => now()->format('d M Y'), 'lender' => $lender, 'borrower' => $application->applicant_name]) }}</p>
@if(sys_setting('lender_license_number'))<p>{{ __('License No.') }}: {{ sys_setting('lender_license_number') }}</p>@endif

<h3>1. {{ __('Borrower') }}</h3>
<ul>
    <li>{{ __('Name') }}: {{ $application->applicant_name }}</li>
    <li>{{ __('Mobile Number') }}: {{ $application->phone }}</li>
    @if($application->email)<li>{{ __('Email') }}: {{ $application->email }}</li>@endif
    <li>{{ __('Present Address') }}: {{ $application->present_address }}</li>
    <li>{{ __('Application Reference') }}: {{ $application->reference }}</li>
</ul>

<h3>2. {{ __('Loan terms') }}</h3>
<ul>
    <li>{{ __('Loan package') }}: {{ $product->name }}</li>
    <li>{{ __('Principal amount') }}: BDT {{ number_format($calc['amount'], 2) }}</li>
    <li>{{ __('Duration') }}: {{ $calc['months'] }} {{ __('months') }}</li>
    <li>{{ __('Interest rate') }}: {{ $calc['interest_rate'] }}% {{ __('per year, flat') }}</li>
    <li>{{ __('Total interest') }}: BDT {{ number_format($calc['total_interest'], 2) }}</li>
    <li>{{ __('Total repayment') }}: BDT {{ number_format($calc['total_payable'], 2) }}</li>
    <li>{{ __('Installment') }}: BDT {{ number_format($calc['installment_amount'], 2) }} × {{ $calc['installments'] }} ({{ $calc['frequency'] === 'weekly' ? __('weekly') : __('monthly') }})</li>
</ul>

<h3>3. {{ __('Fees') }}</h3>
<ul>
    <li>{{ __('Processing fee') }}: BDT {{ number_format($calc['processing_fee'], 2) }} ({{ $product->feeLabel() }})</li>
    <li>{{ __('Collection') }}: {{ $calc['fee_collection'] === 'deduct' ? __('Deducted from the disbursed amount') : __('Paid online after approval, before disbursement') }}</li>
    <li>{{ __('Amount to be disbursed') }}: BDT {{ number_format($calc['net_disbursement'], 2) }}</li>
    <li>{{ __('Refundable?') }}: {{ $product->fee_refundable ? __('Yes') : __('No') }}</li>
    @if($product->fee_purpose)<li>{{ __('Purpose of fee') }}: {{ $product->fee_purpose }}</li>@endif
    @if($product->fee_refund_policy)<li>{{ __('Refund conditions') }}: {{ $product->fee_refund_policy }}</li>@endif
    <li>{{ __('Fee recipient') }}: {{ $lender }}</li>
    <li>{{ __('Late fee') }}: {{ (float) $product->late_fee_per_installment > 0 ? 'BDT '.number_format($product->late_fee_per_installment, 2).' '.__('per overdue installment after :days days grace', ['days' => $product->grace_days]) : __('None') }}</li>
    <li>{{ __('No other charges apply unless agreed in writing.') }}</li>
</ul>

<h3>4. {{ __('Repayment') }}</h3>
<p>{{ __('The Borrower will pay each installment on or before its due date through the online dashboard or other official channels listed by the Lender. The installment schedule will be shown in the Borrower\'s dashboard after disbursement.') }}</p>

<h3>5. {{ __('Early repayment') }}</h3>
<p>{{ __('The Borrower may repay the outstanding amount early at any time without penalty.') }}</p>

<h3>6. {{ __('Data protection') }}</h3>
<p>{{ __('Personal and NID data are used only for verification and loan servicing, stored securely, and shared only as required by law.') }}</p>

<h3>7. {{ __('Disbursement') }}</h3>
<p>{{ __('Signing this agreement does not by itself transfer money. The loan starts only when the Lender records the actual disbursement, which will be notified to the Borrower.') }}</p>

<h3>8. {{ __('Governing law') }}</h3>
<p>{{ __('This agreement is governed by the laws of Bangladesh.') }}</p>

@if(setting('loan_terms_disclosure'))
    <h3>9. {{ __('Additional terms') }}</h3>
    {!! safe_markdown(setting('loan_terms_disclosure')) !!}
@endif
