<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Role;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class Module11DocumentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected Document $document;

    protected Verification $verification;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Abebe Kebede',
            'status' => 'inactive',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin Officer',
            'status' => 'active',
        ]);
        Role::firstOrCreate(['name' => 'Administrator']);
        $this->admin->assignRole('Administrator');

        $this->document = Document::create([
            'user_id' => $this->user->id,
            'name' => 'National ID Card - Abebe',
            'type' => 'Identity Document',
            'status' => 'verified',
            'file_path' => 'documents/national_id_abebe.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048000,
            'description' => 'Scanned copy of Ethiopian National Digital ID.',
        ]);

        DocumentVersion::create([
            'document_id' => $this->document->id,
            'version_number' => 'v1.0',
            'file_path' => 'documents/national_id_abebe_v1.pdf',
            'uploaded_by' => $this->user->id,
            'change_summary' => 'Initial high-res upload',
        ]);

        $this->verification = Verification::create([
            'user_id' => $this->user->id,
            'type' => 'Identity Verification',
            'status' => 'pending_review',
            'risk_score' => 'low',
            'notes' => 'Awaiting manual agent verification of national ID.',
        ]);
    }

    public function test_documents_index_renders_with_database_records(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard.documents.index'));

        $response->assertStatus(200);
        $response->assertSee('National ID Card - Abebe');
        $response->assertSee('Abebe Kebede');
    }

    public function test_document_show_renders_and_provides_temporary_signed_url(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard.documents.show', $this->document->id));

        $response->assertStatus(200);
        $response->assertSee('National ID Card - Abebe');
        $response->assertSee('v1.0');
        $this->assertNotEmpty($this->document->download_url);
        $this->assertStringContainsString('signature=', $this->document->download_url);
    }

    public function test_signed_download_url_downloads_file_and_logs_access(): void
    {
        $signedUrl = URL::temporarySignedRoute(
            'dashboard.documents.download',
            now()->addMinutes(15),
            ['document' => $this->document->id]
        );

        $response = $this->actingAs($this->admin)->get($signedUrl);

        $response->assertStatus(200);
        $this->assertDatabaseHas('document_access_logs', [
            'document_id' => $this->document->id,
            'user_id' => $this->admin->id,
            'action' => 'download',
        ]);
    }

    public function test_expired_or_invalid_signed_download_url_returns_403(): void
    {
        // Expired signature
        $expiredUrl = URL::temporarySignedRoute(
            'dashboard.documents.download',
            now()->subMinutes(5),
            ['document' => $this->document->id]
        );

        $response = $this->actingAs($this->admin)->get($expiredUrl);
        $response->assertStatus(403);

        // Tampered signature
        $tamperedUrl = route('dashboard.documents.download', [
            'document' => $this->document->id,
            'signature' => 'invalid-hash-token',
        ]);

        $tamperedResponse = $this->actingAs($this->admin)->get($tamperedUrl);
        $tamperedResponse->assertStatus(403);
    }

    public function test_verifications_index_renders_records(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard.documents.verifications'));

        $response->assertStatus(200);
        $response->assertSee('Abebe Kebede');
        $response->assertSee('Identity Verification');
    }

    public function test_verification_review_approves_and_updates_user_status(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('dashboard.verifications.review', $this->verification->id),
            [
                'decision' => 'approve',
                'notes' => 'Official national ID verified against federal registry.',
            ]
        );

        $response->assertRedirect();
        $this->verification->refresh();
        $this->user->refresh();

        $this->assertSame('approved', $this->verification->status);
        $this->assertSame($this->admin->id, $this->verification->reviewed_by);
        $this->assertNotNull($this->verification->reviewed_at);
        $this->assertSame('Official national ID verified against federal registry.', $this->verification->notes);
        $this->assertSame('active', $this->user->status);
    }

    public function test_verification_review_rejects(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('dashboard.verifications.review', $this->verification->id),
            [
                'decision' => 'reject',
                'notes' => 'Document scan is blurry and unverifiable.',
            ]
        );

        $response->assertRedirect();
        $this->verification->refresh();

        $this->assertSame('rejected', $this->verification->status);
        $this->assertSame($this->admin->id, $this->verification->reviewed_by);
        $this->assertSame('Document scan is blurry and unverifiable.', $this->verification->notes);
    }
}
