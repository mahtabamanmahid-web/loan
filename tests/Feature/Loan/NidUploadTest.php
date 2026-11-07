<?php

namespace Tests\Feature\Loan;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentStatus;
use App\Models\UserDocument;
use App\Services\KycService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NidUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function upload(array $overrides = []): array
    {
        return array_merge([
            'nid_front' => UploadedFile::fake()->image('front.jpg', 800, 500),
            'nid_back' => UploadedFile::fake()->image('back.jpg', 800, 500),
            'consent' => '1',
        ], $overrides);
    }

    public function test_nid_photos_stored_privately_without_nid_number(): void
    {
        $user = $this->customer(kyc: false);

        $this->actingAs($user)->post('/kyc', $this->upload())->assertRedirect()->assertSessionHas('success');

        $doc = UserDocument::firstOrFail();
        $this->assertSame(DocumentStatus::Pending, $doc->status);
        $this->assertNull($doc->nid_number);                    // photo only, no number collected
        Storage::disk('local')->assertExists($doc->front_image_path);
        Storage::disk('local')->assertExists($doc->back_image_path);
        // nothing on the public disk
        $this->assertEmpty(Storage::disk('public')->allFiles());
        // file name is random, not the client name
        $this->assertStringNotContainsString('front', $doc->front_image_path);
    }

    public function test_invalid_file_type_and_size_are_rejected(): void
    {
        $user = $this->customer(kyc: false);

        $this->actingAs($user)->post('/kyc', $this->upload([
            'nid_front' => UploadedFile::fake()->create('front.pdf', 100, 'application/pdf'),
            'nid_back' => UploadedFile::fake()->image('back.jpg', 800, 500)->size(6000),
        ]))->assertSessionHasErrors(['nid_front', 'nid_back']);

        $this->assertDatabaseCount('user_documents', 0);
    }

    public function test_consent_is_required(): void
    {
        $this->actingAs($this->customer(kyc: false))->post('/kyc', $this->upload(['consent' => null]))->assertSessionHasErrors('consent');
        $this->assertDatabaseCount('user_documents', 0);
    }

    public function test_only_authorised_admin_can_view_nid_image(): void
    {
        $owner = $this->customer(kyc: false);
        $this->actingAs($owner)->post('/kyc', $this->upload());
        $doc = UserDocument::firstOrFail();
        $url = route('admin.kyc.file', [$doc, 'front']);

        $this->actingAs($owner)->get($url)->assertForbidden();                        // customer
        $this->actingAs($this->admin('payment_manager'))->get($url)->assertForbidden(); // no kyc.review
        $this->actingAs($this->admin('loan_officer'))->get($url)->assertOk()->assertHeader('cache-control', 'no-store, private');
        $this->assertDatabaseHas('admin_activity_logs', ['action' => 'kyc.viewed']);
    }

    public function test_admin_approval_moves_nid_pending_applications_forward(): void
    {
        $user = $this->customer(kyc: false);
        $this->actingAs($user)->post('/kyc', $this->upload());
        $doc = UserDocument::firstOrFail();
        $app = $this->submittedApplication($user->fresh());
        $this->assertSame(ApplicationStatus::NidPending, $app->status);

        $this->actingAs($this->admin('loan_officer'))->post(route('admin.kyc.review', $doc), ['status' => 'approved'])->assertRedirect();

        $this->assertSame(DocumentStatus::Approved, $doc->fresh()->status);
        $this->assertStatus($app, ApplicationStatus::Submitted);
    }

    public function test_rejection_requires_a_note(): void
    {
        $this->actingAs($this->customer(kyc: false))->post('/kyc', $this->upload());
        $doc = UserDocument::firstOrFail();

        $this->actingAs($this->admin())->post(route('admin.kyc.review', $doc), ['status' => 'rejected'])->assertSessionHasErrors('note');
        $this->assertSame(KycService::DISK, 'local');
    }
}
