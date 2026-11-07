{{-- Only verified + published reviews from real customer accounts. Hidden entirely when there are none. --}}
@if($reviews->isNotEmpty())
<section id="reviews" class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto mb-10 max-w-2xl text-center" data-aos="fade-up">
            <span class="text-sm font-bold uppercase tracking-wider text-primary">{{ __('Customer Reviews') }}</span>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-white">{{ __('What our customers say') }}</h2>
        </div>
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach($reviews as $i => $review)
                <figure class="card card-pad flex flex-col {{ $review->is_featured ? 'ring-2 ring-primary/30' : '' }}" data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 80 }}">
                    <div class="flex gap-0.5 text-amber-400" aria-label="{{ $review->rating }}/5">
                        @for($s = 1; $s <= 5; $s++)<x-icon name="star" class="h-4 w-4 {{ $s <= $review->rating ? 'fill-current' : 'text-slate-300 dark:text-slate-700' }}" />@endfor
                    </div>
                    <blockquote class="mt-3 flex-1 text-slate-700 dark:text-slate-300">“{{ $review->comment }}”</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-gradient-to-br from-primary to-secondary font-bold text-white">{{ mb_substr($review->display_name, 0, 1) }}</span>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">{{ $review->display_name }}</p>
                            <p class="flex items-center gap-1 text-xs text-emerald-600"><x-icon name="badge-check" class="h-3.5 w-3.5" />{{ __('Verified customer') }}@if($review->location) · <span class="text-slate-500">{{ $review->location }}</span>@endif</p>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif
