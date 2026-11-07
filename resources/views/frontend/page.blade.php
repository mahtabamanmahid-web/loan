@extends('layouts.app')
@section('title', $title)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
    <x-page-header :title="$title" />
    <article class="card card-pad prose-content">
        @if($content)
            {!! safe_markdown($content) !!}
        @else
            <p class="text-slate-500">{{ __('This page has not been published yet.') }}</p>
        @endif
    </article>
</div>
@endsection
