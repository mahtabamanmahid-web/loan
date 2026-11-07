@extends('layouts.admin')
@section('title', __('Announcement'))

@section('content')
<x-page-header :title="$announcement->exists ? __('Edit Announcement') : __('New Announcement')" :back="route('admin.announcements.index')" />
<form method="POST" action="{{ $announcement->exists ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" class="card card-pad grid max-w-3xl gap-5 sm:grid-cols-2">
    @csrf
    @if($announcement->exists) @method('PUT') @endif
    <div class="sm:col-span-2"><x-input name="title" :label="__('Title')" :value="$announcement->title" required /></div>
    <div class="sm:col-span-2"><x-textarea name="body" :label="__('Message')" :value="$announcement->body" rows="3" required /></div>
    <x-select name="type" :label="__('Type')" :options="['info' => 'Info', 'success' => 'Success', 'warning' => 'Warning', 'danger' => 'Important']" :selected="$announcement->type" />
    <x-select name="audience" :label="__('Audience')" :options="['all' => __('Everyone'), 'public' => __('Website visitors'), 'customers' => __('Logged-in customers')]" :selected="$announcement->audience" />
    <x-input name="starts_at" type="datetime-local" :label="__('Starts at')" :value="$announcement->starts_at?->format('Y-m-d\TH:i')" />
    <x-input name="ends_at" type="datetime-local" :label="__('Ends at')" :value="$announcement->ends_at?->format('Y-m-d\TH:i')" />
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $announcement->is_active))>{{ __('Active') }}</label>
    <div class="sm:col-span-2"><button class="btn btn-primary"><x-icon name="save" />{{ __('Save') }}</button></div>
</form>
@endsection
