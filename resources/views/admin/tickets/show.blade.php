@extends('layouts.admin')
@section('title', $ticket->subject)

@section('content')
<x-page-header :title="$ticket->subject" :subtitle="$ticket->ticket_number.' · '.$ticket->user->name.' · '.$ticket->user->phone" :back="route('admin.tickets.index')">
    <x-badge :color="$ticket->statusColor()">{{ $ticket->statusLabel() }}</x-badge>
</x-page-header>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">
        @foreach($ticket->replies as $r)
            <div class="flex {{ $r->is_staff ? 'justify-end' : '' }}">
                <div class="max-w-[85%] rounded-2xl p-4 {{ $r->is_staff ? 'rounded-tr-sm bg-gradient-to-br from-primary to-secondary text-white' : 'rounded-tl-sm bg-white shadow-sm dark:bg-slate-900' }}">
                    <p class="mb-1 text-xs font-semibold opacity-80">{{ $r->user->name }} · {{ $r->created_at->format('d M, h:i A') }}</p>
                    <p class="whitespace-pre-line text-sm">{{ $r->message }}</p>
                </div>
            </div>
        @endforeach
        <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" class="card card-pad space-y-3">@csrf
            <x-textarea name="message" :label="__('Reply to customer')" rows="4" required />
            <button class="btn btn-primary"><x-icon name="send" />{{ __('Send Reply') }}</button>
        </form>
    </div>
    <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}" class="card card-pad h-fit space-y-4">@csrf
        <h3 class="font-bold">{{ __('Ticket settings') }}</h3>
        <x-select name="status" :label="__('Status')" :options="['open' => __('Open'), 'answered' => __('Answered'), 'closed' => __('Closed')]" :selected="$ticket->status === 'customer_reply' ? 'open' : $ticket->status" />
        <x-select name="priority" :label="__('Priority')" :options="['low' => 'Low', 'normal' => 'Normal', 'high' => 'High']" :selected="$ticket->priority" />
        <x-select name="assigned_to" :label="__('Assigned to')" :options="$staff->pluck('name', 'id')->all()" :selected="$ticket->assigned_to" :placeholder="__('Unassigned')" />
        <button class="btn btn-outline w-full">{{ __('Update') }}</button>
        <a href="{{ route('admin.users.show', $ticket->user) }}" class="block text-center text-sm font-semibold text-primary">{{ __('Customer profile') }}</a>
    </form>
</div>
@endsection
