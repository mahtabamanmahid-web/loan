@extends('layouts.admin')
@section('title', __('FAQ'))

@section('content')
<x-page-header :title="$faq->exists ? __('Edit FAQ') : __('Add FAQ')" :back="route('admin.faqs.index')" />
<form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="card card-pad grid max-w-4xl gap-5 md:grid-cols-2">
    @csrf
    @if($faq->exists) @method('PUT') @endif
    <x-input name="question" :label="__('Question (English)')" :value="$faq->question" required />
    <x-input name="question_bn" :label="__('Question (Bangla)')" :value="$faq->question_bn" />
    <x-textarea name="answer" :label="__('Answer (English)')" :value="$faq->answer" rows="5" required />
    <x-textarea name="answer_bn" :label="__('Answer (Bangla)')" :value="$faq->answer_bn" rows="5" />
    <x-input name="sort_order" type="number" min="0" :label="__('Display order')" :value="$faq->sort_order" />
    <label class="flex items-center gap-2 self-end pb-3 text-sm"><input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $faq->is_active))>{{ __('Show on website') }}</label>
    <div class="md:col-span-2"><button class="btn btn-primary"><x-icon name="save" />{{ __('Save') }}</button></div>
</form>
@endsection
