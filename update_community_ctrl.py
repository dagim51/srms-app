import os

def write_file(path, content):
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

base_dir = r"c:\Users\dell\Desktop\SRMS\srms-app"
ctrl_path = os.path.join(base_dir, "app", "Http", "Controllers", "Api", "V1", "CommunityController.php")
test_path = os.path.join(base_dir, "tests", "Feature", "CommunityTest.php")

write_file(ctrl_path, """<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Community;
use App\Models\SharedFacility;
use App\Models\FacilityBooking;

class CommunityController extends Controller
{
    public function storeCommunity(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hoa_rules' => 'nullable|string'
        ]);

        $community = Community::create($validated);
        return response()->json(['data' => $community], 201);
    }

    public function storeFacility(Request $request, Community $community)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer',
            'booking_rules' => 'nullable|string'
        ]);

        $facility = $community->facilities()->create($validated);
        return response()->json(['data' => $facility], 201);
    }

    public function storeBooking(Request $request, SharedFacility $facility)
    {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time'
        ]);

        $booking = $facility->bookings()->create([
            'user_id' => auth()->id(),
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'pending'
        ]);

        return response()->json(['data' => $booking], 201);
    }
}
""")

write_file(test_path, """<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_facility_booking_workflow()
    {
        $user = User::factory()->create();

        // 1. Create Community
        $commRes = $this->actingAs($user)->postJson('/api/v1/communities', [
            'name' => 'Palm Grove Estate',
            'description' => 'A lovely community',
            'hoa_rules' => 'No loud music after 10 PM'
        ]);
        $commRes->assertStatus(201);
        $commId = $commRes->json('data.id');

        // 2. Create Facility
        $facRes = $this->actingAs($user)->postJson("/api/v1/communities/{$commId}/facilities", [
            'name' => 'Main Pool',
            'capacity' => 20
        ]);
        $facRes->assertStatus(201);
        $facId = $facRes->json('data.id');

        // 3. Book Facility
        $bookRes = $this->actingAs($user)->postJson("/api/v1/facilities/{$facId}/bookings", [
            'start_time' => '2026-10-10 10:00:00',
            'end_time' => '2026-10-10 12:00:00'
        ]);
        $bookRes->assertStatus(201);
        $bookId = $bookRes->json('data.id');

        $this->assertDatabaseHas('communities', ['id' => $commId]);
        $this->assertDatabaseHas('shared_facilities', ['id' => $facId]);
        $this->assertDatabaseHas('facility_bookings', [
            'id' => $bookId,
            'status' => 'pending'
        ]);
    }
}
""")

print("Controller and Test updated.")
