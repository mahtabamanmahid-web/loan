@if($faqs->isNotEmpty())
<section id="faq" class="scroll-mt-20 bg-white py-16 dark:bg-slate-900/50">
    <div class="mx-auto max-w-3xl px-4 sm:px-6">
        <div class="mb-10 text-center" data-aos="fade-up">
            <span class="text-sm font-bold uppercase tracking-wider text-primary">{{ __('FAQ') }}</span>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-white">{{ __('Frequently asked questions') }}</h2>
        </div>
        <div class="space-y-3" x-data="{ open: 0 }">
            @foreach($faqs as $i => $faq)
                <div class="card overflow-hidden" data-aos="fade-up">
                    <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 p-5 text-left" :aria-expanded="open === {{ $i }}">
                        <span class="font-bold text-slate-900 dark:text-white">{{ $faq->displayQuestion() }}</span>
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-100 transition dark:bg-slate-800" :class="open === {{ $i }} && 'rotate-45 bg-primary text-white'"><x-icon name="plus" class="h-4 w-4" /></span>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse @if($i !== 0) x-cloak @endif>
                        <p class="px-5 pb-5 leading-relaxed text-slate-600 dark:text-slate-400">{{ $faq->displayAnswer() }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
