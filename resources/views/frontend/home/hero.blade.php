<section class="relative overflow-hidden">
    {{-- Decorative background --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -left-24 top-0 h-[28rem] w-[28rem] rounded-full bg-primary/20 blur-3xl animate-blob"></div>
        <div class="absolute -right-24 top-24 h-[24rem] w-[24rem] rounded-full bg-secondary/20 blur-3xl animate-blob" style="animation-delay:-4s"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(100,116,139,.15)_1px,transparent_0)] [background-size:24px_24px] [mask-image:linear-gradient(to_bottom,black,transparent)]"></div>
    </div>

    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 pb-16 pt-10 sm:px-6 lg:grid-cols-2 lg:px-8 lg:pb-24 lg:pt-16">
        <div data-aos="fade-up">
            <span class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-3.5 py-1.5 text-xs font-bold text-primary">
                <x-icon name="shield-check" class="h-4 w-4" /> {{ setting('site_tagline') }}
            </span>
            <h1 class="mt-5 text-4xl font-extrabold leading-[1.15] tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white">
                {{ localized_setting('hero_heading') }}
            </h1>
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-600 dark:text-slate-400">{{ localized_setting('hero_text') }}</p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('user.applications.create') }}" class="btn btn-primary btn-lg"><x-icon name="file-pen-line" />{{ __('Apply Now') }}</a>
                <a href="#calculator" class="btn btn-outline btn-lg"><x-icon name="calculator" />{{ __('Calculate Installment') }}</a>
            </div>

            <ul class="mt-8 grid grid-cols-1 gap-3 text-sm font-medium text-slate-600 sm:grid-cols-3 dark:text-slate-400">
                <li class="flex items-center gap-2"><x-icon name="badge-check" class="h-5 w-5 text-emerald-500" />{{ __('Free to apply') }}</li>
                <li class="flex items-center gap-2"><x-icon name="eye" class="h-5 w-5 text-emerald-500" />{{ __('No hidden charges') }}</li>
                <li class="flex items-center gap-2"><x-icon name="lock" class="h-5 w-5 text-emerald-500" />{{ __('Secure NID storage') }}</li>
            </ul>
        </div>

        <div class="relative" data-aos="fade-left">
            @if(setting('hero_banner'))
                <img src="{{ public_asset(setting('hero_banner')) }}" alt="" class="w-full rounded-3xl object-cover shadow-2xl">
            @else
                {{-- Illustrative card (no fake numbers) --}}
                <div class="relative mx-auto max-w-md animate-float">
                    <div class="rounded-3xl bg-gradient-to-br from-primary to-secondary p-6 text-white shadow-2xl shadow-primary/40">
                        <div class="flex items-center justify-between"><x-icon name="landmark" class="h-8 w-8" /><span class="text-sm font-semibold opacity-80">{{ site_name() }}</span></div>
                        <p class="mt-10 text-sm opacity-80">{{ __('Your loan, step by step') }}</p>
                        <div class="mt-4 space-y-3">
                            @foreach(['Apply online', 'NID verification', 'Review & approval', 'Sign agreement', 'Receive money'] as $i => $step)
                                <div class="flex items-center gap-3 rounded-xl bg-white/10 px-3 py-2.5 backdrop-blur">
                                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-white text-xs font-bold text-primary">{{ bn_digits($i + 1) }}</span>
                                    <span class="text-sm font-semibold">{{ __($step) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="absolute -bottom-6 -left-6 hidden items-center gap-3 rounded-2xl bg-white p-4 shadow-xl sm:flex dark:bg-slate-900">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-100 text-emerald-600"><x-icon name="receipt-text" /></span>
                        <div><p class="text-xs text-slate-500">{{ __('Every fee') }}</p><p class="text-sm font-bold text-slate-900 dark:text-white">{{ __('shown before you sign') }}</p></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
