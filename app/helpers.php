<?php

/*
|--------------------------------------------------------------------------
| Global helper functions (autoloaded from composer.json "files")
|--------------------------------------------------------------------------
*/

use App\Services\SettingService;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    /** Website (branding/homepage) setting, e.g. setting('site_name'). */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->website($key, $default);
    }
}

if (! function_exists('sys_setting')) {
    /** System (business rule) setting, e.g. sys_setting('min_loan_amount'). */
    function sys_setting(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->system($key, $default);
    }
}

if (! function_exists('site_name')) {
    function site_name(): string
    {
        return (string) setting('site_name');
    }
}

if (! function_exists('localized_setting')) {
    /** Returns "key_bn" when the locale is Bangla and that value exists. */
    function localized_setting(string $key): ?string
    {
        if (app()->getLocale() === 'bn') {
            $bn = setting($key.'_bn');
            if ($bn) {
                return $bn;
            }
        }

        return setting($key);
    }
}

if (! function_exists('public_asset')) {
    /** URL for a file stored on the public disk (logo, banner...), or null. */
    function public_asset(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}

if (! function_exists('asset_v')) {
    /** asset() with a ?v=filemtime suffix so browsers reload changed CSS/JS. */
    function asset_v(string $path): string
    {
        $file = public_path($path);

        return asset($path).(is_file($file) ? '?v='.filemtime($file) : '');
    }
}

if (! function_exists('bn_digits')) {
    /** Convert 0-9 to Bangla digits when the current locale is bn. */
    function bn_digits(string|int|float $value): string
    {
        $value = (string) $value;
        if (app()->getLocale() !== 'bn') {
            return $value;
        }

        return strtr($value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }
}

if (! function_exists('money')) {
    /** Format an amount in Taka: money(12500) => "৳12,500" (Bangla digits in bn locale). */
    function money(int|float|string|null $amount, int $decimals = 0): string
    {
        $amount = (float) $amount;
        $decimals = $decimals === 0 && fmod($amount, 1.0) !== 0.0 ? 2 : $decimals;

        return '৳'.bn_digits(number_format($amount, $decimals));
    }
}

if (! function_exists('money_plain')) {
    /** Money format without Bangla digits (used in PDFs & CSV). */
    function money_plain(int|float|string|null $amount): string
    {
        return 'BDT '.number_format((float) $amount, 2);
    }
}

if (! function_exists('safe_markdown')) {
    /**
     * Render admin-written text (terms, policies...) as Markdown.
     * Raw HTML is stripped and unsafe links (javascript:) are removed → no XSS.
     */
    function safe_markdown(?string $text): string
    {
        return \Illuminate\Support\Str::markdown((string) $text, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}

if (! function_exists('hex_to_rgb')) {
    function hex_to_rgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        [$r, $g, $b] = sscanf($hex, '%02x%02x%02x') ?: [79, 70, 229];

        return "$r $g $b";
    }
}
