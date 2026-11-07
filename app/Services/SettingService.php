<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Central place for reading/writing dynamic settings.
 *
 *  website settings -> branding & homepage content   (helper: setting('site_name'))
 *  system settings  -> business rules                (helper: sys_setting('min_loan_amount'))
 *
 * Values are cached forever and the cache is cleared on every write,
 * so changing the Website Name in the admin updates every page instantly.
 */
class SettingService
{
    private const WEBSITE_CACHE = 'settings.website';

    private const SYSTEM_CACHE = 'settings.system';

    /** Defaults used when a key has never been saved. */
    public const WEBSITE_DEFAULTS = [
        'site_name' => 'Amar Loan',
        'site_tagline' => 'Simple, transparent digital loans',
        'site_description' => 'Apply for a loan online with clear terms, transparent fees and secure KYC.',
        'logo' => null,
        'favicon' => null,
        'primary_color' => '#4f46e5',
        'secondary_color' => '#0ea5e9',
        'hero_heading' => 'Transparent loans, right from your phone',
        'hero_heading_bn' => 'স্বচ্ছ শর্তে ঋণ, আপনার মোবাইল থেকেই',
        'hero_text' => 'Check your installment, apply online and track every step. All fees are shown before you sign.',
        'hero_text_bn' => 'কিস্তি হিসাব করুন, অনলাইনে আবেদন করুন এবং প্রতিটি ধাপ দেখুন। স্বাক্ষরের আগেই সব চার্জ দেখানো হয়।',
        'hero_banner' => null,
        'contact_phone' => '',
        'support_email' => '',
        'whatsapp_number' => '',
        'office_address' => '',
        'license_info' => '',
        'footer_text' => '',
        'facebook_url' => '',
        'youtube_url' => '',
        'linkedin_url' => '',
        'terms_conditions' => '',
        'privacy_policy' => '',
        'loan_terms_disclosure' => '',
        'seo_title' => '',
        'seo_description' => '',
        'seo_keywords' => '',
        'og_image' => null,
        // Dynamic homepage sections: order + visibility
        'home_sections' => '["calculator","products","process","benefits","reviews","faq","contact"]',
    ];

    public const SYSTEM_DEFAULTS = [
        'min_loan_amount' => '5000',
        'max_loan_amount' => '100000',
        'loan_amount_presets' => '5000,10000,20000,50000,100000',
        'default_interest_rate' => '12',
        'min_monthly_income' => '10000',
        'max_income_multiplier' => '5',          // eligible amount ≤ income × this
        'min_age' => '18',
        'max_open_applications' => '1',
        'require_nid_before_apply' => '0',
        'require_selfie' => '0',
        'reminder_days_before' => '3',
        'lender_legal_name' => '',               // licensed institution receiving fees
        'lender_license_number' => '',
        'maintenance_mode' => '0',
        'maintenance_message' => 'We are upgrading our system. Please check back soon.',
        'registration_enabled' => '1',
    ];

    public function website(string $key, mixed $default = null): mixed
    {
        $all = $this->allWebsite();

        return array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== ''
            ? $all[$key]
            : ($default ?? self::WEBSITE_DEFAULTS[$key] ?? null);
    }

    public function system(string $key, mixed $default = null): mixed
    {
        $all = $this->allSystem();

        return array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== ''
            ? $all[$key]
            : ($default ?? self::SYSTEM_DEFAULTS[$key] ?? null);
    }

    public function allWebsite(): array
    {
        return $this->load(self::WEBSITE_CACHE, WebsiteSetting::class);
    }

    public function allSystem(): array
    {
        return $this->load(self::SYSTEM_CACHE, SystemSetting::class);
    }

    /** @param array<string, mixed> $values */
    public function setWebsite(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            WebsiteSetting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }
        Cache::forget(self::WEBSITE_CACHE);
    }

    /** @param array<string, mixed> $values */
    public function setSystem(array $values, string $group = 'loan'): void
    {
        foreach ($values as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], ['value' => is_bool($value) ? (int) $value : $value, 'group' => $group]);
        }
        Cache::forget(self::SYSTEM_CACHE);
    }

    public function flush(): void
    {
        Cache::forget(self::WEBSITE_CACHE);
        Cache::forget(self::SYSTEM_CACHE);
    }

    private function load(string $cacheKey, string $model): array
    {
        try {
            return Cache::rememberForever($cacheKey, fn () => $model::query()->pluck('value', 'key')->all());
        } catch (Throwable) {
            // Database not migrated yet (e.g. during installation) – fall back to defaults.
            return [];
        }
    }
}
