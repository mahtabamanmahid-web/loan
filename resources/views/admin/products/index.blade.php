@extends('layouts.admin')
@section('title', __('Loan Packages'))

@section('content')
<x-page-header :title="__('Loan Packages')" :subtitle="__('Amount limits, durations, interest rates and fees.')">
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" />{{ __('Add Package') }}</a>
</x-page-header>

<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    @forelse($products as $p)
        <div class="card card-pad flex flex-col">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br from-primary to-secondary text-white"><x-icon :name="$p->icon" /></span>
                    <div><p class="font-bold">{{ $p->name }}</p><p class="text-xs text-slate-500">{{ $p->name_bn }}</p></div>
                </div>
                @if($p->is_active)<x-badge color="emerald">{{ __('Active') }}</x-badge>@else<x-badge color="slate">{{ __('Inactive') }}</x-badge>@endif
            </div>
            <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
                <div><dt class="text-slate-500">{{ __('Amount') }}</dt><dd class="font-semibold">{{ number_format($p->min_amount) }} – {{ number_format($p->max_amount) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Fee') }}</dt><dd class="font-semibold">{{ $p->feeLabel() }} · {{ $p->fee_collection }}</dd></div>
            </dl>
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach($p->terms as $t)<x-badge :color="$t->is_active ? 'indigo' : 'slate'">{{ $t->months }}m · {{ (float) $t->interest_rate }}%</x-badge>@endforeach
            </div>
            <div class="mt-auto flex gap-2 pt-5">
                <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-outline btn-sm flex-1"><x-icon name="pencil" class="h-4 w-4" />{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('admin.products.destroy', $p) }}" data-confirm="{{ __('Delete this package?') }}">@csrf @method('DELETE')<button class="btn btn-ghost btn-sm text-rose-600" aria-label="{{ __('Delete') }}"><x-icon name="trash-2" class="h-4 w-4" /></button></form>
            </div>
        </div>
    @empty
        <div class="card md:col-span-3"><x-empty icon="package" :title="__('No packages yet')" /></div>
    @endforelse
</div>
@endsection
