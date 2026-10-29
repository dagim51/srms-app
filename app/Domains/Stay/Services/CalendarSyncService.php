<?php

namespace App\Domains\Stay\Services;

use App\Models\Accommodation;
use App\Models\AccommodationAvailability;
use App\Models\ExternalCalendarSync;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalendarSyncService
{
    /**
     * Synchronize a single external iCal calendar feed
     */
    public function syncFeed(ExternalCalendarSync $sync, ?string $rawIcsContent = null): int
    {
        try {
            $icsContent = $rawIcsContent;

            if ($icsContent === null) {
                $url = trim($sync->url);
                if (str_starts_with($url, 'webcal://')) {
                    $url = 'https://'.substr($url, 9);
                }

                $response = Http::timeout(15)
                    ->withHeaders(['User-Agent' => 'SRMS-Calendar-Sync/1.0'])
                    ->get($url);

                if (! $response->successful()) {
                    throw new Exception('HTTP request failed with status: '.$response->status());
                }

                $icsContent = $response->body();
            }

            $events = $this->parseIcsEvents($icsContent);

            DB::transaction(function () use ($sync, $events) {
                // Delete previous entries created by this sync feed
                AccommodationAvailability::where('accommodation_id', $sync->accommodation_id)
                    ->where('reason', 'like', "%[Sync:#{$sync->id}]%")
                    ->delete();

                // Insert newly parsed events as external reservations
                foreach ($events as $event) {
                    $summary = ! empty($event['summary']) ? $event['summary'] : 'External Reservation';
                    $reason = "[Sync:{$sync->name} #{$sync->id}] {$summary}";
                    if (! empty($event['uid'])) {
                        $reason .= " [UID:{$event['uid']}]";
                    }

                    AccommodationAvailability::create([
                        'accommodation_id' => $sync->accommodation_id,
                        'start_date' => $event['start_date'],
                        'end_date' => $event['end_date'],
                        'type' => 'external_reservation',
                        'reason' => substr($reason, 0, 255),
                    ]);
                }
            });

            $sync->update([
                'last_synced_at' => now(),
                'status' => 'synced',
                'events_count' => count($events),
                'last_error' => null,
            ]);

            return count($events);
        } catch (Exception $e) {
            Log::error("Failed to sync external calendar feed [{$sync->id}]: ".$e->getMessage());

            $sync->update([
                'status' => 'failed',
                'last_error' => substr($e->getMessage(), 0, 500),
            ]);

            throw $e;
        }
    }

    /**
     * Sync all active calendar feeds
     */
    public function syncAllActive(): int
    {
        $syncs = ExternalCalendarSync::where('status', '!=', 'paused')->get();
        $totalEvents = 0;

        foreach ($syncs as $sync) {
            try {
                $totalEvents += $this->syncFeed($sync);
            } catch (Exception $e) {
                // Continue with next feed on failure
                continue;
            }
        }

        return $totalEvents;
    }

    /**
     * Parse VEVENT elements from raw iCalendar string
     */
    public function parseIcsEvents(string $icsContent): array
    {
        $events = [];

        // Unfold folded lines (RFC 5545 lines beginning with whitespace continue the previous line)
        $icsContent = preg_replace("/\r\n[ \t]/", '', $icsContent);
        $icsContent = preg_replace("/\n[ \t]/", '', $icsContent);

        // Match all VEVENT blocks
        if (preg_match_all('/BEGIN:VEVENT\s*(.*?)\s*END:VEVENT/s', $icsContent, $matches)) {
            foreach ($matches[1] as $eventBlock) {
                $parsed = $this->parseSingleVevent($eventBlock);
                if ($parsed) {
                    $events[] = $parsed;
                }
            }
        }

        return $events;
    }

    protected function parseSingleVevent(string $block): ?array
    {
        $lines = preg_split("/\r\n|\n|\r/", $block);
        $data = [
            'uid' => null,
            'summary' => null,
            'start_date' => null,
            'end_date' => null,
        ];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if (preg_match('/^UID:(.+)$/i', $line, $m)) {
                $data['uid'] = trim($m[1]);
            } elseif (preg_match('/^SUMMARY:(.+)$/i', $line, $m)) {
                $data['summary'] = trim($m[1]);
            } elseif (preg_match('/^DTSTART(?:;[^:]+)?:([0-9TZ]+)$/i', $line, $m)) {
                $data['start_date'] = $this->parseIcsDate($m[1]);
            } elseif (preg_match('/^DTEND(?:;[^:]+)?:([0-9TZ]+)$/i', $line, $m)) {
                // In RFC 5545, all-day DTEND is exclusive, so subtract 1 day to make inclusive end_date
                $endDate = $this->parseIcsDate($m[1]);
                if ($endDate) {
                    $data['end_date'] = Carbon::parse($endDate)->subDay()->format('Y-m-d');
                }
            }
        }

        if (empty($data['start_date'])) {
            return null;
        }

        if (empty($data['end_date'])) {
            $data['end_date'] = $data['start_date'];
        }

        // If end_date is before start_date due to date/time format difference, set to start_date
        if ($data['end_date'] < $data['start_date']) {
            $data['end_date'] = $data['start_date'];
        }

        return $data;
    }

    protected function parseIcsDate(string $raw): ?string
    {
        $raw = trim($raw);
        // Date only: YYYYMMDD
        if (preg_match('/^([0-9]{4})([0-9]{2})([0-9]{2})$/', $raw, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }
        // DateTime: YYYYMMDDTHHMMSS...
        if (preg_match('/^([0-9]{4})([0-9]{2})([0-9]{2})T/i', $raw, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Generate standard RFC 5545 iCalendar feed string for an accommodation.
     */
    public function generateIcsFeed(Accommodation $accommodation): string
    {
        $accommodation->loadMissing(['property']);

        $availabilities = AccommodationAvailability::where('accommodation_id', $accommodation->id)
            ->where('end_date', '>=', now()->toDateString())
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SRMS//Ethiopian Real Estate & Short Stay Engine//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:SRMS - '.($accommodation->property?->title ?? 'Accommodation').' Calendar',
            'X-WR-TIMEZONE:UTC',
        ];

        foreach ($availabilities as $avail) {
            $dtStart = Carbon::parse($avail->start_date)->format('Ymd');
            $dtEnd = Carbon::parse($avail->end_date)->addDay()->format('Ymd');
            $uid = 'srms-avail-'.$avail->id.'@srms.et';
            $summary = match ($avail->type) {
                'held' => 'Temporary Hold (SRMS)',
                'booked' => 'Reserved (SRMS)',
                'maintenance' => 'Maintenance (SRMS)',
                default => 'Unavailable (SRMS)',
            };

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = "UID:{$uid}";
            $lines[] = 'DTSTAMP:'.gmdate('Ymd\THis\Z');
            $lines[] = "DTSTART;VALUE=DATE:{$dtStart}";
            $lines[] = "DTEND;VALUE=DATE:{$dtEnd}";
            $lines[] = "SUMMARY:{$summary}";
            $lines[] = 'STATUS:CONFIRMED';
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines);
    }
}
