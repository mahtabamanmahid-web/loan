@extends('layouts.admin')
@section('title', __('Applications'))

@section('content')
<x-page-header :title="__('Loan Applications')" :subtitle="__(':count total', ['count' => $applications->total()])" />

@include('admin.partials.filters', [
    'selects' => ['status' => ['' => __('All')] + collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()],
    'dates' => true,
])

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Applicant') }}</th><th>{{ __('Package') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Duration') }}</th><th>{{ __('NID') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($applications as $a)
            <tr>
                <td class="font-mono text-xs">{{ $a->reference }}</td>
                <td><p class="font-semibold">{{ $a->applicant_name }}</p><p class="text-xs text-slate-500">{{ $a->phone }}</p></td>
                <td>{{ $a->product->name }}</td>
                <td class="font-semibold">{{ money_plain($a->finalAmount()) }}</td>
                <td>{{ $a->finalMonths() }} {{ __('months') }}</td>
                <td>@if($a->user->latestDocument)<x-status-badge :status="$a->user->latestDocument->status" />@else<x-badge color="slate">{{ __('None') }}</x-badge>@endif</td>
                <td><x-status-badge :status="$a->status" /></td>
                <td>{{ $a->created_at->format('d M Y') }}</td>
                <td class="text-right"><a href="{{ route('admin.applications.show', $a) }}" class="btn btn-primary btn-sm">{{ __('Open') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="9"><x-empty icon="file-text" :title="__('No applications found')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $applications->links() }}</div>
@endsection
