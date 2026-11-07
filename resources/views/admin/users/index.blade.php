@extends('layouts.admin')
@section('title', __('Customers'))

@section('content')
<x-page-header :title="__('Customers')" :subtitle="__(':count total', ['count' => $users->total()])" />

@include('admin.partials.filters', ['selects' => ['filter' => [
    '' => __('All'), 'pending' => __('NID pending'), 'approved' => __('NID verified'), 'rejected' => __('NID rejected'),
    'resubmit' => __('Resubmission Required'), 'no_kyc' => __('No NID'), 'blocked' => __('Blocked'),
]]])

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Mobile') }}</th><th>{{ __('NID') }}</th><th>{{ __('Applications') }}</th><th>{{ __('Loans') }}</th><th>{{ __('Joined') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($users as $u)
            <tr>
                <td><div class="flex items-center gap-3"><img src="{{ $u->avatarUrl() }}" class="h-9 w-9 rounded-lg" alt=""><div><p class="font-semibold">{{ $u->name }} @if($u->is_blocked)<x-badge color="rose">{{ __('Blocked') }}</x-badge>@endif</p><p class="text-xs text-slate-500">{{ $u->email }}</p></div></div></td>
                <td>{{ $u->phone }}</td>
                <td>@if($u->latestDocument)<x-status-badge :status="$u->latestDocument->status" />@else<span class="text-slate-400">—</span>@endif</td>
                <td>{{ $u->applications_count }}</td>
                <td>{{ $u->loans_count }}</td>
                <td>{{ $u->created_at->format('d M Y') }}</td>
                <td class="text-right"><a href="{{ route('admin.users.show', $u) }}" class="btn btn-outline btn-sm">{{ __('View') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="7"><x-empty icon="users" :title="__('No customers found')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $users->links() }}</div>
@endsection
