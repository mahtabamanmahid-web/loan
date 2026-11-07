@if($products->isNotEmpty())
<section id="products" class="scroll-mt-20 bg-white py-16 dark:bg-slate-900/50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto mb-10 max-w-2xl text-center" data-aos="fade-up">
            <span class="text-sm font-bold uppercase tracking-wider text-primary">{{ __('Loan Packages') }}</span>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-white">{{ __('Choose the loan that fits you') }}</h2>
        </div>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($products as $i => $p)
                <div class="card card-pad group flex flex-col transition hover:-translate-y-1 hover:shadow-xl" data-aos="fade-up" data-aos-delay="{{ $i * 80 }}">
                    <span class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg shadow-primary/25 transition group-hover:scale-110"><x-icon :name="$p->icon" /></span>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $p->displayName() }}</h3>
                    <p class="mt-2 flex-1 text-sm text-slate-600 dark:text-slate-400">{{ $p->displayDescription() }}</p>
                    <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-xs text-slate-500">{{ __('Amount') }}</dt><dd class="font-bold">{{ money(max($p->min_amount, sys_setting('min_loan_amount'))) }} – {{ money(min($p->max_amount, sys_setting('max_loan_amount'))) }}</dd></div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-xs text-slate-500">{{ __('Duration') }}</dt><dd class="font-bold">{{ bn_digits($p->activeTerms->min('months')) }}–{{ bn_digits($p->activeTerms->max('months')) }} {{ __('months') }}</dd></div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-xs text-slate-500">{{ __('Interest (flat/yr)') }}</dt><dd class="font-bold">{{ bn_digits(rtrim(rtrim(number_format($p->activeTerms->min('interest_rate'), 2), '0'), '.')) }}% {{ __('from') }}</dd></div>
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800"><dt class="text-xs text-slate-500">{{ __('Processing fee') }}</dt><dd class="font-bold">{{ bn_digits($p->feeLabel()) }}</dd></div>
                    </dl>
                    <a href="{{ route('user.applications.create', ['product' => $p->id]) }}" class="btn btn-outline mt-5 w-full group-hover:border-primary group-hover:bg-primary group-hover:text-white">{{ __('Apply for this loan') }}<x-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
            @endforeach
        </div>
        <p class="mt-6 text-center text-sm text-slate-500"><a href="{{ route('loan-terms') }}" class="font-semibold text-primary hover:underline">{{ __('See full fee & terms disclosure') }} →</a></p>
    </div>
</section>
@endif
