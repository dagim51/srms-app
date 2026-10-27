<?php

namespace App\Domains\Stay\Services;

use App\Models\Accommodation;
use App\Models\AccommodationAvailability;
use App\Models\AccommodationBooking;
use Carbon\Carbon;

class ICalGeneratorService
{
    /**
     * Generate standard RFC 5545 iCalendar format for an accommodation
     */
    public function generate(Accommodation $accommodation): string
    {
        $accommodation->loadMissing(['property']);

        $calName = 'SRMS - '.($accommodation->property?->title ?? 'Accommodation '.substr($accommodation->id, 0, 8));
        $nowStamp = Carbon::now('UTC')->format('Ymd\THis\Z');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SRMS//Accommodation Calendar 1.0//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->escapeString($calName),
            'X-WR-TIMEZONE:UTC',
        ];

        // 1. Export confirmed/active bookings
        $bookings = AccommodationBooking::where('accommodation_id', $accommodation->id)
            ->whereIn('status', ['confirmed', 'paid', 'checked_in'])
            ->get();

        foreach ($bookings as $booking) {
            $startDate = Carbon::parse($booking->check_in_date)->format('Ymd');
            $endDate = Carbon::parse($booking->check_out_date)->format('Ymd');
            $uid = 'srms-booking-'.$booking->id.'@srms.et';
            $summary = 'Reserved (SRMS-'.strtoupper(substr($booking->id, 0, 8)).')';

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$uid;
            $lines[] = 'DTSTAMP:'.$nowStamp;
            $lines[] = 'DTSTART;VALUE=DATE:'.$startDate;
            $lines[] = 'DTEND;VALUE=DATE:'.$endDate;
            $lines[] = 'SUMMARY:'.$this->escapeString($summary);
            $lines[] = 'DESCRIPTION:'.$this->escapeString('Confirmed Accommodation Booking on SRMS Platform');
            $lines[] = 'STATUS:CONFIRMED';
            $lines[] = 'END:VEVENT';
        }

        // 2. Export blocked dates & external reservations
        $availabilities = AccommodationAvailability::where('accommodation_id', $accommodation->id)->get();

        foreach ($availabilities as $avail) {
            $startDate = Carbon::parse($avail->start_date)->format('Ymd');
            // In RFC 5545, DTEND with VALUE=DATE is non-inclusive, so add 1 day to end_date
            $endDate = Carbon::parse($avail->end_date)->addDay()->format('Ymd');
            $uid = 'srms-avail-'.$avail->id.'@srms.et';

            $summary = $avail->reason ?: ($avail->type === 'external_reservation' ? 'External Reservation' : 'Blocked (Unavailable)');

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$uid;
            $lines[] = 'DTSTAMP:'.$nowStamp;
            $lines[] = 'DTSTART;VALUE=DATE:'.$startDate;
            $lines[] = 'DTEND;VALUE=DATE:'.$endDate;
            $lines[] = 'SUMMARY:'.$this->escapeString($summary);
            $lines[] = 'DESCRIPTION:'.$this->escapeString('Dates unavailable on SRMS Platform');
            $lines[] = 'STATUS:CONFIRMED';
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    protected function escapeString(string $text): string
    {
        return preg_replace('/([\,;])/', '\\\$1', str_replace(["\r", "\n"], ' ', $text));
    }
}
