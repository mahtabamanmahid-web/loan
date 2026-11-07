<section id="calculator" class="scroll-mt-20 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto mb-10 max-w-2xl text-center" data-aos="fade-up">
            <span class="text-sm font-bold uppercase tracking-wider text-primary">{{ __('Loan Calculator') }}</span>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-white">{{ __('Know your installment before you apply') }}</h2>
            <p class="mt-3 text-slate-600 dark:text-slate-400">{{ __('Move the slider or choose an amount. Everything updates instantly.') }}</p>
        </div>
        <div data-aos="fade-up">@include('frontend.partials.calculator')</div>
    </div>
</section>
