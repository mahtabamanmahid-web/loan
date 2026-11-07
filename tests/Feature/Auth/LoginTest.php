<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_customer_can_login_with_phone(): void
    {
        $user = User::factory()->create(['phone' => '01711111111']);

        $this->post('/login', ['login' => '01711111111', 'password' => 'password1'])->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('login_histories', ['user_id' => $user->id, 'successful' => true]);
    }

    public function test_customer_can_login_with_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->post('/login', ['login' => 'TEST@example.com', 'password' => 'password1'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_fails_and_is_logged(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['login' => $user->phone, 'password' => 'wrong-pass'])->assertSessionHasErrors('login');
        $this->assertGuest();
        $this->assertDatabaseHas('login_histories', ['user_id' => $user->id, 'successful' => false]);
    }

    public function test_blocked_user_cannot_login(): void
    {
        $user = User::factory()->blocked()->create();

        $this->post('/login', ['login' => $user->phone, 'password' => 'password1'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['login' => $user->phone, 'password' => 'bad']);
        }

        $this->post('/login', ['login' => $user->phone, 'password' => 'password1'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_customer_cannot_login_on_admin_login_page(): void
    {
        $user = User::factory()->create();
        $this->post('/admin/login', ['login' => $user->email, 'password' => 'password1'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_admin_can_login_on_admin_page(): void
    {
        $admin = $this->admin();
        $this->post('/admin/login', ['login' => $admin->email, 'password' => 'password1'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
