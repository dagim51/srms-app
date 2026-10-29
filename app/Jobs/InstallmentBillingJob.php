<?php

namespace App\Jobs;

use App\Domains\Development\Models\DevelopmentBuilding;
use App\Domains\Development\Models\DevelopmentProject;
use App\Domains\Development\Models\DevelopmentUnit;
use App\Domains\Development\Models\HandoverRecord;
use App\Domains\Development\Models\PaymentPlan;
use App\Domains\Development\Models\PaymentPlanInstallment;
use App\Domains\Transaction\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InstallmentBillingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $projectId;

    public float $milestonePercentage;

    /**
     * Create a new job instance.
     */
    public function __construct(string $projectId, float $milestonePercentage)
    {
        $this->projectId = $projectId;
        $this->milestonePercentage = $milestonePercentage;
    }

    /**
     * Execute the job.
     */
    public function handle(): int
    {
        $project = DevelopmentProject::with(['paymentPlans.installments', 'buildings.units'])->find($this->projectId);

        if (! $project) {
            Log::warning("InstallmentBillingJob: Project [{$this->projectId}] not found.");

            return 0;
        }

        $invoicesCreated = 0;

        /** @var PaymentPlan $plan */
        foreach ($project->paymentPlans as $plan) {
            /** @var PaymentPlanInstallment $installment */
            foreach ($plan->installments as $installment) {
                $threshold = (float) ($installment->percentage ?? 0);

                if ($threshold > 0 && $this->milestonePercentage >= $threshold) {
                    // Find all reserved or sold units in the project
                    /** @var DevelopmentBuilding $building */
                    foreach ($project->buildings as $building) {
                        /** @var DevelopmentUnit $unit */
                        foreach ($building->units as $unit) {
                            $buyerId = $unit->owner_user_id
                                ?? $unit->reserved_by_user_id
                                ?? HandoverRecord::where('unit_id', $unit->id)->value('buyer_id');

                            if ($buyerId) {
                                $unitPrice = (float) ($unit->price ?? 0);
                                $installmentAmount = ((float) ($installment->amount ?? 0) > 0)
                                    ? (float) $installment->amount
                                    : (($unitPrice * $threshold) / 100);

                                $invoiceNote = "Installment '{$installment->name}' ({$threshold}%) for Unit {$unit->unit_number} in Project {$project->name}";

                                $exists = Invoice::where('user_id', $buyerId)
                                    ->where('notes', $invoiceNote)
                                    ->exists();

                                if ($installmentAmount > 0) {
                                    if (! $exists) {
                                        Invoice::create([
                                            'user_id' => $buyerId,
                                            'amount' => $installmentAmount,
                                            'due_date' => now()->addDays($installment->due_days_after_reservation ?? 30)->toDateString(),
                                            'status' => 'pending',
                                            'notes' => $invoiceNote,
                                        ]);

                                        $invoicesCreated++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        Log::info("InstallmentBillingJob: Created {$invoicesCreated} installment invoices for project [{$project->name}].");

        return $invoicesCreated;
    }
}
