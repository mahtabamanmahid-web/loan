{{-- Inline message box. Usage: <x-alert type="warning" title="Heads up">Text</x-alert> --}}
@props(['type' => 'info', 'title' => null, 'icon' => null])
@php
    $map = [
        'info' => ['sky', 'info'],
        'success' => ['emerald', 'circle-check'],
        'warning' => ['amber', 'triangle-alert'],
        'danger' => ['rose', 'circle-alert'],
    ];
    [$c, $defaultIcon] = $map[$type] ?? $map['info'];
@endphp
<div {{ $attributes->merge(['class' => "flex gap-3 rounded-2xl border border-{$c}-200 bg-{$c}-50 p-4 text-{$c}-800 dark:border-{$c}-500/30 dark:bg-{$c}-500/10 dark:text-{$c}-200"]) }} role="alert">
    <x-icon :name="$icon ?? $defaultIcon" class="mt-0.5 h-5 w-5" />
    <div class="min-w-0 flex-1 text-sm leading-relaxed">
        @if($title)<p class="mb-0.5 font-bold">{{ $title }}</p>@endif
        {{ $slot }}
    </div>
</div>
