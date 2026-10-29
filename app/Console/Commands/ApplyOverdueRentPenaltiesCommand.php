<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\RentSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplyOverdueRentPenaltiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'srms:apply-rent-penalties {--grace-days=5 : Days grace period allowed after due date} {--penalty-pct=5 : Percentage penalty to apply}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan overdue rent invoices past grace period and apply localized late fee penalties';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $graceDays = (int) $this->option('grace-days');
        $penaltyPct = (float) $this->option('penalty-pct');
        $cutoffDate = now()->subDays($graceDays)->toDateString();

        $this->info("Scanning for rent invoices overdue before {$cutoffDate} (Grace period: {$graceDays} days)...");

        $overdueInvoices = Invoice::whereIn('status', ['sent', 'pending', 'unpaid'])
            ->where('due_date', '<', $cutoffDate)
            ->where(function ($q) {
                $q->where('notes', 'like', '%rent%')
                    ->orWhere('notes', 'like', '%Lease%')
                    ->orWhere('notes', 'like', '%Schedule%');
            })
            ->where('notes', 'not like', '%[Late penalty applied]%')
            ->get();

        $penalizedCount = 0;

        foreach ($overdueInvoices as $invoice) {
            DB::transaction(function () use ($invoice, $penaltyPct, &$penalizedCount) {
                $originalAmount = (float) $invoice->amount;
                $penaltyAmount = round($originalAmount * ($penaltyPct / 100), 2);
                $newTotal = $originalAmount + $penaltyAmount;

                $invoice->update([
                    'amount' => $newTotal,
                    'status' => 'overdue',
                    'notes' => trim($invoice->notes.' '."[Late penalty applied: +{$penaltyAmount} ETB ({$penaltyPct}%) on ".now()->toDateString().']'),
                ]);

                // Update associated RentSchedule if referenced
                if (preg_match('/Schedule #([a-f0-9\-]+)/i', $invoice->notes, $matches)) {
                    $scheduleId = $matches[1];
                    RentSchedule::where('id', $scheduleId)->update([
                        'status' => 'overdue',
                    ]);
                }

                $penalizedCount++;
                Log::warning("Applied {$penaltyPct}% overdue penalty ({$penaltyAmount} ETB) to Invoice [{$invoice->id}]. New total: {$newTotal} ETB.");
            });
        }

        $this->info("Applied overdue penalties to {$penalizedCount} invoices.");

        return Command::SUCCESS;
    }
}
