@extends('layouts.user')
@section('title', __('Login History'))

@section('content')
<x-page-header :title="__('Login History')" :subtitle="__('If you see a login you do not recognise, change your password immediately.')" :back="route('user.profile.edit')" />
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Date & Time') }}</th><th>{{ __('Result') }}</th><th>{{ __('IP address') }}</th><th>{{ __('Device') }}</th></tr></thead>
        <tbody>
        @forelse($logins as $l)
            <tr>
                <td>{{ bn_digits($l->created_at->format('d M Y, h:i A')) }}</td>
                <td>@if($l->successful)<x-badge color="emerald" icon="check">{{ __('Success') }}</x-badge>@else<x-badge color="rose" icon="x">{{ __('Failed') }}</x-badge>@endif</td>
                <td class="font-mono text-xs">{{ $l->ip_address }}</td>
                <td class="max-w-xs truncate text-xs" title="{{ $l->user_agent }}">{{ \Illuminate\Support\Str::limit($l->user_agent, 60) }}</td>
            </tr>
        @empty
            <tr><td colspan="4"><x-empty icon="history" :title="__('No login records')" /></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $logins->links() }}</div>
@endsection
