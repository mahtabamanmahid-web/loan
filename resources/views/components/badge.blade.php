{{-- Simple coloured pill. Usage: <x-badge color="emerald" icon="check">Paid</x-badge> --}}
@props(['color' => 'slate', 'icon' => null])
<span {{ $attributes->merge(['class' => "badge bg-{$color}-100 text-{$color}-700 dark:bg-{$color}-500/15 dark:text-{$color}-300"]) }}>
    @if($icon)<x-icon :name="$icon" class="h-3.5 w-3.5" />@endif
    {{ $slot }}
</span>
