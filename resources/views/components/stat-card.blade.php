{{-- Dashboard number tile. Usage: <x-stat-card icon="users" label="Total Users" :value="120" color="indigo" /> --}}
@props(['icon', 'label', 'value', 'color' => 'indigo', 'hint' => null, 'href' => null])
@php($tag = $href ? 'a' : 'div')
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'card card-pad group relative overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lg']) }}>
    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-{{ $color }}-500/10 transition group-hover:scale-125"></div>
    <div class="relative flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="mt-2 truncate text-2xl font-bold text-slate-900 dark:text-white">{{ $value }}</p>
            @if($hint)<p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>@endif
        </div>
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-{{ $color }}-100 text-{{ $color }}-600 dark:bg-{{ $color }}-500/15 dark:text-{{ $color }}-300">
            <x-icon :name="$icon" />
        </span>
    </div>
</{{ $tag }}>
