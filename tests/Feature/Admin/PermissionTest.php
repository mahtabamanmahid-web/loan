<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

/** Role-based access control. */
class PermissionTest extends TestCase
{
    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/users')->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_access_admin_panel(): void
    {
        $this->actingAs($this->customer())->get('/admin')->assertForbidden();
    }

    public function test_admin_is_redirected_away_from_customer_panel(): void
    {
        $this->actingAs($this->admin())->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    }

    public function test_support_agent_cannot_approve_or_disburse(): void
    {
        $app = $this->submittedApplication();
        $agent = $this->admin('support_agent');

        $this->actingAs($agent)->get(route('admin.applications.show', $app))->assertOk();
        $this->actingAs($agent)->post(route('admin.applications.approve', $app), ['approved_amount' => 1, 'approved_months' => 6, 'approved_interest_rate' => 1])->assertForbidden();
        $this->actingAs($agent)->post(route('admin.applications.disburse', $app), [])->assertForbidden();
        $this->actingAs($agent)->get(route('admin.settings.website'))->assertForbidden();
    }

    public function test_loan_officer_cannot_manage_payments_or_admins(): void
    {
        $officer = $this->admin('loan_officer');
        $this->actingAs($officer)->get(route('admin.payments.index'))->assertForbidden();
        $this->actingAs($officer)->get(route('admin.admins.index'))->assertForbidden();
        $this->actingAs($officer)->get(route('admin.backups.index'))->assertForbidden();
    }

    public function test_payment_manager_can_export_but_not_review_kyc(): void
    {
        $pm = $this->admin('payment_manager');
        $this->actingAs($pm)->get(route('admin.payments.export'))->assertOk();
        $this->actingAs($pm)->get(route('admin.kyc.index'))->assertForbidden();
    }

    public function test_super_admin_has_everything(): void
    {
        $sa = $this->admin();
        foreach (['admin.admins.index', 'admin.roles.index', 'admin.backups.index', 'admin.settings.system', 'admin.logs.activity'] as $r) {
            $this->actingAs($sa)->get(route($r))->assertOk();
        }
    }

    public function test_role_permissions_can_be_changed(): void
    {
        $role = \App\Models\AdminRole::where('slug', 'support_agent')->first();
        $perm = \App\Models\AdminPermission::where('slug', 'payments.export')->first();

        $this->actingAs($this->admin())->put(route('admin.roles.update', $role), ['name' => 'Support Agent', 'permissions' => [$perm->id]])->assertRedirect();
        $agent = $this->admin('support_agent');
        $this->actingAs($agent)->get(route('admin.payments.export'))->assertOk();
        $this->actingAs($agent)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_last_super_admin_cannot_be_demoted(): void
    {
        $sa = $this->admin();
        $officerRole = \App\Models\AdminRole::where('slug', 'loan_officer')->value('id');

        $this->actingAs($sa)->put(route('admin.admins.update', $sa), [
            'name' => $sa->name, 'email' => $sa->email, 'phone' => $sa->phone, 'admin_role_id' => $officerRole,
        ])->assertSessionHas('error');
        $this->assertTrue($sa->fresh()->isSuperAdmin());
    }

    public function test_blocked_user_is_logged_out(): void
    {
        $user = $this->customer();
        $user->forceFill(['is_blocked' => true])->save();
        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
