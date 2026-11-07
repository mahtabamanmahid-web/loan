@extends('layouts.admin')
@section('title', __('Login History'))

@section('content')
<x-page-header :title="__('Logs')">
    <a href="{{ route('admin.logs.logins', ['failed' => 1]) }}" class="btn btn-outline btn-sm text-rose-600">{{ __('Failed only') }}</a>
</x-page-header>
@include('admin.logs.tabs')
@include('admin.partials.filters', ['placeholder' => __('Phone, email or IP...')])
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Time') }}</th><th>{{ __('Login') }}</th><th>{{ __('User') }}</th><th>{{ __('Result') }}</th><th>{{ __('IP') }}</th><th>{{ __('Device') }}</th></tr></thead>
        <tbody>
        @forelse($logs as $l)
            <tr>
                <td class="text-xs">{{ $l->created_at->format('d M Y, h:i:s A') }}</td>
                <td>{{ $l->login_identifier }}</td>
                <td>{{ $l->user?->name ?? '—' }}</td>
                <td>@if($l->successful)<x-badge color="emerald">{{ __('Success') }}</x-badge>@else<x-badge color="rose">{{ __('Failed') }}</x-badge>@endif</td>
                <td class="font-mono text-xs">{{ $l->ip_address }}</td>
                <td class="max-w-xs truncate text-xs" title="{{ $l->user_agent }}">{{ \Illuminate\Support\Str::limit($l->user_agent, 50) }}</td>
            </tr>
        @empty
            <tr><td colspan="6"><x-empty icon="history" :title="__('No login records')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $logs->links() }}</div>
@endsection
