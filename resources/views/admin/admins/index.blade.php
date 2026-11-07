@extends('layouts.admin')
@section('title', __('Admin Users'))

@section('content')
<x-page-header :title="__('Admin Users')">
    <a href="{{ route('admin.admins.create') }}" class="btn btn-primary btn-sm"><x-icon name="user-plus" class="h-4 w-4" />{{ __('Add Admin') }}</a>
</x-page-header>
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Mobile') }}</th><th>{{ __('Role') }}</th><th>{{ __('Last login') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($admins as $a)
            <tr>
                <td class="font-semibold">{{ $a->name }} @if($a->is_blocked)<x-badge color="rose">{{ __('Blocked') }}</x-badge>@endif @if($a->id === auth()->id())<x-badge color="sky">{{ __('You') }}</x-badge>@endif</td>
                <td>{{ $a->email }}</td><td>{{ $a->phone }}</td>
                <td><x-badge color="indigo" icon="shield">{{ $a->adminRole?->name }}</x-badge></td>
                <td>{{ $a->last_login_at?->diffForHumans() ?? '—' }}</td>
                <td class="text-right">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('admin.admins.edit', $a) }}" class="btn btn-ghost btn-sm"><x-icon name="pencil" class="h-4 w-4" /></a>
                        @if($a->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.admins.destroy', $a) }}" data-confirm="{{ __('Remove this admin?') }}">@csrf @method('DELETE')<button class="btn btn-ghost btn-sm text-rose-600"><x-icon name="trash-2" class="h-4 w-4" /></button></form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
