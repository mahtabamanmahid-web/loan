@extends('layouts.admin')
@section('title', __('Payment Audit Logs'))

@section('content')
<x-page-header :title="__('Logs')">
    <a href="{{ route('admin.logs.payments', ['level' => 'error']) }}" class="btn btn-outline btn-sm text-rose-600">{{ __('Errors only') }}</a>
</x-page-header>
@include('admin.logs.tabs')
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Time') }}</th><th>{{ __('Payment') }}</th><th>{{ __('Event') }}</th><th>{{ __('Message') }}</th><th>{{ __('IP') }}</th></tr></thead>
        <tbody>
        @forelse($logs as $l)
            <tr>
                <td class="text-xs">{{ $l->created_at->format('d M Y, h:i:s A') }}</td>
                <td>@if($l->transaction)<a href="{{ route('admin.payments.show', $l->transaction) }}" class="font-mono text-xs text-primary">{{ $l->transaction->reference }}</a>@else — @endif</td>
                <td><x-badge :color="['error' => 'rose', 'warning' => 'amber'][$l->level] ?? 'sky'">{{ $l->event }}</x-badge></td>
                <td class="max-w-md whitespace-normal text-xs">{{ $l->message }}</td>
                <td class="font-mono text-xs">{{ $l->ip_address }}</td>
            </tr>
        @empty
            <tr><td colspan="5"><x-empty icon="history" :title="__('No payment logs')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $logs->links() }}</div>
@endsection
