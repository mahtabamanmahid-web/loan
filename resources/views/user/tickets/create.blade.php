@extends('layouts.user')
@section('title', __('New Ticket'))

@section('content')
<x-page-header :title="__('New Support Ticket')" :back="route('user.tickets.index')" />
<form method="POST" action="{{ route('user.tickets.store') }}" class="card card-pad max-w-2xl space-y-5">
    @csrf
    <x-input name="subject" :label="__('Subject')" icon="tag" required maxlength="150" />
    <x-select name="category" :label="__('Category')" required :options="[
        'general' => __('General'), 'loan' => __('Loan'), 'payment' => __('Payment'), 'kyc' => __('NID / KYC'), 'technical' => __('Technical problem'),
    ]" />
    <x-textarea name="message" :label="__('Message')" rows="6" required minlength="10" :placeholder="__('Describe your problem. Never share your password or OTP.')" />
    <button class="btn btn-primary"><x-icon name="send" />{{ __('Send') }}</button>
</form>
@endsection
