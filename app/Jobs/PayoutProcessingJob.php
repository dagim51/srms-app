<?php

namespace App\Jobs;

use App\Domains\Transaction\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayoutProcessingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): int
    {
        $pendingPayouts = Payout::where('status', 'pending')
            ->with('account')
            ->get();

        $processedCount = 0;

        foreach ($pendingPayouts as $payout) {
            $bankName = $payout->account?->bank_name ?? 'Bank';
            $ref = 'PO-ETB-'.date('Ymd').'-'.strtoupper(Str::random(8));

            $payout->update([
                'status' => 'completed',
                'reference_number' => $ref,
                'processed_at' => now(),
                'notes' => "Settlement processed via {$bankName} transfer [Ref: {$ref}]",
            ]);

            $processedCount++;
        }

        Log::info("PayoutProcessingJob: Successfully processed {$processedCount} settlements.");

        return $processedCount;
    }
}
