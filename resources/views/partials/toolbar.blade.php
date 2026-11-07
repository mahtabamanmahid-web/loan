{{-- Language switch + dark mode toggle (used in every header). --}}
<div class="flex items-center gap-1">
    <a href="{{ route('locale.switch', app()->getLocale() === 'bn' ? 'en' : 'bn') }}"
       class="flex h-10 items-center gap-1.5 rounded-xl px-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
       title="{{ __('Change language') }}">
        <x-icon name="languages" class="h-[18px] w-[18px]" />
        <span>{{ app()->getLocale() === 'bn' ? 'EN' : 'বাং' }}</span>
    </a>
    <button type="button" @click="$store.theme.toggle()"
            class="grid h-10 w-10 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
            :aria-label="$store.theme.dark ? @js(__('Light mode')) : @js(__('Dark mode'))">
        <span x-show="!$store.theme.dark"><x-icon name="moon" class="h-[18px] w-[18px]" /></span>
        <span x-show="$store.theme.dark" x-cloak><x-icon name="sun" class="h-[18px] w-[18px]" /></span>
    </button>
</div>
