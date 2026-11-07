<?php

namespace Tests\Feature\Loan;

use App\Enums\ApplicationStatus;
use App\Models\LoanApplication;
use App\Notifications\ApplicationSubmitted;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LoanApplicationTest extends TestCase
{
    public function test_customer_without_nid_is_sent_to_kyc_page(): void
    {
        $this->actingAs($this->customer(kyc: false))->get('/apply')->assertRedirect(route('user.kyc.show'));
    }

    public function test_wizard_renders_for_verified_customer(): void
    {
        $this->product();
        $this->actingAs($this->customer())->get('/apply')->assertOk()->assertSee('applyWizard');
    }

    public function test_customer_can_submit_application_with_correct_snapshot(): void
    {
        Notification::fake();
        $user = $this->customer();
        $product = $this->product();

        $this->actingAs($user)->post('/apply', $this->applicationPayload($product, 20000, 6))->assertRedirect();

        $app = LoanApplication::firstOrFail();
        $this->assertSame($user->id, $app->user_id);
        $this->assertSame(ApplicationStatus::Submitted, $app->status);
        $this->assertEquals(21200, (float) $app->total_payable);
        $this->assertEquals(400, (float) $app->processing_fee);
        $this->assertMatchesRegularExpression('/^APP-\d{6}-[A-Z0-9]{6}$/', $app->reference);
        // Address is encrypted at rest
        $this->assertStringNotContainsString('Dhaka', (string) \DB::table('loan_applications')->value('present_address'));
        Notification::assertSentTo($user, ApplicationSubmitted::class);
    }

    public function test_application_with_pending_nid_gets_nid_pending_status(): void
    {
        $user = $this->customer(kyc: false);
        $user->documents()->create(['nid_number' => '1234567890', 'nid_hash' => 'x', 'front_image_path' => 'a', 'back_image_path' => 'b', 'status' => 'pending']);

        $this->actingAs($user)->post('/apply', $this->applicationPayload($this->product()))->assertRedirect();
        $this->assertSame(ApplicationStatus::NidPending, LoanApplication::first()->status);
    }

    public function test_amount_outside_limits_is_rejected(): void
    {
        $product = $this->product(['min_amount' => 5000, 'max_amount' => 50000]);
        $this->actingAs($this->customer())->post('/apply', $this->applicationPayload($product, 900000))->assertSessionHas('error');
        $this->assertDatabaseCount('loan_applications', 0);
    }

    public function test_required_fields_and_declarations_are_validated(): void
    {
        $payload = $this->applicationPayload($this->product());
        unset($payload['accept_fees'], $payload['present_address']);

        $this->actingAs($this->customer())->post('/apply', $payload)->assertSessionHasErrors(['accept_fees', 'present_address']);
    }

    public function test_only_one_open_application_allowed(): void
    {
        $user = $this->customer();
        $product = $this->product();
        $this->submittedApplication($user, $product);

        $this->actingAs($user)->post('/apply', $this->applicationPayload($product))->assertSessionHas('error');
        $this->assertDatabaseCount('loan_applications', 1);
    }

    public function test_customer_cannot_view_someone_elses_application(): void
    {
        $app = $this->submittedApplication();
        $this->actingAs($this->customer())->get(route('user.applications.show', $app))->assertNotFound();
        $this->actingAs($app->user)->get(route('user.applications.show', $app))->assertOk();
    }

    public function test_customer_can_cancel_submitted_application(): void
    {
        $app = $this->submittedApplication();
        $this->actingAs($app->user)->post(route('user.applications.cancel', $app))->assertRedirect();
        $this->assertStatus($app, ApplicationStatus::Cancelled);
    }

    public function test_application_pdf_downloads(): void
    {
        $app = $this->submittedApplication();
        $this->actingAs($app->user)->get(route('user.applications.pdf', $app))->assertOk()->assertHeader('content-type', 'application/pdf');
    }
}
