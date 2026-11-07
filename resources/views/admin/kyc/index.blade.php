@extends('layouts.admin')
@section('title', __('NID Verification'))

@section('content')
<x-page-header :title="__('NID Verification')" :subtitle="__('Review customer identity documents.')" />

<div class="mb-5 flex gap-2 overflow-x-auto">
    @foreach(['pending' => 'Pending Verification', 'approved' => 'Verified', 'resubmit' => 'Resubmission Required', 'rejected' => 'Rejected', 'all' => 'All'] as $k => $l)
        <a href="{{ route('admin.kyc.index', ['status' => $k]) }}" class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold {{ $status === $k ? 'bg-primary text-white' : 'bg-white text-slate-600 dark:bg-slate-900 dark:text-slate-300' }}">{{ __($l) }}</a>
    @endforeach
</div>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('NID') }}</th><th>{{ __('Submitted') }}</th><th>{{ __('Status') }}</th><th>{{ __('Reviewed by') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($documents as $d)
            <tr>
                <td><p class="font-semibold">{{ $d->user->name }}</p><p class="text-xs text-slate-500">{{ $d->user->phone }}</p></td>
                <td>{{ __('NID photo') }}</td>
                <td>{{ $d->created_at->format('d M Y, h:i A') }}</td>
                <td><x-status-badge :status="$d->status" /></td>
                <td>{{ $d->reviewer?->name ?? '—' }}</td>
                <td class="text-right"><a href="{{ route('admin.kyc.show', $d) }}" class="btn btn-primary btn-sm">{{ __('Review') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="6"><x-empty icon="id-card" :title="__('Nothing to review')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $documents->links() }}</div>
@endsection
