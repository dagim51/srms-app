<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\DocumentTemplate;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_signature_workflow()
    {
        $landlord = User::factory()->create();
        $tenant = User::factory()->create();

        // 1. Create Template
        $tplRes = $this->actingAs($landlord)->postJson('/api/v1/documents/templates', [
            'name' => 'Standard Lease',
            'body_content' => '<p>This is a standard lease...</p>'
        ]);
        $tplRes->assertStatus(201);
        $tplId = $tplRes->json('data.id');

        // 2. Generate Contract
        $contractRes = $this->actingAs($landlord)->postJson('/api/v1/documents/contracts', [
            'document_template_id' => $tplId,
            'title' => 'Lease for Unit 1A',
            'signatories' => [
                ['user_id' => $landlord->id, 'role' => 'Landlord'],
                ['user_id' => $tenant->id, 'role' => 'Tenant'],
            ]
        ]);
        $contractRes->assertStatus(201);
        $contractId = $contractRes->json('data.id');

        // 3. Landlord Signs
        $signRes1 = $this->actingAs($landlord)->postJson("/api/v1/documents/contracts/{$contractId}/sign");
        $signRes1->assertStatus(200);
        
        $this->assertDatabaseHas('contracts', [
            'id' => $contractId,
            'status' => 'partial_signed'
        ]);

        // 4. Tenant Signs
        $signRes2 = $this->actingAs($tenant)->postJson("/api/v1/documents/contracts/{$contractId}/sign");
        $signRes2->assertStatus(200);

        $this->assertDatabaseHas('contracts', [
            'id' => $contractId,
            'status' => 'fully_signed'
        ]);
    }
}
