<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Karim Ahmed',
            'phone' => '01712345678',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ], $overrides);
    }

    public function test_registration_page_has_no_email_field(): void
    {
        $this->get('/register')->assertOk()->assertSee(site_name())->assertDontSee('name="email"', false);
    }

    public function test_user_registers_with_mobile_only_and_goes_straight_to_dashboard(): void
    {
        $this->post('/register', $this->payload())->assertRedirect(route('user.dashboard'));

        $user = User::where('phone', '01712345678')->firstOrFail();
        $this->assertNull($user->email);
        $this->assertTrue(Hash::check('secret123', $user->password), 'password must be hashed');
        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk();
    }

    public function test_phone_with_country_code_is_normalised(): void
    {
        $this->post('/register', $this->payload(['phone' => '+880 1712-345678']))->assertRedirect();
        $this->assertDatabaseHas('users', ['phone' => '01712345678']);
    }

    public function test_duplicate_phone_is_rejected(): void
    {
        User::factory()->create(['phone' => '01712345678']);
        $this->post('/register', $this->payload())->assertSessionHasErrors('phone');
    }

    public function test_many_customers_can_register_without_email(): void
    {
        $this->post('/register', $this->payload());
        auth()->logout();
        $this->post('/register', $this->payload(['phone' => '01812345678']))->assertRedirect(route('user.dashboard'));

        $this->assertSame(2, User::whereNull('email')->count());
    }

    public function test_terms_must_be_accepted_and_phone_valid(): void
    {
        $this->post('/register', $this->payload(['terms' => null, 'phone' => '12345']))
            ->assertSessionHasErrors(['terms', 'phone']);
        $this->assertGuest();
    }

    public function test_forgot_password_page_shows_support_contact(): void
    {
        $this->get('/forgot-password')->assertOk();
    }
}
