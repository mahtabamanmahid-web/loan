@extends('layouts.user')
@section('title', __('Notifications'))

@section('content')
<x-page-header :title="__('Notifications')">
    @if(auth()->user()->unreadNotifications()->exists())
        <form method="POST" action="{{ route('user.notifications.read-all') }}">@csrf<button class="btn btn-outline btn-sm"><x-icon name="check-check" class="h-4 w-4" />{{ __('Mark all as read') }}</button></form>
    @endif
</x-page-header>

<div class="card overflow-hidden">
    @forelse($notifications as $n)
        @php($c = $n->data['color'] ?? 'indigo')
        <a href="{{ route('user.notifications.open', $n->id) }}" class="flex gap-4 border-b border-slate-100 p-4 last:border-0 hover:bg-slate-50 sm:p-5 dark:border-slate-800 dark:hover:bg-slate-800/50 {{ $n->read_at ? '' : 'bg-primary/[.03]' }}">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-{{ $c }}-100 text-{{ $c }}-600 dark:bg-{{ $c }}-500/15"><x-icon :name="$n->data['icon'] ?? 'bell'" /></span>
            <div class="min-w-0 flex-1">
                <p class="flex items-center gap-2 font-semibold">{{ $n->data['title'] ?? '' }} @unless($n->read_at)<span class="h-2 w-2 rounded-full bg-primary"></span>@endunless</p>
                <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-400">{{ $n->data['message'] ?? '' }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</p>
            </div>
        </a>
    @empty
        <x-empty icon="bell-off" :title="__('No notifications')" />
    @endforelse
</div>
<div class="mt-6">{{ $notifications->links() }}</div>
@endsection
