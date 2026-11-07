@extends('layouts.admin')
@section('title', $role->name)

@php($current = $role->permissions->pluck('id')->all())

@section('content')
<x-page-header :title="__('Edit Role').': '.$role->name" :back="route('admin.roles.index')" />
<form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-6">
    @csrf @method('PUT')
    <div class="card card-pad grid gap-5 sm:grid-cols-2">
        <x-input name="name" :label="__('Role name')" :value="$role->name" required />
        <x-input name="description" :label="__('Description')" :value="$role->description" />
    </div>
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach($groups as $group => $perms)
            <div class="card card-pad">
                <h3 class="mb-3 font-bold capitalize">{{ __(ucfirst($group)) }}</h3>
                @foreach($perms as $p)
                    <label class="flex items-center gap-3 py-1.5 text-sm"><input type="checkbox" name="permissions[]" value="{{ $p->id }}" class="checkbox" @checked(in_array($p->id, old('permissions', $current)))><span>{{ $p->name }} <span class="font-mono text-xs text-slate-400">{{ $p->slug }}</span></span></label>
                @endforeach
            </div>
        @endforeach
    </div>
    <button class="btn btn-primary btn-lg"><x-icon name="save" />{{ __('Save Role') }}</button>
</form>
@endsection
