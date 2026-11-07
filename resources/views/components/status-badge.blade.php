{{-- Coloured status pill for any status enum (ApplicationStatus, PaymentStatus, ...). Usage: <x-status-badge :status="$app->status" /> --}}
@props(['status', 'size' => 'sm'])
@php($c = $status->color())
<span {{ $attributes->merge(['class' => "badge bg-{$c}-100 text-{$c}-700 dark:bg-{$c}-500/15 dark:text-{$c}-300 ".($size === 'lg' ? 'px-3.5 py-1.5 text-sm' : '')]) }}>
    <x-icon :name="$status->icon()" class="{{ $size === 'lg' ? 'h-4 w-4' : 'h-3.5 w-3.5' }}" />
    {{ $status->label() }}
</span>
