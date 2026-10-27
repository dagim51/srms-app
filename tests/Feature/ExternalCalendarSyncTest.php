<?php

namespace Tests\Feature;

use App\Domains\Booking\Models\AccommodationBooking;
use App\Domains\Stay\Models\Accommodation;
use App\Domains\Stay\Models\AccommodationAvailability;
use App\Domains\Stay\Models\ExternalCalendarSync;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalCalendarSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $host;

    protected User $otherUser;

    protected Property $property;

    protected Accommodation $accommodation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->host = User::factory()->create();
        $this->otherUser = User::factory()->create();

        $this->property = Property::create([
            'title' => 'Bole Luxury Penthouse',
            'property_code' => 'PROP-BOLE-001',
            'owner_user_id' => $this->host->id,
            'status' => 'verified',
        ]);

        $this->accommodation = Accommodation::create([
            'property_id' => $this->property->id,
            'host_id' => $this->host->id,
            'max_guests' => 4,
            'is_active' => true,
        ]);
    }

    public function test_host_can_view_calendar_with_export_feed_and_sync_manager(): void
    {
        $response = $this->actingAs($this->host)
            ->get(route('dashboard.accommodations.calendar', $this->accommodation));

        $response->assertStatus(200);
        $response->assertSee('Export iCal Feed');
        $response->assertSee('Import External Calendars');
        $response->assertSee($this->accommodation->ical_token);
    }

    public function test_non_host_cannot_access_host_calendar_page(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('dashboard.accommodations.calendar', $this->accommodation));

        $response->assertStatus(403);
    }

    public function test_exports_valid_rfc_5545_ical_feed_with_bookings_and_blocks(): void
    {
        // 1. Confirmed Booking
        AccommodationBooking::create([
            'accommodation_id' => $this->accommodation->id,
            'user_id' => $this->otherUser->id,
            'check_in_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => Carbon::now()->addDays(8)->format('Y-m-d'),
            'guests' => 2,
            'total_price' => 450.00,
            'status' => 'confirmed',
        ]);

        // 2. Manual Block
        AccommodationAvailability::create([
            'accommodation_id' => $this->accommodation->id,
            'start_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
            'type' => 'blocked',
            'reason' => 'Interior Painting Maintenance',
        ]);

        $feedUrl = route('accommodations.calendar.feed', [
            'accommodation' => $this->accommodation,
            'token' => $this->accommodation->ical_token,
        ]);

        $response = $this->get($feedUrl);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $content);
        $this->assertStringContainsString('PRODID:-//SRMS//Accommodation Calendar', $content);
        $this->assertStringContainsString('Reserved (SRMS-', $content);
        $this->assertStringContainsString('SUMMARY:Interior Painting Maintenance', $content);
        $this->assertStringContainsString('END:VCALENDAR', $content);
    }

    public function test_rejects_export_feed_with_invalid_token(): void
    {
        $invalidUrl = route('accommodations.calendar.feed', [
            'accommodation' => $this->accommodation,
            'token' => 'invalid-tampered-token',
        ]);

        $response = $this->get($invalidUrl);

        $response->assertStatus(403);
    }

    public function test_host_can_connect_and_sync_external_ical_feed(): void
    {
        $sampleIcal = <<<ICAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Airbnb Inc//Hosting Calendar//EN
BEGIN:VEVENT
UID:airbnb-res-12345@airbnb.com
DTSTART;VALUE=DATE:20261101
DTEND;VALUE=DATE:20261105
SUMMARY:Airbnb (Not available)
DESCRIPTION:Reservation
END:VEVENT
END:VCALENDAR
ICAL;

        Http::fake([
            'https://www.airbnb.com/calendar/ical/12345.ics' => Http::response($sampleIcal, 200),
        ]);

        $response = $this->actingAs($this->host)
            ->from(route('dashboard.accommodations.calendar', $this->accommodation))
            ->post(route('dashboard.accommodations.calendar.syncs.store', $this->accommodation), [
                'name' => 'Airbnb Penthouse Feed',
                'url' => 'https://www.airbnb.com/calendar/ical/12345.ics',
            ]);

        $response->assertRedirect(route('dashboard.accommodations.calendar', $this->accommodation));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('external_calendar_syncs', [
            'accommodation_id' => $this->accommodation->id,
            'name' => 'Airbnb Penthouse Feed',
            'url' => 'https://www.airbnb.com/calendar/ical/12345.ics',
            'status' => 'synced',
            'events_count' => 1,
        ]);

        // Check imported availability record
        $this->assertDatabaseHas('accommodation_availabilities', [
            'accommodation_id' => $this->accommodation->id,
            'type' => 'external_reservation',
            'start_date' => '2026-11-01 00:00:00',
            'end_date' => '2026-11-04 00:00:00', // RFC 5545 DTEND is exclusive (Nov 5 check-out = Nov 4 night blocked)
        ]);
    }

    public function test_host_can_manually_refresh_external_calendar_feed(): void
    {
        $sync = ExternalCalendarSync::create([
            'accommodation_id' => $this->accommodation->id,
            'name' => 'Google Calendar Personal',
            'url' => 'https://calendar.google.com/calendar/ical/user/basic.ics',
            'status' => 'active',
            'events_count' => 0,
        ]);

        $sampleIcal = <<<ICAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Google Inc//Google Calendar 70.9054//EN
BEGIN:VEVENT
UID:google-event-789@google.com
DTSTART:20261210T100000Z
DTEND:20261215T100000Z
SUMMARY:Personal Travel Block
END:VEVENT
END:VCALENDAR
ICAL;

        Http::fake([
            'https://calendar.google.com/calendar/ical/user/basic.ics' => Http::response($sampleIcal, 200),
        ]);

        $response = $this->actingAs($this->host)
            ->from(route('dashboard.accommodations.calendar', $this->accommodation))
            ->post(route('dashboard.accommodations.calendar.syncs.refresh', [
                'accommodation' => $this->accommodation,
                'sync' => $sync,
            ]));

        $response->assertRedirect(route('dashboard.accommodations.calendar', $this->accommodation));
        $response->assertSessionHas('success');

        $sync->refresh();
        $this->assertEquals(1, $sync->events_count);
        $this->assertEquals('synced', $sync->status);

        $this->assertDatabaseHas('accommodation_availabilities', [
            'accommodation_id' => $this->accommodation->id,
            'type' => 'external_reservation',
            'start_date' => '2026-12-10 00:00:00',
            'end_date' => '2026-12-14 00:00:00',
        ]);
    }

    public function test_disconnecting_feed_removes_synced_availability_records(): void
    {
        $sync = ExternalCalendarSync::create([
            'accommodation_id' => $this->accommodation->id,
            'name' => 'Temporary Airbnb Feed',
            'url' => 'https://example.com/calendar.ics',
            'status' => 'active',
        ]);

        AccommodationAvailability::create([
            'accommodation_id' => $this->accommodation->id,
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-25',
            'type' => 'external_reservation',
            'reason' => "[Sync:{$sync->name} #{$sync->id}] Airbnb Reservation",
        ]);

        $response = $this->actingAs($this->host)
            ->from(route('dashboard.accommodations.calendar', $this->accommodation))
            ->delete(route('dashboard.accommodations.calendar.syncs.destroy', [
                'accommodation' => $this->accommodation,
                'sync' => $sync,
            ]));

        $response->assertRedirect(route('dashboard.accommodations.calendar', $this->accommodation));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('external_calendar_syncs', ['id' => $sync->id]);
        $this->assertDatabaseMissing('accommodation_availabilities', [
            'accommodation_id' => $this->accommodation->id,
            'reason' => "[Sync:{$sync->name} #{$sync->id}] Airbnb Reservation",
        ]);
    }

    public function test_artisan_sync_external_calendars_command_runs_successfully(): void
    {
        $sync = ExternalCalendarSync::create([
            'accommodation_id' => $this->accommodation->id,
            'name' => 'Background Sync Feed',
            'url' => 'https://example.com/sync.ics',
            'status' => 'active',
        ]);

        $sampleIcal = <<<ICAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Airbnb Inc//Calendar//EN
BEGIN:VEVENT
UID:artisan-sync-1@airbnb.com
DTSTART;VALUE=DATE:20261101
DTEND;VALUE=DATE:20261103
SUMMARY:Airbnb Reserved
END:VEVENT
END:VCALENDAR
ICAL;

        Http::fake([
            'https://example.com/sync.ics' => Http::response($sampleIcal, 200),
        ]);

        $this->artisan('calendar:sync-external')
            ->expectsOutputToContain('Synchronizing all active external calendar feeds')
            ->expectsOutputToContain('Completed external calendar synchronization')
            ->assertExitCode(0);

        $sync->refresh();
        $this->assertEquals(1, $sync->events_count);
    }
}
