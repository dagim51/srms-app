<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\RentSchedule;
use Illuminate\Console\Command;

class GenerateRentInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'srms:generate-rent-invoices {--days=7 : Days ahead to generate invoices for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for rent schedules due within the target horizon';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $horizonDate = now()->addDays($days)->toDateString();

        $this->info("Scanning for unpaid rent schedules due by {$horizonDate}...");

        $schedules = RentSchedule::with(['lease.tenantProfile.user'])
            ->where('status', 'unpaid')
            ->where('due_date', '<=', $horizonDate)
            ->get();

        $generatedCount = 0;

        foreach ($schedules as $schedule) {
            $user = $schedule->lease?->tenantProfile?->user;
            if (! $user) {
                continue;
            }

            // Check if an invoice was already generated for this schedule
            $existingInvoice = Invoice::where('user_id', $user->id)
                ->where('due_date', $schedule->due_date)
                ->where('notes', 'like', "%Schedule #{$schedule->id}%")
                ->exists();

            if (! $existingInvoice) {
                Invoice::create([
                    'user_id' => $user->id,
                    'amount' => $schedule->amount,
                    'due_date' => $schedule->due_date,
                    'status' => 'sent',
                    'notes' => "Automated rent invoice for Lease #{$schedule->lease_id} (Schedule #{$schedule->id})",
                ]);
                $generatedCount++;
            }
        }

        $this->info("Generated {$generatedCount} rent invoice(s).");

        return Command::SUCCESS;
    }
}
