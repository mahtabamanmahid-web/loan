<?php

namespace Tests\Feature\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Services\LoanService;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Admin approval → agreement → disbursement → installments → completion. */
class LoanWorkflowTest extends TestCase
{
    private function disbursePayload(array $o = []): array
    {
        return array_merge([
            'method' => 'bkash', 'account' => '01700000000', 'reference' => 'BKASH123',
            'disbursed_at' => now()->format('Y-m-d H:i'), 'first_due_date' => now()->addMonth()->toDateString(), 'confirm' => '1',
        ], $o);
    }

    public function test_admin_approval_flow(): void
    {
        Notification::fake();
        $app = $this->submittedApplication();
        $admin = $this->admin('loan_officer');

        $this->actingAs($admin)->post(route('admin.applications.review', $app))->assertRedirect();
        $this->assertStatus($app, ApplicationStatus::UnderReview);

        $this->actingAs($admin)->post(route('admin.applications.approve', $app), [
            'approved_amount' => 15000, 'approved_months' => 6, 'approved_interest_rate' => 12,
        ])->assertSessionHas('success');

        $app->refresh();
        $this->assertSame(ApplicationStatus::Approved, $app->status);
        $this->assertEquals(15000, (float) $app->approved_amount);
        $this->assertDatabaseHas('admin_activity_logs', ['action' => 'loan.approved']);
    }

    public function test_cannot_approve_without_verified_nid(): void
    {
        $app = $this->submittedApplication($this->customer(kyc: false));
        $this->actingAs($this->admin())->post(route('admin.applications.approve', $app), [
            'approved_amount' => 15000, 'approved_months' => 6, 'approved_interest_rate' => 12,
        ])->assertSessionHas('error');
        $this->assertStatus($app, ApplicationStatus::NidPending);
    }

    public function test_reject_and_request_documents(): void
    {
        $admin = $this->admin();
        $a1 = $this->submittedApplication();
        $this->actingAs($admin)->post(route('admin.applications.reject', $a1), ['reason' => 'Income not verifiable'])->assertRedirect();
        $this->assertStatus($a1, ApplicationStatus::Rejected);
        $this->assertSame('Income not verifiable', $a1->fresh()->admin_note);

        $a2 = $this->submittedApplication();
        $this->actingAs($admin)->post(route('admin.applications.request-documents', $a2), ['message' => 'Send salary slip'])->assertRedirect();
        $this->assertStatus($a2, ApplicationStatus::DocumentsRequested);
    }

    public function test_agreement_must_be_accepted_before_disbursement(): void
    {
        $admin = $this->admin();
        $app = $this->submittedApplication();
        app(LoanService::class)->approve($app, $admin, 20000, 6, 12, null);

        $this->actingAs($admin)->post(route('admin.applications.agreement', $app))->assertRedirect();
        $this->assertStatus($app, ApplicationStatus::AwaitingAgreement);
        $this->assertNotNull($app->fresh()->agreement);

        // Not accepted yet → disbursement refused
        $this->actingAs($admin)->post(route('admin.applications.disburse', $app), $this->disbursePayload())->assertSessionHas('error');
        $this->assertDatabaseCount('loans', 0);

        // Customer accepts with typed signature
        $this->actingAs($app->user)->post(route('user.applications.agreement.accept', $app), ['accept_agreement' => '1', 'signature_name' => 'Wrong Name'])
            ->assertSessionHasErrors('signature_name');
        $this->actingAs($app->user)->post(route('user.applications.agreement.accept', $app), ['accept_agreement' => '1', 'signature_name' => 'rahim uddin'])
            ->assertRedirect();
        $this->assertNotNull($app->fresh()->agreement->accepted_at);
    }

    public function test_disbursement_creates_loan_and_installments(): void
    {
        $app = $this->signedApplication(null, 20000);
        $admin = $this->admin('payment_manager');

        $this->actingAs($admin)->post(route('admin.applications.disburse', $app), $this->disbursePayload())->assertRedirect();

        $loan = Loan::firstOrFail();
        $this->assertSame(LoanStatus::Active, $loan->status);
        $this->assertMatchesRegularExpression('/^LN-\d{6}-[A-Z0-9]{6}$/', $loan->loan_number);
        $this->assertEquals(19600, (float) $loan->disbursed_amount);     // 2% fee deducted
        $this->assertCount(6, $loan->installments);
        $this->assertEqualsWithDelta(21200, $loan->installments->sum('amount'), 0.001);
        $this->assertStatus($app, ApplicationStatus::Disbursed);
    }

    public function test_loan_cannot_be_disbursed_twice(): void
    {
        $app = $this->signedApplication();
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.applications.disburse', $app), $this->disbursePayload());
        $this->actingAs($admin)->post(route('admin.applications.disburse', $app), $this->disbursePayload(['reference' => 'OTHER']))->assertSessionHas('error');

        $this->assertDatabaseCount('loans', 1);
    }

    public function test_disbursement_reference_must_be_unique(): void
    {
        $this->activeLoan();   // uses a unique random ref
        $first = Loan::first();
        $app = $this->signedApplication();
        $this->actingAs($this->admin())->post(route('admin.applications.disburse', $app), $this->disbursePayload(['reference' => $first->disbursement_reference]))
            ->assertSessionHasErrors('reference');
    }

    public function test_upfront_fee_must_be_paid_before_disbursement(): void
    {
        $product = LoanProduct::factory()->upfrontFee()->withTerms()->create()->load('terms');
        $app = $this->signedApplication($product);

        $this->actingAs($this->admin())->post(route('admin.applications.disburse', $app), $this->disbursePayload())->assertSessionHas('error');
        $this->assertDatabaseCount('loans', 0);
    }

    public function test_manual_repayments_complete_the_loan(): void
    {
        $loan = $this->activeLoan(12000);
        $admin = $this->admin('payment_manager');
        $total = (float) $loan->total_payable;

        $this->actingAs($admin)->post(route('admin.loans.repayments.store', $loan), ['amount' => $total + 100, 'method' => 'cash', 'reference' => 'R1'])
            ->assertSessionHas('error');   // overpayment refused

        $this->actingAs($admin)->post(route('admin.loans.repayments.store', $loan), ['amount' => $total, 'method' => 'cash', 'reference' => 'R1'])
            ->assertSessionHas('success');

        $loan->refresh();
        $this->assertSame(LoanStatus::Completed, $loan->status);
        $this->assertSame(0, $loan->installments()->where('status', '!=', 'paid')->count());
        $this->assertStatus($loan->application, ApplicationStatus::Completed);
    }

    public function test_partial_payment_and_overdue_late_fee(): void
    {
        $loan = $this->activeLoan(12000);
        $first = $loan->installments->first();

        app(LoanService::class)->recordRepayment($loan, 1000, 'cash', 'P1');
        $this->assertSame(InstallmentStatus::Partial, $first->fresh()->status);

        // Move time past due date + grace → overdue with late fee (factory: 100 BDT, 3 days)
        $this->travelTo($first->due_date->copy()->addDays(5));
        $count = app(LoanService::class)->markOverdueInstallments();
        $this->assertGreaterThanOrEqual(1, $count);
        $this->assertSame(InstallmentStatus::Overdue, $first->fresh()->status);
        $this->assertEquals(100, (float) $first->fresh()->late_fee);
        $this->assertEquals(100, (float) $loan->fresh()->total_late_fee);

        // Running again must not add the fee twice
        app(LoanService::class)->markOverdueInstallments();
        $this->assertEquals(100, (float) $loan->fresh()->total_late_fee);
    }

    public function test_reminder_command_runs(): void
    {
        Notification::fake();
        $loan = $this->activeLoan();
        $this->travelTo($loan->first_due_date->copy()->subDays(2));

        $this->artisan('loans:reminders')->assertSuccessful();
        Notification::assertSentTo($loan->user, \App\Notifications\InstallmentReminder::class);
    }
}
