<?php

namespace Tests\Feature;

use App\Domains\Transaction\Models\Payout;
use App\Domains\Transaction\Models\PayoutAccount;
use App\Jobs\PayoutProcessingJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayoutSettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_payout_processing_job_settles_pending_payouts(): void
    {
        $user = User::factory()->create();

        $account = PayoutAccount::create([
            'user_id' => $user->id,
            'bank_name' => 'Commercial Bank of Ethiopia',
            'account_number' => '1000123456789',
            'account_name' => 'Abebe Kebede',
            'is_verified' => true,
        ]);

        $payout1 = Payout::create([
            'payout_account_id' => $account->id,
            'amount' => 45000.00,
            'fee' => 150.00,
            'currency' => 'ETB',
            'status' => 'pending',
        ]);

        $payout2 = Payout::create([
            'payout_account_id' => $account->id,
            'amount' => 12500.00,
            'fee' => 50.00,
            'currency' => 'ETB',
            'status' => 'pending',
        ]);

        $job = new PayoutProcessingJob;
        $count = $job->handle();

        $this->assertEquals(2, $count);

        $this->assertDatabaseHas('payouts', [
            'id' => $payout1->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('payouts', [
            'id' => $payout2->id,
            'status' => 'completed',
        ]);

        $freshPayout = $payout1->fresh();
        $this->assertNotNull($freshPayout->processed_at);
        $this->assertStringStartsWith('PO-ETB-', $freshPayout->reference_number);
    }
}
