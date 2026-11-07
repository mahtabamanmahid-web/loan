@extends('layouts.admin')
@section('title', $review->exists ? __('Edit Review') : __('Add Review'))

@section('content')
<x-page-header :title="$review->exists ? __('Edit Review') : __('Add Review')" :back="route('admin.reviews.index')" />

<form method="POST" action="{{ $review->exists ? route('admin.reviews.update', $review) : route('admin.reviews.store') }}" class="card card-pad max-w-2xl space-y-5">
    @csrf
    @if($review->exists) @method('PUT') @endif
    <x-alert type="warning">{{ __('Only enter feedback that a real customer actually gave you (e.g. by phone or in person). Never create fake reviews.') }}</x-alert>
    @if($customers->isEmpty())
        <x-alert type="info">{{ __('No customers with a disbursed loan yet.') }}</x-alert>
    @endif
    <x-select name="user_id" :label="__('Customer (must have a loan)')" :options="$customers->mapWithKeys(fn ($c) => [$c->id => $c->name.' – '.$c->phone])->all()" :selected="$review->user_id" :placeholder="__('Select customer')" required />
    <div class="grid gap-5 sm:grid-cols-2">
        <x-input name="display_name" :label="__('Display name')" :value="$review->display_name" required />
        <x-input name="location" :label="__('Location')" :value="$review->location" />
    </div>
    <x-select name="rating" :label="__('Review Rating')" :options="[5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★']" :selected="$review->rating" />
    <x-textarea name="comment" :label="__('Comment')" :value="$review->comment" rows="4" required />
    <div class="flex flex-wrap gap-5 text-sm">
        <label class="flex items-center gap-2"><input type="checkbox" name="is_verified" value="1" class="checkbox" @checked(old('is_verified', $review->is_verified))>{{ __('Verified real customer') }}</label>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" class="checkbox" @checked(old('is_published', $review->is_published))>{{ __('Publish') }}</label>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" class="checkbox" @checked(old('is_featured', $review->is_featured))>{{ __('Featured Review') }}</label>
    </div>
    <button class="btn btn-primary"><x-icon name="save" />{{ __('Save') }}</button>
</form>
@endsection
