<section id="process" class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto mb-12 max-w-2xl text-center" data-aos="fade-up">
            <span class="text-sm font-bold uppercase tracking-wider text-primary">{{ __('How it works') }}</span>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-white">{{ __('Get a loan in 5 simple steps') }}</h2>
        </div>
        <ol class="relative grid gap-6 md:grid-cols-5">
            <div class="absolute left-0 right-0 top-8 hidden h-0.5 bg-gradient-to-r from-primary/20 via-secondary/40 to-primary/20 md:block"></div>
            @foreach([
                ['user-plus', 'Open account', 'Register with your mobile number and verify with OTP.'],
                ['id-card', 'Verify NID', 'Upload clear photos of both sides of your NID.'],
                ['file-pen-line', 'Apply', 'Choose amount and duration, fill in your details.'],
                ['file-signature', 'Review & sign', 'After approval, read all terms and accept the agreement.'],
                ['banknote', 'Receive money', 'Money is sent to your bank or mobile wallet.'],
            ] as $i => [$icon, $title, $text])
                <li class="relative text-center" data-aos="fade-up" data-aos-delay="{{ $i * 100 }}">
                    <span class="relative mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-white text-primary shadow-lg ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                        <x-icon :name="$icon" class="h-7 w-7" />
                        <span class="absolute -right-2 -top-2 grid h-6 w-6 place-items-center rounded-full bg-gradient-to-br from-primary to-secondary text-xs font-bold text-white">{{ bn_digits($i + 1) }}</span>
                    </span>
                    <h3 class="mt-4 font-bold text-slate-900 dark:text-white">{{ __($title) }}</h3>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __($text) }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
