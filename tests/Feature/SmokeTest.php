<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Faq;
use App\Models\Review;
use App\Models\SupportTicket;
use App\Services\SettingService;
use Tests\TestCase;

/**
 * Renders every page to catch Blade / route errors, and checks the
 * mobile-first essentials (viewport meta, bottom navigation).
 */
class SmokeTest extends TestCase
{
    public function test_public_pages_render(): void
    {
        foreach (['/', '/calculator', '/loan-terms', '/terms', '/privacy', '/login', '/register', '/forgot-password', '/admin/login'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/api/v1/loan-products')->assertOk()->assertJsonStructure(['data' => [['id', 'name', 'terms']]]);
    }

    public function test_mobile_first_layout_essentials(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('name="viewport" content="width=device-width, initial-scale=1', $html);
        $this->assertStringContainsString('md:hidden', $html);           // mobile bottom navigation
        $this->assertStringContainsString('lang="bn"', $html);           // Bangla by default

        $this->actingAs($this->customer());
        $this->assertStringContainsString('lg:hidden', $this->get('/dashboard')->getContent()); // customer bottom nav
    }

    public function test_dynamic_site_name_appears_everywhere(): void
    {
        app(SettingService::class)->setWebsite(['site_name' => 'Taka Sheba']);

        foreach (['/', '/login', '/register', '/admin/login'] as $url) {
            $this->get($url)->assertSee('Taka Sheba');
        }
        $this->actingAs($this->customer())->get('/dashboard')->assertSee('Taka Sheba');
        $this->actingAs($this->admin())->get('/admin')->assertSee('Taka Sheba');
    }

    public function test_language_switch(): void
    {
        $this->get('/locale/en')->assertRedirect();
        $this->get('/')->assertSee('lang="en"', false);
    }

    public function test_404_page(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('404');
    }

    public function test_maintenance_mode_blocks_public_but_not_admin(): void
    {
        app(SettingService::class)->setSystem(['maintenance_mode' => 1, 'maintenance_message' => 'Back at 5pm']);

        $this->get('/')->assertStatus(503)->assertSee('Back at 5pm');
        $this->get('/admin/login')->assertOk();
        $this->actingAs($this->admin())->get('/')->assertOk();
    }

    public function test_customer_pages_render(): void
    {
        $loan = $this->activeLoan();
        $user = $loan->user;
        SupportTicket::create(['ticket_number' => 'TKT-1', 'user_id' => $user->id, 'subject' => 'Help', 'category' => 'general'])
            ->replies()->create(['user_id' => $user->id, 'message' => 'Hello there']);
        Announcement::create(['title' => 'Eid holiday', 'body' => 'Office closed', 'audience' => 'customers']);
        $this->actingAs($user);

        foreach ([
            route('user.dashboard'), route('user.applications.index'), route('user.applications.show', $loan->application),
            route('user.applications.agreement', $loan->application), route('user.loans.index'), route('user.loans.show', $loan),
            route('user.kyc.show'), route('user.transactions.index'), route('user.profile.edit'), route('user.profile.logins'),
            route('user.notifications.index'), route('user.tickets.index'), route('user.tickets.create'), route('user.tickets.show', SupportTicket::first()),
        ] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get(route('user.dashboard'))->assertSee('Eid holiday');
        $this->get(route('user.applications.agreement.pdf', $loan->application))->assertOk();
    }

    public function test_admin_pages_render(): void
    {
        $loan = $this->activeLoan();
        $this->submittedApplication();
        $doc = $loan->user->latestDocument;
        Review::create(['user_id' => $loan->user_id, 'display_name' => 'R', 'rating' => 5, 'comment' => 'Very good service']);
        $ticket = SupportTicket::create(['ticket_number' => 'TKT-2', 'user_id' => $loan->user_id, 'subject' => 'Q', 'category' => 'loan']);
        $this->actingAs($this->admin());

        foreach ([
            'admin.dashboard', 'admin.users.index', 'admin.kyc.index', 'admin.applications.index', 'admin.loans.index',
            'admin.products.index', 'admin.products.create', 'admin.payments.index', 'admin.payments.reports', 'admin.settings.loan',
            'admin.settings.website', 'admin.settings.system', 'admin.reviews.index', 'admin.reviews.create', 'admin.faqs.index',
            'admin.faqs.create', 'admin.announcements.index', 'admin.announcements.create', 'admin.tickets.index', 'admin.admins.index',
            'admin.admins.create', 'admin.roles.index', 'admin.logs.activity', 'admin.logs.logins', 'admin.logs.payments', 'admin.backups.index',
        ] as $name) {
            $this->get(route($name))->assertOk();
        }

        foreach ([
            route('admin.users.show', $loan->user), route('admin.users.edit', $loan->user), route('admin.kyc.show', $doc),
            route('admin.applications.show', $loan->application), route('admin.loans.show', $loan),
            route('admin.products.edit', $loan->application->product), route('admin.tickets.show', $ticket),
            route('admin.faqs.edit', Faq::first()), route('admin.roles.edit', 2), route('admin.reviews.edit', Review::first()),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_can_update_website_settings(): void
    {
        $this->actingAs($this->admin())->put(route('admin.settings.website.update'), [
            'site_name' => 'New Name', 'primary_color' => '#112233', 'secondary_color' => '#445566', 'sections' => ['faq', 'calculator'],
        ])->assertSessionHas('success');

        $this->assertSame('New Name', site_name());
        $this->get('/')->assertSee('17 34 51');   // primary colour as CSS rgb variable
    }

    public function test_admin_rejects_invalid_colour(): void
    {
        $this->actingAs($this->admin())->put(route('admin.settings.website.update'), [
            'site_name' => 'X', 'primary_color' => 'red;}</style><script>', 'secondary_color' => '#445566',
        ])->assertSessionHasErrors('primary_color');
    }
}
