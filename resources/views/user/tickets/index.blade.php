@extends('layouts.user')
@section('title', __('Support'))

@section('content')
<x-page-header :title="__('Support Tickets')" :subtitle="__('Ask us anything about your loan, documents or payments.')">
    <a href="{{ route('user.tickets.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" />{{ __('New Ticket') }}</a>
</x-page-header>

<div class="card overflow-hidden">
    @forelse($tickets as $t)
        <a href="{{ route('user.tickets.show', $t) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 p-4 last:border-0 hover:bg-slate-50 sm:p-5 dark:border-slate-800 dark:hover:bg-slate-800/50">
            <div class="min-w-0">
                <p class="truncate font-semibold">{{ $t->subject }}</p>
                <p class="text-xs text-slate-500">{{ $t->ticket_number }} · {{ __(ucfirst($t->category)) }} · {{ $t->updated_at->diffForHumans() }}</p>
            </div>
            <x-badge :color="$t->statusColor()">{{ $t->statusLabel() }}</x-badge>
        </a>
    @empty
        <x-empty icon="life-buoy" :title="__('No tickets yet')" :text="__('Create a ticket and our team will reply.')">
            <a href="{{ route('user.tickets.create') }}" class="btn btn-primary btn-sm">{{ __('New Ticket') }}</a>
        </x-empty>
    @endforelse
</div>
<div class="mt-6">{{ $tickets->links() }}</div>
@endsection
