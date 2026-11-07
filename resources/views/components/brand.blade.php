{{-- Website logo + name (both editable in Admin → Website). --}}
@props(['light' => false])
<a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="{{ site_name() }}">
    @if(setting('logo'))
        <img src="{{ public_asset(setting('logo')) }}" alt="{{ site_name() }}" class="h-9 w-auto max-w-[140px] object-contain">
    @else
        <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg shadow-primary/30">
            <x-icon name="landmark" class="h-5 w-5" />
        </span>
    @endif
    <span class="text-lg font-extrabold tracking-tight {{ $light ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ site_name() }}</span>
</a>
