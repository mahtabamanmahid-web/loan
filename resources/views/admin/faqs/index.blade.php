@extends('layouts.admin')
@section('title', __('FAQ'))

@section('content')
<x-page-header :title="__('FAQ Management')">
    <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" />{{ __('Add FAQ') }}</a>
</x-page-header>
<div class="space-y-3">
    @forelse($faqs as $f)
        <div class="card card-pad flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="font-semibold">{{ $f->sort_order }}. {{ $f->question }} @unless($f->is_active)<x-badge color="slate">{{ __('Hidden') }}</x-badge>@endunless</p>
                <p class="text-sm text-slate-500">{{ $f->question_bn }}</p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($f->answer, 160) }}</p>
            </div>
            <div class="flex shrink-0 gap-1">
                <a href="{{ route('admin.faqs.edit', $f) }}" class="btn btn-ghost btn-sm" aria-label="{{ __('Edit') }}"><x-icon name="pencil" class="h-4 w-4" /></a>
                <form method="POST" action="{{ route('admin.faqs.destroy', $f) }}" data-confirm="{{ __('Delete?') }}">@csrf @method('DELETE')<button class="btn btn-ghost btn-sm text-rose-600" aria-label="{{ __('Delete') }}"><x-icon name="trash-2" class="h-4 w-4" /></button></form>
            </div>
        </div>
    @empty
        <div class="card"><x-empty icon="circle-help" :title="__('No FAQs yet')" /></div>
    @endforelse
</div>
@endsection
