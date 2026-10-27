<?php

namespace App\Console\Commands;

use App\Domains\Stay\Services\CalendarSyncService;
use App\Models\ExternalCalendarSync;
use Illuminate\Console\Command;

class SyncExternalCalendarsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:sync-external {--feed= : Specific sync ID to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize external iCal calendar feeds (Airbnb, Google Calendar, VRBO, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(CalendarSyncService $service): int
    {
        $feedId = $this->option('feed');

        if ($feedId) {
            $sync = ExternalCalendarSync::find($feedId);
            if (!$sync) {
                $this->error("External calendar sync feed [{$feedId}] not found.");
                return 1;
            }

            $this->info("Synchronizing feed: {$sync->name} ({$sync->url})...");
            try {
                $count = $service->syncFeed($sync);
                $this->info("✓ Successfully synced {$count} events for {$sync->name}.");
                return 0;
            } catch (\Exception $e) {
                $this->error("✗ Failed to sync feed: " . $e->getMessage());
                return 1;
            }
        }

        $this->info("Synchronizing all active external calendar feeds...");
        $total = $service->syncAllActive();
        $this->info("✓ Completed external calendar synchronization. Total events updated: {$total}.");

        return 0;
    }
}
