<?php

namespace Tests\Feature\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Karim Ahmed',
            'phone' => '01712345678',
            'email' => 'karim@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ], $overrides);
    }

    public function test_registration_page_renders(): void
    {
        $this->get('/register')->assertOk()->assertSee(site_name());
    }

    public function test_user_can_register_and_is_sent_to_otp(): void
    {
        $this->post('/register', $this->payload())->assertRedirect(route('otp.show'));

        $user = User::where('phone', '01712345678')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->phone_verified_at);
        $this->assertTrue(Hash::check('secret123', $user->password), 'password must be hashed');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('otp_codes', ['identifier' => '01712345678', 'purpose' => 'register']);
    }

    public function test_phone_with_country_code_is_normalised(): void
    {
        $this->post('/register', $this->payload(['phone' => '+880 1712-345678']))->assertRedirect();
        $this->assertDatabaseHas('users', ['phone' => '01712345678']);
    }

    public function test_duplicate_phone_or_email_is_rejected(): void
    {
        User::factory()->create(['phone' => '01712345678', 'email' => 'other@example.com']);
        $this->post('/register', $this->payload())->assertSessionHasErrors('phone');

        User::factory()->create(['phone' => '01812345678', 'email' => 'karim@example.com']);
        $this->post('/register', $this->payload(['phone' => '01912345678']))->assertSessionHasErrors('email');
    }

    public function test_terms_must_be_accepted_and_phone_valid(): void
    {
        $this->post('/register', $this->payload(['terms' => null, 'phone' => '12345']))
            ->assertSessionHasErrors(['terms', 'phone']);
        $this->assertGuest();
    }

    public function test_unverified_user_cannot_open_dashboard(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('otp.show'));
    }

    public function test_correct_otp_verifies_phone(): void
    {
        $user = User::factory()->unverified()->create();
        // Create a known OTP directly
        OtpCode::create(['identifier' => $user->phone, 'purpose' => 'register', 'code_hash' => Hash::make('123456'), 'expires_at' => now()->addMinutes(5)]);

        $this->actingAs($user)->post('/verify-phone', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->actingAs($user)->post('/verify-phone', ['code' => '123456'])->assertRedirect(route('user.dashboard'));
        $this->assertNotNull($user->fresh()->phone_verified_at);
    }

    public function test_otp_is_locked_after_too_many_wrong_attempts(): void
    {
        OtpCode::create(['identifier' => '01700000000', 'purpose' => 'register', 'code_hash' => Hash::make('123456'), 'expires_at' => now()->addMinutes(5)]);
        $otp = app(OtpService::class);

        for ($i = 0; $i < OtpService::MAX_ATTEMPTS; $i++) {
            $this->assertFalse($otp->verify('01700000000', 'register', '111111'));
        }
        // Even the right code no longer works
        $this->assertFalse($otp->verify('01700000000', 'register', '123456'));
    }

    public function test_expired_otp_is_rejected(): void
    {
        OtpCode::create(['identifier' => '01700000001', 'purpose' => 'register', 'code_hash' => Hash::make('123456'), 'expires_at' => now()->subMinute()]);
        $this->assertFalse(app(OtpService::class)->verify('01700000001', 'register', '123456'));
    }
}
