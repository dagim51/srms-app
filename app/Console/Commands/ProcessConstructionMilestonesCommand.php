<?php

namespace App\Console\Commands;

use App\Domains\Development\Models\PaymentPlan;
use App\Domains\Development\Models\PaymentPlanInstallment;
use App\Jobs\InstallmentBillingJob;
use App\Models\DevelopmentProject;
use Illuminate\Console\Command;

class ProcessConstructionMilestonesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'srms:check-milestones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit development project construction milestones and flag installment billing readiness';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Auditing development project milestones...');

        $projects = DevelopmentProject::with(['paymentPlans.installments', 'organization'])->get();
        $auditedCount = 0;
        $triggeredMilestones = 0;

        foreach ($projects as $project) {
            $auditedCount++;
            $progress = (float) ($project->progress_percentage ?? 0);

            $this->line("Project: [{$project->name}] Current Progress: {$progress}%");

            if ($progress > 0) {
                // Dispatch installment billing job to release due milestone invoices
                InstallmentBillingJob::dispatchSync($project->id, $progress);
            }

            /** @var PaymentPlan $plan */
            foreach ($project->paymentPlans as $plan) {
                /** @var PaymentPlanInstallment $installment */
                foreach ($plan->installments as $installment) {
                    // Milestone installment unlocked if progress reached
                    $requiredThreshold = (float) $installment->percentage;
                    if ($progress >= $requiredThreshold) {
                        $triggeredMilestones++;
                        $this->info(" -> Milestone Reached: Installment '{$installment->name}' ({$requiredThreshold}%) is ready for disbursement/billing.");
                    }
                }
            }
        }

        $this->info("Audit complete: {$auditedCount} projects audited, {$triggeredMilestones} installment milestone conditions verified.");

        return Command::SUCCESS;
    }
}
