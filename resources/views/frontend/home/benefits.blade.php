<section id="benefits" class="bg-white py-16 dark:bg-slate-900/50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto mb-12 max-w-2xl text-center" data-aos="fade-up">
            <span class="text-sm font-bold uppercase tracking-wider text-primary">{{ __('Why choose us') }}</span>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-white">{{ __('Simple, clear and secure') }}</h2>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['receipt-text', 'emerald', 'Transparent fees', 'Interest, fees and total repayment are shown before you sign. No hidden charges.'],
                ['smartphone', 'sky', 'Fully online', 'Apply, sign and pay installments from your phone.'],
                ['shield-check', 'indigo', 'Data protection', 'NID and personal data are encrypted and stored privately.'],
                ['calendar-check', 'violet', 'Flexible duration', 'Choose a repayment period that suits your income.'],
                ['bell-ring', 'amber', 'Reminders', 'Get SMS and in-app reminders before each installment.'],
                ['headset', 'rose', 'Real support', 'Open a support ticket or chat with us on WhatsApp.'],
            ] as $i => [$icon, $c, $title, $text])
                <div class="card card-pad transition hover:-translate-y-1 hover:shadow-lg" data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 80 }}">
                    <span class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-{{ $c }}-100 text-{{ $c }}-600 dark:bg-{{ $c }}-500/15 dark:text-{{ $c }}-300"><x-icon :name="$icon" /></span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __($title) }}</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ __($text) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
