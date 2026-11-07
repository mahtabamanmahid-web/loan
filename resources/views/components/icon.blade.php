{{-- Lucide icon. Usage: <x-icon name="wallet" class="h-5 w-5" /> (names: https://lucide.dev/icons) --}}
@props(['name'])
<i data-lucide="{{ $name }}" {{ $attributes->merge(['class' => 'h-5 w-5 shrink-0']) }} aria-hidden="true"></i>
