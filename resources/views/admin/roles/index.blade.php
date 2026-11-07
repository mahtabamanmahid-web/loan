@extends('layouts.admin')
@section('title', __('Roles & Permissions'))

@section('content')
<x-page-header :title="__('Roles & Permissions')" :subtitle="__('Each admin role has its own set of permissions.')" />
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @foreach($roles as $r)
        <div class="card card-pad">
            <div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15"><x-icon name="shield" /></span>
                <div><p class="font-bold">{{ $r->name }}</p><p class="text-xs text-slate-500">{{ $r->users_count }} {{ __('users') }} · {{ $r->slug === 'super_admin' ? __('all permissions') : $r->permissions_count.' '.__('permissions') }}</p></div></div>
            @if($r->description)<p class="mt-3 text-sm text-slate-500">{{ $r->description }}</p>@endif
            @if($r->slug !== 'super_admin')<a href="{{ route('admin.roles.edit', $r) }}" class="btn btn-outline btn-sm mt-4 w-full">{{ __('Edit permissions') }}</a>@endif
        </div>
    @endforeach
</div>
@endsection
