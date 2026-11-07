{{--
    Interactive loan calculator (Alpine component "loanCalculator" in public/js/app.js).
    Variables: $calculatorProducts, $presets
    The maths matches App\Services\LoanCalculator exactly.
--}}
@if(empty($calculatorProducts))
    <x-empty icon="calculator" :title="__('No loan packages available right now')" />
@else
<div x-data="loanCalculator({ products: @js($calculatorProducts), presets: @js($presets) })" class="grid gap-6 lg:grid-cols-5">
    {{-- Inputs --}}
    <div class="card card-pad space-y-6 lg:col-span-3">
        {{-- Package --}}
        <div>
            <p class="label">{{ __('1. Choose a loan package') }}</p>
            <div class="grid gap-2 sm:grid-cols-2">
                <template x-for="p in products" :key="p.id">
                    <button type="button" @click="productId = p.id"
                            :class="productId == p.id ? 'border-primary bg-primary/5 ring-4 ring-primary/10' : 'border-slate-200 hover:border-slate-300 dark:border-slate-700'"
                            class="flex items-center gap-3 rounded-xl border-2 p-3 text-left transition">
                        <span class="grid h-10 w-10 place-items-center rounded-lg bg-primary/10 text-primary"><i :data-lucide="p.icon" class="h-5 w-5"></i></span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-100" x-text="p.name"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Amount --}}
        <div>
            <div class="mb-2 flex items-end justify-between gap-3">
                <label for="calc-amount" class="label mb-0">{{ __('2. Loan amount') }}</label>
                <span class="text-2xl font-extrabold text-primary" x-text="money(amount)"></span>
            </div>
            <input type="range" class="range" :min="min" :max="max" step="500" x-model.number="amount" aria-label="{{ __('Loan amount') }}"
                   :style="`background: linear-gradient(to right, rgb(var(--brand-primary)) ${progress}%, transparent ${progress}%)`">
            <div class="mt-1 flex justify-between text-xs text-slate-500"><span x-text="money(min)"></span><span x-text="money(max)"></span></div>
            <div class="mt-3 flex flex-wrap gap-2">
                <template x-for="p in visiblePresets" :key="p">
                    <button type="button" @click="amount = p" :class="amount == p ? 'bg-primary text-white border-primary' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700'"
                            class="rounded-lg border px-3 py-1.5 text-sm font-semibold transition" x-text="money(p)"></button>
                </template>
            </div>
            <div class="mt-3">
                <label for="calc-amount" class="hint mb-1 block">{{ __('Or type a custom amount') }}</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 font-bold text-slate-400">৳</span>
                    <input id="calc-amount" type="number" inputmode="numeric" class="input pl-9" x-model.number="amount" @change="clampAmount()" :min="min" :max="max">
                </div>
                <p class="error-text" x-show="!amountValid" x-cloak>{{ __('Amount must be between') }} <span x-text="money(min)"></span> – <span x-text="money(max)"></span></p>
            </div>
        </div>

        {{-- Duration --}}
        <div>
            <p class="label">{{ __('3. Loan duration') }}</p>
            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                <template x-for="t in terms" :key="t.id">
                    <button type="button" @click="termId = t.id"
                            :class="termId == t.id ? 'border-primary bg-primary text-white shadow-lg shadow-primary/25' : 'border-slate-200 bg-white hover:border-primary/50 dark:border-slate-700 dark:bg-slate-800'"
                            class="rounded-xl border-2 px-2 py-3 text-center transition">
                        <span class="block text-lg font-extrabold" x-text="digits(t.months)"></span>
                        <span class="block text-xs opacity-80">{{ __('months') }} · <span x-text="digits(t.interest_rate) + '%'"></span></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- Result --}}
    <div class="lg:col-span-2">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-secondary p-6 text-white shadow-2xl shadow-primary/30 lg:sticky lg:top-24">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="relative">
                <p class="text-sm font-medium text-white/80" x-text="result.frequency === 'weekly' ? @js(__('Weekly installment')) : @js(__('Monthly installment'))"></p>
                <p class="mt-1 text-4xl font-extrabold tracking-tight" x-text="money(result.installment)"></p>
                <p class="mt-1 text-sm text-white/80"><span x-text="digits(result.count)"></span> {{ __('installments') }}</p>

                <dl class="mt-6 space-y-2.5 rounded-2xl bg-white/10 p-4 text-sm backdrop-blur">
                    <div class="flex justify-between"><dt class="text-white/80">{{ __('Loan Amount') }}</dt><dd class="font-semibold" x-text="money(result.amount)"></dd></div>
                    <div class="flex justify-between"><dt class="text-white/80">{{ __('Duration') }}</dt><dd class="font-semibold"><span x-text="digits(result.months)"></span> {{ __('months') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-white/80">{{ __('Interest Rate (flat, yearly)') }}</dt><dd class="font-semibold" x-text="digits(result.rate) + '%'"></dd></div>
                    <div class="flex justify-between"><dt class="text-white/80">{{ __('Total Interest') }}</dt><dd class="font-semibold" x-text="money(result.totalInterest)"></dd></div>
                    <div class="flex justify-between"><dt class="text-white/80">{{ __('Processing Fee') }}</dt><dd class="font-semibold" x-text="money(result.fee)"></dd></div>
                    <div class="flex justify-between border-t border-white/20 pt-2.5 text-base"><dt class="font-semibold">{{ __('Total Repayment') }}</dt><dd class="font-extrabold" x-text="money(result.totalPayable)"></dd></div>
                    <div class="flex justify-between text-xs" x-show="result.collection === 'deduct' && result.fee > 0"><dt class="text-white/80">{{ __('You receive (after fee)') }}</dt><dd class="font-semibold" x-text="money(result.netDisbursement)"></dd></div>
                </dl>

                <a :href="'{{ route('user.applications.create') }}?product=' + productId + '&term=' + termId + '&amount=' + amount"
                   class="btn btn-lg mt-6 w-full bg-white text-primary shadow-xl hover:bg-slate-50"><x-icon name="file-pen-line" />{{ __('Apply Now') }}</a>
                <p class="mt-3 text-center text-[11px] leading-snug text-white/70">{{ __('This is an estimate. Final terms are shown in your agreement before you sign. Approval is not guaranteed.') }}</p>
            </div>
        </div>
    </div>
</div>
@endif
