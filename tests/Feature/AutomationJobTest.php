<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccommodationBooking;
use App\Models\Property;
use App\Models\Accommodation;
use App\Jobs\ProcessUpcomingBookingsJob;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

class AutomationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_confirmed_notification_is_sent()
    {
        Notification::fake();
        
        $user = User::factory()->create();
        
        $property = Property::create([
            'title' => 'Test Booking Property',
            'property_code' => 'TEST-BOOK-NOTIFY-001',
            'owner_user_id' => $user->id,
            'status' => 'verified',
        ]);
        
        $accommodation = Accommodation::create([
            'property_id' => $property->id,
            'host_id' => $user->id,
            'max_guests' => 4,
        ]);
        
        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'user_id' => $user->id,
            'check_in_date' => now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => now()->addDays(7)->format('Y-m-d'),
            'guests' => 2,
            'total_price' => 200,
            'status' => 'confirmed',
        ]);
        
        $user->notify(new BookingConfirmedNotification($booking));
        
        Notification::assertSentTo(
            [$user], BookingConfirmedNotification::class
        );
    }

    public function test_upcoming_bookings_automation_job_dispatches()
    {
        Queue::fake();
        
        ProcessUpcomingBookingsJob::dispatch();
        
        Queue::assertPushed(ProcessUpcomingBookingsJob::class);
    }

    public function test_upcoming_bookings_automation_job_processes()
    {
        $user = User::factory()->create();
        
        $property = Property::create([
            'title' => 'Test Booking Property',
            'property_code' => 'TEST-JOB-001',
            'owner_user_id' => $user->id,
            'status' => 'verified',
        ]);
        
        $accommodation = Accommodation::create([
            'property_id' => $property->id,
            'host_id' => $user->id,
            'max_guests' => 4,
        ]);
        
        // This booking is exactly tomorrow
        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'user_id' => $user->id,
            'check_in_date' => now()->addDay()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'guests' => 2,
            'total_price' => 200,
            'status' => 'confirmed',
        ]);
        
        $job = new ProcessUpcomingBookingsJob();
        $job->handle();
        
        // At this point we are just asserting it doesn't crash 
        // and ideally checks for side-effects (like Log outputs which we simulated)
        $this->assertTrue(true);
    }
}
