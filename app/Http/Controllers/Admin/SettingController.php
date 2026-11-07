<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Loan rules, website customization (branding, homepage, SEO, legal) and system settings. */
class SettingController extends Controller
{
    public const HOME_SECTIONS = ['calculator', 'products', 'process', 'benefits', 'reviews', 'faq', 'contact'];

    public function __construct(private SettingService $settings) {}

    /* ---------------- Loan settings ---------------- */

    public function loan(): View
    {
        return view('admin.settings.loan', ['s' => $this->settings->allSystem() + SettingService::SYSTEM_DEFAULTS]);
    }

    public function updateLoan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'min_loan_amount' => ['required', 'numeric', 'min:1'],
            'max_loan_amount' => ['required', 'numeric', 'gt:min_loan_amount'],
            'loan_amount_presets' => ['nullable', 'string', 'max:200', 'regex:/^[\d,\s]*$/'],
            'default_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_monthly_income' => ['required', 'numeric', 'min:0'],
            'max_income_multiplier' => ['required', 'numeric', 'min:0.5', 'max:50'],
            'min_age' => ['required', 'integer', 'min:18', 'max:100'],
            'max_open_applications' => ['required', 'integer', 'min:1', 'max:10'],
            'reminder_days_before' => ['required', 'integer', 'min:0', 'max:30'],
            'lender_legal_name' => ['nullable', 'string', 'max:150'],
            'lender_license_number' => ['nullable', 'string', 'max:100'],
        ]);
        $data['require_nid_before_apply'] = $request->boolean('require_nid_before_apply');
        $data['require_selfie'] = $request->boolean('require_selfie');

        $this->settings->setSystem($data, 'loan');
        ActivityLogger::log('settings.loan', null, 'Loan settings updated', $data);

        return back()->with('success', __('Settings saved.'));
    }

    /* ---------------- Website customization ---------------- */

    public function website(): View
    {
        return view('admin.settings.website', [
            's' => $this->settings->allWebsite() + SettingService::WEBSITE_DEFAULTS,
            'allSections' => self::HOME_SECTIONS,
        ]);
    }

    public function updateWebsite(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:60'],
            'site_tagline' => ['nullable', 'string', 'max:120'],
            'site_description' => ['nullable', 'string', 'max:300'],
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'hero_heading' => ['nullable', 'string', 'max:150'],
            'hero_heading_bn' => ['nullable', 'string', 'max:150'],
            'hero_text' => ['nullable', 'string', 'max:400'],
            'hero_text_bn' => ['nullable', 'string', 'max:400'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'support_email' => ['nullable', 'email', 'max:150'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'office_address' => ['nullable', 'string', 'max:300'],
            'license_info' => ['nullable', 'string', 'max:200'],
            'footer_text' => ['nullable', 'string', 'max:300'],
            'facebook_url' => ['nullable', 'url:https', 'max:255'],
            'youtube_url' => ['nullable', 'url:https', 'max:255'],
            'linkedin_url' => ['nullable', 'url:https', 'max:255'],
            'terms_conditions' => ['nullable', 'string', 'max:50000'],
            'privacy_policy' => ['nullable', 'string', 'max:50000'],
            'loan_terms_disclosure' => ['nullable', 'string', 'max:50000'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:170'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['in:'.implode(',', self::HOME_SECTIONS)],
            // Uploads – images only. SVG is NOT allowed (it can contain scripts).
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,webp', 'max:256'],
            'hero_banner' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:3072'],
            'og_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
        ]);

        $values = collect($data)->except(['sections', 'logo', 'favicon', 'hero_banner', 'og_image'])->all();
        $values['home_sections'] = json_encode(array_values($request->input('sections', [])));

        foreach (['logo', 'favicon', 'hero_banner', 'og_image'] as $file) {
            if ($request->boolean("remove_$file")) {
                $this->deleteFile(setting($file));
                $values[$file] = null;
            }
            if ($request->hasFile($file)) {
                $this->deleteFile(setting($file));
                $values[$file] = $request->file($file)->store('branding', 'public');
            }
        }

        $this->settings->setWebsite($values, 'website');
        ActivityLogger::log('settings.website', null, 'Website settings updated', ['keys' => array_keys($values)]);

        return back()->with('success', __('Website updated. Changes are live now.'));
    }

    /* ---------------- System / maintenance ---------------- */

    public function system(): View
    {
        return view('admin.settings.system', ['s' => $this->settings->allSystem() + SettingService::SYSTEM_DEFAULTS]);
    }

    public function updateSystem(Request $request): RedirectResponse
    {
        $data = $request->validate(['maintenance_message' => ['nullable', 'string', 'max:300']]);
        $data['maintenance_mode'] = $request->boolean('maintenance_mode');
        $data['registration_enabled'] = $request->boolean('registration_enabled');
        $data['otp_enabled'] = $request->boolean('otp_enabled');

        $this->settings->setSystem($data, 'system');
        ActivityLogger::log('settings.system', null, 'System settings updated', $data);

        return back()->with('success', __('Settings saved.'));
    }

    private function deleteFile(?string $path): void
    {
        if ($path && str_starts_with($path, 'branding/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
