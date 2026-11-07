@extends('layouts.admin')
@section('title', __('Payment Methods'))

@section('content')
<x-page-header :title="__('Manual Payment Methods')" :subtitle="__('bKash, Nagad, Rocket, Upay numbers and QR for the security deposit.')">
    <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" class="h-4 w-4" />{{ __('Add Method') }}</a>
</x-page-header>

@if($gatewayActive)
    <x-alert type="warning" class="mb-5" :title="__('UddoktaPay is active')">{{ __('The online gateway is configured, so these manual methods are hidden from customers and UddoktaPay is shown instead. Remove the UddoktaPay API key from .env to use manual methods.') }}</x-alert>
@else
    <x-alert type="info" class="mb-5">{{ __('UddoktaPay is not configured, so customers pay using the active methods below and submit the Transaction ID for your approval.') }}</x-alert>
@endif

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @forelse($methods as $m)
        <div class="card card-pad flex flex-col">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-primary/10 text-primary"><x-icon :name="$m->icon()" /></span>
                    <div><p class="font-bold">{{ $m->name }}</p><p class="text-xs text-slate-500">{{ ucfirst($m->type) }}{{ $m->account_kind ? ' · '.ucfirst($m->account_kind) : '' }}</p></div>
                </div>
                @if($m->is_active)<x-badge color="emerald">{{ __('Active') }}</x-badge>@else<x-badge color="slate">{{ __('Inactive') }}</x-badge>@endif
            </div>
            @if($m->account_number)<p class="mt-3 font-mono text-lg font-bold">{{ $m->account_number }}</p>@endif
            @if($m->qrUrl())<img src="{{ $m->qrUrl() }}" alt="QR" class="mt-3 h-28 w-28 rounded-lg border border-slate-200 object-contain dark:border-slate-700">@endif
            @if($m->instructions)<p class="mt-2 text-sm text-slate-500">{{ \Illuminate\Support\Str::limit($m->instructions, 120) }}</p>@endif
            <div class="mt-auto flex gap-2 pt-4">
                <a href="{{ route('admin.payment-methods.edit', $m) }}" class="btn btn-outline btn-sm flex-1"><x-icon name="pencil" class="h-4 w-4" />{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('admin.payment-methods.destroy', $m) }}" data-confirm="{{ __('Delete?') }}">@csrf @method('DELETE')<button class="btn btn-ghost btn-sm text-rose-600"><x-icon name="trash-2" class="h-4 w-4" /></button></form>
            </div>
        </div>
    @empty
        <div class="card md:col-span-2 xl:col-span-3"><x-empty icon="wallet" :title="__('No payment methods yet')" :text="__('Add bKash, Nagad, Rocket or Upay so customers can pay the deposit.')">
            <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary btn-sm">{{ __('Add Method') }}</a>
        </x-empty></div>
    @endforelse
</div>
@endsection
