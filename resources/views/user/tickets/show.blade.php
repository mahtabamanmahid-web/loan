@extends('layouts.user')
@section('title', $ticket->subject)

@section('content')
<x-page-header :title="$ticket->subject" :subtitle="$ticket->ticket_number" :back="route('user.tickets.index')">
    <x-badge :color="$ticket->statusColor()">{{ $ticket->statusLabel() }}</x-badge>
    @if($ticket->status !== 'closed')
        <form method="POST" action="{{ route('user.tickets.close', $ticket) }}" data-confirm="{{ __('Close this ticket?') }}">@csrf<button class="btn btn-ghost btn-sm">{{ __('Close ticket') }}</button></form>
    @endif
</x-page-header>

<div class="space-y-4">
    @foreach($ticket->replies as $r)
        <div class="flex {{ $r->is_staff ? '' : 'justify-end' }}">
            <div class="max-w-[85%] rounded-2xl p-4 {{ $r->is_staff ? 'rounded-tl-sm bg-white shadow-sm dark:bg-slate-900' : 'rounded-tr-sm bg-gradient-to-br from-primary to-secondary text-white' }}">
                <p class="mb-1 text-xs font-semibold {{ $r->is_staff ? 'text-primary' : 'text-white/80' }}">{{ $r->is_staff ? site_name().' '.__('Support') : __('You') }} · {{ $r->created_at->diffForHumans() }}</p>
                <p class="whitespace-pre-line text-sm">{{ $r->message }}</p>
            </div>
        </div>
    @endforeach
</div>

@if($ticket->status !== 'closed')
    <form method="POST" action="{{ route('user.tickets.reply', $ticket) }}" class="card card-pad mt-6 space-y-3">
        @csrf
        <x-textarea name="message" :label="__('Your reply')" rows="3" required />
        <button class="btn btn-primary"><x-icon name="send" />{{ __('Send Reply') }}</button>
    </form>
@endif
@endsection
