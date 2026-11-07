<section id="contact" class="scroll-mt-20 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-secondary p-8 text-white shadow-2xl shadow-primary/30 sm:p-12" data-aos="zoom-in">
            <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-white/10"></div>
            <div class="relative grid gap-10 lg:grid-cols-2 lg:items-center">
                <div>
                    <h2 class="text-3xl font-extrabold sm:text-4xl">{{ __('Need help? Talk to us') }}</h2>
                    <p class="mt-3 text-white/85">{{ __('Our support team will help you with your application, documents or payments.') }}</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('user.applications.create') }}" class="btn btn-lg bg-white text-primary hover:bg-slate-50"><x-icon name="file-pen-line" />{{ __('Apply Now') }}</a>
                        @if(setting('whatsapp_number'))
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp_number')) }}" target="_blank" rel="noopener" class="btn btn-lg glass text-white hover:bg-white/20"><x-icon name="message-circle" />WhatsApp</a>
                        @endif
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach([
                        ['phone', 'Phone', setting('contact_phone'), 'tel:'.setting('contact_phone')],
                        ['mail', 'Email', setting('support_email'), 'mailto:'.setting('support_email')],
                        ['message-circle', 'WhatsApp', setting('whatsapp_number'), 'https://wa.me/'.preg_replace('/\D/', '', (string) setting('whatsapp_number'))],
                        ['map-pin', 'Office', setting('office_address'), null],
                    ] as [$icon, $label, $value, $href])
                        @if($value)
                            <{{ $href ? 'a' : 'div' }} @if($href) href="{{ $href }}" @endif class="glass flex items-start gap-3 rounded-2xl p-4 transition hover:bg-white/20">
                                <x-icon :name="$icon" class="mt-0.5" />
                                <div class="min-w-0"><p class="text-xs text-white/70">{{ __($label) }}</p><p class="break-words font-semibold">{{ $value }}</p></div>
                            </{{ $href ? 'a' : 'div' }}>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
