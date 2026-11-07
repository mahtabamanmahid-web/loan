@extends('layouts.admin')
@section('title', __('Website Customization'))

@php
    $enabled = json_decode($s['home_sections'] ?? '[]', true) ?: [];
    // enabled first (in saved order), then the rest
    $ordered = array_values(array_unique(array_merge($enabled, $allSections)));
    $sectionLabels = ['calculator' => 'Loan Calculator', 'products' => 'Loan Packages', 'process' => 'Loan Process Steps', 'benefits' => 'Loan Benefits', 'reviews' => 'Customer Reviews', 'faq' => 'FAQ', 'contact' => 'Contact Information'];
@endphp

@section('content')
<x-page-header :title="__('Website Customization')" :subtitle="__('Changes apply everywhere immediately: header, footer, title, dashboards, emails.')" />

<form method="POST" action="{{ route('admin.settings.website.update') }}" enctype="multipart/form-data" x-data="{ tab: 'brand' }">
    @csrf @method('PUT')

    <div class="mb-5 flex gap-2 overflow-x-auto">
        @foreach(['brand' => 'Branding', 'home' => 'Homepage', 'contact' => 'Contact & Social', 'legal' => 'Legal Pages', 'seo' => 'SEO'] as $k => $l)
            <button type="button" @click="tab = '{{ $k }}'" :class="tab === '{{ $k }}' ? 'bg-primary text-white' : 'bg-white text-slate-600 dark:bg-slate-900 dark:text-slate-300'" class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold">{{ __($l) }}</button>
        @endforeach
    </div>

    {{-- Branding --}}
    <div x-show="tab === 'brand'" class="grid gap-6 lg:grid-cols-2">
        <div class="card card-pad space-y-5">
            <x-input name="site_name" :label="__('Website Name')" :value="$s['site_name']" required maxlength="60" />
            <x-input name="site_tagline" :label="__('Tagline')" :value="$s['site_tagline']" />
            <x-textarea name="site_description" :label="__('Website Description')" :value="$s['site_description']" rows="3" />
            <div class="grid grid-cols-2 gap-4">
                @foreach(['primary_color' => __('Primary Color'), 'secondary_color' => __('Secondary Color')] as $key => $label)
                    <div class="field" x-data="{ c: '{{ old($key, $s[$key]) }}' }">
                        <label class="label" for="{{ $key }}">{{ $label }}</label>
                        <div class="flex gap-2"><input type="color" x-model="c" class="h-12 w-14 cursor-pointer rounded-xl border border-slate-300"><input id="{{ $key }}" name="{{ $key }}" x-model="c" class="input font-mono" pattern="#[0-9a-fA-F]{6}" required></div>
                        @error($key)<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
            <x-input name="footer_text" :label="__('Footer Text')" :value="$s['footer_text']" />
            <x-input name="license_info" :label="__('License info in footer')" :value="$s['license_info']" />
        </div>
        <div class="card card-pad space-y-5">
            @foreach(['logo' => __('Website Logo'), 'favicon' => __('Favicon'), 'og_image' => __('Social share image')] as $key => $label)
                <div class="field">
                    <label class="label">{{ $label }}</label>
                    <div class="flex items-center gap-4">
                        <div class="grid h-16 w-28 place-items-center overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                            @if($s[$key])<img src="{{ public_asset($s[$key]) }}" class="max-h-full max-w-full object-contain" alt="">@else<x-icon name="image" class="text-slate-400" />@endif
                        </div>
                        <div class="flex-1 space-y-1">
                            <input type="file" name="{{ $key }}" accept="{{ $key === 'favicon' ? '.png,.ico,.webp' : 'image/png,image/jpeg,image/webp' }}" class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:font-semibold file:text-primary">
                            @if($s[$key])<label class="flex items-center gap-2 text-xs text-rose-600"><input type="checkbox" name="remove_{{ $key }}" value="1" class="checkbox h-4 w-4">{{ __('Remove') }}</label>@endif
                        </div>
                    </div>
                    @error($key)<p class="error-text">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
    </div>

    {{-- Homepage --}}
    <div x-show="tab === 'home'" x-cloak class="grid gap-6 lg:grid-cols-2">
        <div class="card card-pad space-y-5">
            <x-input name="hero_heading" :label="__('Homepage Heading (English)')" :value="$s['hero_heading']" />
            <x-input name="hero_heading_bn" :label="__('Homepage Heading (Bangla)')" :value="$s['hero_heading_bn']" />
            <x-textarea name="hero_text" :label="__('Homepage Text (English)')" :value="$s['hero_text']" rows="3" />
            <x-textarea name="hero_text_bn" :label="__('Homepage Text (Bangla)')" :value="$s['hero_text_bn']" rows="3" />
            <div class="field">
                <label class="label">{{ __('Homepage Banner') }}</label>
                @if($s['hero_banner'])<img src="{{ public_asset($s['hero_banner']) }}" class="mb-2 h-32 rounded-xl object-cover" alt="">@endif
                <input type="file" name="hero_banner" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:font-semibold file:text-primary">
                @if($s['hero_banner'])<label class="mt-1 flex items-center gap-2 text-xs text-rose-600"><input type="checkbox" name="remove_hero_banner" value="1" class="checkbox h-4 w-4">{{ __('Remove') }}</label>@endif
            </div>
        </div>
        <div class="card card-pad" x-data="{ items: @js(collect($ordered)->map(fn ($k) => ['key' => $k, 'on' => in_array($k, $enabled, true)])->all()) }">
            <h3 class="font-bold">{{ __('Dynamic Homepage Sections') }}</h3>
            <p class="hint mb-4">{{ __('Turn sections on/off and change their order with the arrows.') }}</p>
            <template x-for="(item, i) in items" :key="item.key">
                <div class="mb-2 flex items-center gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                    <input type="checkbox" class="checkbox" x-model="item.on">
                    <span class="flex-1 text-sm font-semibold" x-text="@js(collect($sectionLabels)->map(fn ($l) => __($l)))[item.key]"></span>
                    <button type="button" class="btn-ghost rounded-lg p-1.5" @click="if (i > 0) { const t = items[i - 1]; items[i - 1] = items[i]; items[i] = t; }" aria-label="Up"><i data-lucide="chevron-up" class="h-4 w-4"></i></button>
                    <button type="button" class="btn-ghost rounded-lg p-1.5" @click="if (i < items.length - 1) { const t = items[i + 1]; items[i + 1] = items[i]; items[i] = t; }" aria-label="Down"><i data-lucide="chevron-down" class="h-4 w-4"></i></button>
                </div>
            </template>
            <template x-for="item in items.filter(x => x.on)" :key="'h' + item.key"><input type="hidden" name="sections[]" :value="item.key"></template>
        </div>
    </div>

    {{-- Contact --}}
    <div x-show="tab === 'contact'" x-cloak class="card card-pad grid gap-5 sm:grid-cols-2">
        <x-input name="contact_phone" :label="__('Contact Number')" icon="phone" :value="$s['contact_phone']" />
        <x-input name="support_email" type="email" :label="__('Support Email')" icon="mail" :value="$s['support_email']" />
        <x-input name="whatsapp_number" :label="__('WhatsApp Number')" icon="message-circle" :value="$s['whatsapp_number']" :hint="__('With country code, e.g. 8801712345678')" />
        <x-input name="office_address" :label="__('Office Address')" icon="map-pin" :value="$s['office_address']" />
        <x-input name="facebook_url" type="url" :label="'Facebook URL'" icon="facebook" :value="$s['facebook_url']" />
        <x-input name="youtube_url" type="url" :label="'YouTube URL'" icon="youtube" :value="$s['youtube_url']" />
        <x-input name="linkedin_url" type="url" :label="'LinkedIn URL'" icon="linkedin" :value="$s['linkedin_url']" />
    </div>

    {{-- Legal --}}
    <div x-show="tab === 'legal'" x-cloak class="card card-pad space-y-5">
        <x-alert type="info">{{ __('Write in plain text or Markdown (# Heading, - list, **bold**). HTML is not allowed for security.') }}</x-alert>
        <x-textarea name="terms_conditions" :label="__('Terms & Conditions')" :value="$s['terms_conditions']" rows="10" />
        <x-textarea name="privacy_policy" :label="__('Privacy Policy')" :value="$s['privacy_policy']" rows="10" />
        <x-textarea name="loan_terms_disclosure" :label="__('Additional loan terms (fee disclosure page & agreements)')" :value="$s['loan_terms_disclosure']" rows="8" />
    </div>

    {{-- SEO --}}
    <div x-show="tab === 'seo'" x-cloak class="card card-pad max-w-2xl space-y-5">
        <x-input name="seo_title" :label="__('SEO title')" :value="$s['seo_title']" maxlength="70" :hint="__('Shown in Google results and the browser tab (max 70).')" />
        <x-textarea name="seo_description" :label="__('Meta description')" :value="$s['seo_description']" rows="3" maxlength="170" />
        <x-input name="seo_keywords" :label="__('Meta keywords')" :value="$s['seo_keywords']" />
    </div>

    <div class="sticky bottom-4 mt-6"><button class="btn btn-primary btn-lg shadow-2xl"><x-icon name="save" />{{ __('Save Website Settings') }}</button></div>
</form>
@endsection
