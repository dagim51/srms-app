<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\LoginEvent;
use App\Domains\Listing\Models\Listing;
use App\Domains\Listing\Models\ListingFlag;
use App\Domains\Property\Models\Property;
use App\Domains\Property\Models\PropertyMedia;
use App\Domains\Trust\Models\Dispute;
use App\Domains\Trust\Models\DisputeEvidence;
use App\Domains\Trust\Models\UserFlag;
use App\Domains\Trust\Services\FraudDetectionService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Module12TrustSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $userA;

    protected User $userB;

    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Safety Admin',
            'status' => 'active',
        ]);

        $this->userA = User::factory()->create([
            'name' => 'Almaz Tefera',
            'status' => 'active',
        ]);

        $this->userB = User::factory()->create([
            'name' => 'Bekele Desta',
            'status' => 'active',
        ]);

        $this->property = Property::create([
            'title' => 'Sample Bole Residence',
            'owner_user_id' => $this->userB->id,
            'property_code' => 'BOL-RES-001',
            'status' => 'verified',
        ]);
    }

    public function test_trust_disputes_index_renders_live_records(): void
    {
        $dispute = Dispute::create([
            'complainant_id' => $this->userA->id,
            'respondent_id' => $this->userB->id,
            'disputable_id' => $this->property->id,
            'disputable_type' => Property::class,
            'type' => 'contract_dispute',
            'subject' => 'Security Deposit Withholding Dispute',
            'description' => 'Landlord refused to return security deposit after full walkthrough inspection.',
            'amount_in_dispute' => 25000.00,
            'currency' => 'ETB',
            'status' => 'open',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard.trust.disputes'));

        $response->assertStatus(200);
        $response->assertSee('Security Deposit Withholding Dispute');
        $response->assertSee('Almaz Tefera');
        $response->assertSee('Bekele Desta');
    }

    public function test_trust_reports_index_renders_live_records(): void
    {
        $property = Property::create([
            'title' => 'Luxury Villa Kazanchis',
            'owner_user_id' => $this->userB->id,
            'property_code' => 'KAZ-VIL-01',
            'status' => 'verified',
        ]);

        $listing = Listing::create([
            'property_id' => $property->id,
            'owner_actor_id' => $this->userB->id,
            'purpose' => 'For Rent',
            'price' => 60000.00,
            'status' => 'published',
        ]);

        $flag = ListingFlag::create([
            'listing_id' => $listing->id,
            'reporter_id' => $this->userA->id,
            'reason' => 'Misleading amenities and photos',
            'description' => 'The property claims a private pool which does not exist on site.',
            'severity' => 'medium',
            'status' => 'open',
            'reported_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard.trust.reports'));

        $response->assertStatus(200);
        $response->assertSee('Misleading amenities and photos');
        $response->assertSee('Luxury Villa Kazanchis');
    }

    public function test_trust_case_show_renders_dispute_details_and_evidence(): void
    {
        $dispute = Dispute::create([
            'complainant_id' => $this->userA->id,
            'respondent_id' => $this->userB->id,
            'disputable_id' => $this->property->id,
            'disputable_type' => Property::class,
            'type' => 'payment_issue',
            'subject' => 'Unpaid Maintenance Cost Reversal',
            'description' => 'Plumbing leaks required emergency repair paid by tenant.',
            'amount_in_dispute' => 12000.00,
            'currency' => 'ETB',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        DisputeEvidence::create([
            'dispute_id' => $dispute->id,
            'submitted_by' => $this->userA->id,
            'title' => 'Plumber_Receipt_Signed.pdf',
            'type' => 'document',
            'file_path' => 'evidence/receipt_001.pdf',
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard.trust.cases.show', $dispute->id));

        $response->assertStatus(200);
        $response->assertSee('Unpaid Maintenance Cost Reversal');
        $response->assertSee('Plumber_Receipt_Signed.pdf');
    }

    public function test_dispute_can_be_resolved_via_mediator_action(): void
    {
        $dispute = Dispute::create([
            'complainant_id' => $this->userA->id,
            'respondent_id' => $this->userB->id,
            'disputable_id' => $this->property->id,
            'disputable_type' => Property::class,
            'type' => 'contract_dispute',
            'subject' => 'Lease Termination Settlement',
            'description' => 'Mutual lease end dispute.',
            'amount_in_dispute' => 15000.00,
            'currency' => 'ETB',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('dashboard.trust.cases.resolve', $dispute->id),
            [
                'status' => 'resolved',
                'resolution_notes' => 'Both parties mutually agreed to refund 50% of the disputed sum.',
            ]
        );

        $response->assertRedirect();
        $dispute->refresh();

        $this->assertSame('resolved', $dispute->status);
        $this->assertSame('Both parties mutually agreed to refund 50% of the disputed sum.', $dispute->resolution_notes);
        $this->assertNotNull($dispute->resolved_at);
    }

    public function test_fraud_detection_raises_flag_on_severe_price_anomaly(): void
    {
        // Setup baseline market properties in same neighborhood
        for ($i = 1; $i <= 3; $i++) {
            $p = Property::create([
                'title' => "Bole Apartment {$i}",
                'owner_user_id' => $this->userB->id,
                'property_code' => "BOL-APT-{$i}",
                'status' => 'verified',
            ]);

            Listing::create([
                'property_id' => $p->id,
                'owner_actor_id' => $this->userB->id,
                'purpose' => 'For Rent',
                'price' => 50000.00,
                'status' => 'published',
            ]);
        }

        // Fraudulent listing with price 5,000 ETB (90% below average of 50,000 ETB)
        $fraudProperty = Property::create([
            'title' => 'Unbelievably Cheap Penthouse',
            'owner_user_id' => $this->userA->id,
            'property_code' => 'BOL-FRAUD-01',
            'status' => 'verified',
        ]);

        $fraudListing = Listing::create([
            'property_id' => $fraudProperty->id,
            'owner_actor_id' => $this->userA->id,
            'purpose' => 'For Rent',
            'price' => 5000.00,
            'status' => 'published',
        ]);

        $service = new FraudDetectionService;
        $flag = $service->checkPriceAnomaly($fraudListing, 0.70);

        $this->assertNotNull($flag);
        $this->assertInstanceOf(ListingFlag::class, $flag);
        $this->assertSame('high', $flag->severity);
        $this->assertSame($fraudListing->id, $flag->listing_id);
        $this->assertStringContainsString('anomaly', strtolower($flag->reason));

        $this->assertDatabaseHas('listing_flags', [
            'id' => $flag->id,
            'listing_id' => $fraudListing->id,
            'severity' => 'high',
        ]);
    }

    public function test_fraud_detection_does_not_flag_reasonable_price(): void
    {
        $p = Property::create([
            'title' => 'Fair Market Apartment',
            'owner_user_id' => $this->userB->id,
            'property_code' => 'BOL-FAIR-01',
            'status' => 'verified',
        ]);

        $listing = Listing::create([
            'property_id' => $p->id,
            'owner_actor_id' => $this->userB->id,
            'purpose' => 'For Rent',
            'price' => 45000.00,
            'status' => 'published',
        ]);

        $service = new FraudDetectionService;
        $flag = $service->checkPriceAnomaly($listing, 0.70);

        $this->assertNull($flag);
    }

    public function test_fraud_detection_flags_high_registration_velocity_from_single_ip(): void
    {
        $ip = '196.188.10.55';

        for ($i = 1; $i <= 3; $i++) {
            $user = User::factory()->create();
            LoginEvent::create([
                'user_id' => $user->id,
                'event_type' => 'register',
                'ip' => $ip,
                'occurred_at' => now(),
            ]);
        }

        $service = new FraudDetectionService;
        $flag = $service->checkRegistrationVelocity($ip, 10, 3, $user->id, $this->admin->id);

        $this->assertNotNull($flag);
        $this->assertInstanceOf(UserFlag::class, $flag);
        $this->assertSame('high', $flag->severity);
        $this->assertStringContainsString('velocity', strtolower($flag->reason));
        $this->assertDatabaseHas('user_flags', [
            'id' => $flag->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_fraud_detection_flags_duplicate_media_across_different_properties(): void
    {
        $checksum = '9f837fc8d31ffb15a6b0dcd3f5424fca9f659357';

        $propA = Property::create([
            'title' => 'Original Property',
            'owner_user_id' => $this->userA->id,
            'property_code' => 'ORIG-001',
            'status' => 'verified',
        ]);

        PropertyMedia::create([
            'property_id' => $propA->id,
            'kind' => 'image',
            'storage_key' => 'properties/original.jpg',
            'checksum' => $checksum,
            'status' => 'processed',
        ]);

        $propB = Property::create([
            'title' => 'Clone Scammer Property',
            'owner_user_id' => $this->userB->id,
            'property_code' => 'CLONE-001',
            'status' => 'verified',
        ]);

        $listingB = Listing::create([
            'property_id' => $propB->id,
            'owner_actor_id' => $this->userB->id,
            'purpose' => 'For Rent',
            'price' => 30000.00,
            'status' => 'published',
        ]);

        $mediaB = PropertyMedia::create([
            'property_id' => $propB->id,
            'kind' => 'image',
            'storage_key' => 'properties/clone.jpg',
            'checksum' => $checksum,
            'status' => 'processed',
        ]);

        $service = new FraudDetectionService;
        $flag = $service->checkDuplicateMedia($mediaB);

        $this->assertNotNull($flag);
        $this->assertSame($listingB->id, $flag->listing_id);
        $this->assertSame('high', $flag->severity);
        $this->assertStringContainsString('duplicate', strtolower($flag->reason));
    }

    public function test_can_create_dispute_case_via_web_form(): void
    {
        $response = $this->actingAs($this->admin)->post(route('dashboard.trust.cases.store'), [
            'complainant_id' => $this->userA->id,
            'respondent_id' => $this->userB->id,
            'subject' => 'Late Rent Penalty Dispute',
            'description' => 'Unfairly assessed late fee during banking outage.',
            'type' => 'payment_issue',
            'amount_in_dispute' => 5000,
            'priority' => 'high',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('disputes', [
            'complainant_id' => $this->userA->id,
            'respondent_id' => $this->userB->id,
            'subject' => 'Late Rent Penalty Dispute',
            'status' => 'open',
            'priority' => 'high',
        ]);
    }

    public function test_can_upload_dispute_evidence(): void
    {
        $dispute = Dispute::create([
            'complainant_id' => $this->userA->id,
            'respondent_id' => $this->userB->id,
            'disputable_type' => Property::class,
            'disputable_id' => $this->property->id,
            'type' => 'contract_dispute',
            'subject' => 'Evidence Test Case',
            'description' => 'Testing evidence artifact attachment.',
            'status' => 'open',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($this->admin)->post(route('dashboard.trust.cases.evidence', $dispute->id), [
            'title' => 'Wire_Transfer_Receipt.pdf',
            'type' => 'payment_slip',
            'description' => 'Bank confirmation stamp from CBE branch.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dispute_evidence', [
            'dispute_id' => $dispute->id,
            'title' => 'Wire_Transfer_Receipt.pdf',
            'type' => 'payment_slip',
        ]);
    }

    public function test_trust_verifications_queue_renders_and_reviews_records(): void
    {
        $verification = \App\Domains\Trust\Models\Verification::create([
            'user_id' => $this->userA->id,
            'type' => 'identity',
            'status' => 'pending_review',
            'risk_score' => 'low',
        ]);

        // 1. Queue renders verification record
        $queueResponse = $this->actingAs($this->admin)->get(route('dashboard.trust.verifications'));
        $queueResponse->assertStatus(200);
        $queueResponse->assertSee($this->userA->name);
        $queueResponse->assertSee('Identity');

        // 2. Review and approve credential
        $reviewResponse = $this->actingAs($this->admin)->post(route('dashboard.trust.verifications.review', $verification->id), [
            'status' => 'approved',
            'risk_score' => 'low',
            'notes' => 'Fayda digital ID verified with National ID Program API.',
        ]);

        $reviewResponse->assertRedirect();
        $verification->refresh();
        $this->assertSame('approved', $verification->status);
        $this->assertSame('Fayda digital ID verified with National ID Program API.', $verification->notes);
    }
}
