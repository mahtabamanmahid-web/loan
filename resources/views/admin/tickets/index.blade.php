@extends('layouts.admin')
@section('title', __('Support Tickets'))

@section('content')
<x-page-header :title="__('Support Tickets')" />
<div class="mb-5 flex gap-2 overflow-x-auto">
    @foreach(['active' => 'Needs reply', 'answered' => 'Answered', 'closed' => 'Closed', 'all' => 'All'] as $k => $l)
        <a href="{{ route('admin.tickets.index', ['status' => $k]) }}" class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold {{ $status === $k ? 'bg-primary text-white' : 'bg-white text-slate-600 dark:bg-slate-900 dark:text-slate-300' }}">{{ __($l) }}</a>
    @endforeach
</div>
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Ticket') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Category') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Status') }}</th><th>{{ __('Assigned') }}</th><th>{{ __('Updated') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($tickets as $t)
            <tr>
                <td><p class="font-semibold">{{ \Illuminate\Support\Str::limit($t->subject, 40) }}</p><p class="font-mono text-xs text-slate-500">{{ $t->ticket_number }}</p></td>
                <td>{{ $t->user->name }}<br><span class="text-xs text-slate-500">{{ $t->user->phone }}</span></td>
                <td>{{ $t->category }}</td>
                <td><x-badge :color="['high' => 'rose', 'normal' => 'sky', 'low' => 'slate'][$t->priority] ?? 'slate'">{{ $t->priority }}</x-badge></td>
                <td><x-badge :color="$t->statusColor()">{{ $t->statusLabel() }}</x-badge></td>
                <td>{{ $t->assignee?->name ?? '—' }}</td>
                <td>{{ $t->updated_at->diffForHumans() }}</td>
                <td class="text-right"><a href="{{ route('admin.tickets.show', $t) }}" class="btn btn-primary btn-sm">{{ __('Open') }}</a></td>
            </tr>
        @empty
            <tr><td colspan="8"><x-empty icon="life-buoy" :title="__('No tickets')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $tickets->links() }}</div>
@endsection
