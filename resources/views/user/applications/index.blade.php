@extends('layouts.user')
@section('title', __('My Applications'))

@section('content')
<x-page-header :title="__('My Applications')">
    <a href="{{ route('user.applications.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" />{{ __('New Application') }}</a>
</x-page-header>

<div class="space-y-3">
    @forelse($applications as $app)
        <a href="{{ route('user.applications.show', $app) }}" class="card card-pad flex flex-col gap-3 transition hover:shadow-lg sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-{{ $app->status->color() }}-100 text-{{ $app->status->color() }}-600 dark:bg-{{ $app->status->color() }}-500/15"><x-icon :name="$app->status->icon()" /></span>
                <div>
                    <p class="font-bold">{{ $app->product->displayName() }} · {{ money($app->finalAmount()) }}</p>
                    <p class="text-sm text-slate-500">{{ $app->reference }} · {{ bn_digits($app->finalMonths()) }} {{ __('months') }} · {{ bn_digits($app->created_at->format('d M Y')) }}</p>
                </div>
            </div>
            <x-status-badge :status="$app->status" />
        </a>
    @empty
        <div class="card"><x-empty icon="file-text" :title="__('No applications yet')" :text="__('Apply for your first loan in a few minutes.')">
            <a href="{{ route('user.applications.create') }}" class="btn btn-primary"><x-icon name="plus" />{{ __('Apply Now') }}</a>
        </x-empty></div>
    @endforelse
</div>
<div class="mt-6">{{ $applications->links() }}</div>
@endsection
