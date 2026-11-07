@extends('layouts.admin')
@section('title', __('Activity Logs'))

@section('content')
<x-page-header :title="__('Logs')" />
@include('admin.logs.tabs')
@include('admin.partials.filters', ['placeholder' => __('Action or description...')])
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Time') }}</th><th>{{ __('Admin') }}</th><th>{{ __('Action') }}</th><th>{{ __('Description') }}</th><th>{{ __('IP') }}</th></tr></thead>
        <tbody>
        @forelse($logs as $l)
            <tr>
                <td class="text-xs">{{ $l->created_at->format('d M Y, h:i:s A') }}</td>
                <td>{{ $l->admin?->name ?? '—' }}</td>
                <td><x-badge color="indigo">{{ $l->action }}</x-badge></td>
                <td class="max-w-md whitespace-normal text-xs">{{ $l->description }}</td>
                <td class="font-mono text-xs">{{ $l->ip_address }}</td>
            </tr>
        @empty
            <tr><td colspan="5"><x-empty icon="history" :title="__('No activity yet')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $logs->links() }}</div>
@endsection
