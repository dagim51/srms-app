<?php

namespace App\Domains\Transaction\Services;

use App\Domains\Transaction\Models\LedgerAccount;
use App\Domains\Transaction\Models\LedgerEntry;
use App\Domains\Transaction\Models\LedgerEntryLine;
use App\Domains\Transaction\Models\Payment;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LedgerPostingService
{
    /**
     * Post balanced double-entry journal lines for a completed payment settlement.
     */
    public function postPaymentSettlement(Payment $payment, ?array $distribution = null): LedgerEntry
    {
        return DB::transaction(function () use ($payment, $distribution) {
            // Prevent duplicate entries for the same payment
            $existing = LedgerEntry::where('reference_id', $payment->id)
                ->orWhere('reference_id', $payment->transaction_reference)
                ->first();

            if ($existing) {
                return $existing;
            }

            $payableClass = class_basename($payment->payable_type ?? '');
            $amount = round(abs((float) $payment->amount), 2);

            $entry = LedgerEntry::create([
                'description' => "Settlement for Payment #{$payment->id} ({$payableClass} #{$payment->payable_id})",
                'reference_id' => $payment->id,
                'entry_date' => now()->toDateString(),
            ]);

            // 1. Debit Asset Account (Gateway Clearing / Cash increases)
            $assetCode = match ($payment->payment_method) {
                'telebirr' => '1020',
                default => '1010',
            };
            $assetName = ($assetCode === '1020')
                ? 'Cash - Telebirr Merchant Account'
                : 'Cash - Commercial Bank of Ethiopia';

            $assetAccount = LedgerAccount::firstOrCreate(
                ['code' => $assetCode],
                ['name' => $assetName, 'type' => 'Asset']
            );

            LedgerEntryLine::create([
                'ledger_entry_id' => $entry->id,
                'ledger_account_id' => $assetAccount->id,
                'type' => 'debit',
                'amount' => $amount,
            ]);

            // 2. Credits (Split distribution or Default Revenue)
            $itemized = $distribution ?? ($payment->metadata['split_distribution'] ?? null);

            if (is_array($itemized) && ! empty($itemized)) {
                $this->applyItemizedCredits($entry, $itemized, $amount);
            } else {
                $this->applyDefaultRevenueCredit($entry, $payableClass, $amount);
            }

            // 3. Strict balancing verification: Debits must equal Credits
            $this->assertBalanced($entry);

            Log::info("Posted balanced double-entry journal for Payment [{$payment->id}]", [
                'entry_id' => $entry->id,
                'amount' => $amount,
            ]);

            return $entry;
        });
    }

    /**
     * Post balanced double-entry journal lines for a payment refund.
     */
    public function postRefund(Payment $refund, Payment $originalPayment, float $amount): LedgerEntry
    {
        return DB::transaction(function () use ($refund, $originalPayment, $amount) {
            $amount = round(abs($amount), 2);

            $entry = LedgerEntry::create([
                'description' => "Refund for Payment #{$originalPayment->id} (Refund Ref: {$refund->transaction_reference})",
                'reference_id' => $refund->id,
                'entry_date' => now()->toDateString(),
            ]);

            // 1. Debit Revenue / Escrow Liability (Contra-revenue / Liability reduction)
            $payableClass = class_basename($originalPayment->payable_type ?? '');
            $liabilityCode = match ($payableClass) {
                'AccommodationBooking' => '2030', // Host Escrow
                default => '2050', // Refunds & Payouts Payable
            };
            $liabilityName = ($liabilityCode === '2030')
                ? 'Host Funds Held in Escrow'
                : 'Refunds & Payouts Payable';

            $debitAccount = LedgerAccount::firstOrCreate(
                ['code' => $liabilityCode],
                ['name' => $liabilityName, 'type' => 'Liability']
            );

            LedgerEntryLine::create([
                'ledger_entry_id' => $entry->id,
                'ledger_account_id' => $debitAccount->id,
                'type' => 'debit',
                'amount' => $amount,
            ]);

            // 2. Credit Asset Account (Cash / Merchant account decreases)
            $assetCode = match ($originalPayment->payment_method) {
                'telebirr' => '1020',
                default => '1010',
            };
            $assetName = ($assetCode === '1020')
                ? 'Cash - Telebirr Merchant Account'
                : 'Cash - Commercial Bank of Ethiopia';

            $assetAccount = LedgerAccount::firstOrCreate(
                ['code' => $assetCode],
                ['name' => $assetName, 'type' => 'Asset']
            );

            LedgerEntryLine::create([
                'ledger_entry_id' => $entry->id,
                'ledger_account_id' => $assetAccount->id,
                'type' => 'credit',
                'amount' => $amount,
            ]);

            // 3. Strict balancing verification
            $this->assertBalanced($entry);

            return $entry;
        });
    }

    /**
     * Post disbursement of host/landlord funds from Escrow.
     */
    public function postDisbursement(User $beneficiary, float $amount, string $referenceId, string $method = 'telebirr'): LedgerEntry
    {
        return DB::transaction(function () use ($beneficiary, $amount, $referenceId, $method) {
            $amount = round(abs($amount), 2);

            $entry = LedgerEntry::create([
                'description' => "Disbursement payout to User #{$beneficiary->id} ({$beneficiary->name}) - Ref: {$referenceId}",
                'reference_id' => $referenceId,
                'entry_date' => now()->toDateString(),
            ]);

            // 1. Debit Host Escrow Liability (Escrow decreases)
            $escrowAccount = LedgerAccount::firstOrCreate(
                ['code' => '2030'],
                ['name' => 'Host Funds Held in Escrow', 'type' => 'Liability']
            );

            LedgerEntryLine::create([
                'ledger_entry_id' => $entry->id,
                'ledger_account_id' => $escrowAccount->id,
                'type' => 'debit',
                'amount' => $amount,
            ]);

            // 2. Credit Bank / Outgoing Asset (Cash decreases)
            $assetCode = ($method === 'telebirr') ? '1020' : '1010';
            $assetName = ($assetCode === '1020')
                ? 'Cash - Telebirr Merchant Account'
                : 'Cash - Commercial Bank of Ethiopia';

            $assetAccount = LedgerAccount::firstOrCreate(
                ['code' => $assetCode],
                ['name' => $assetName, 'type' => 'Asset']
            );

            LedgerEntryLine::create([
                'ledger_entry_id' => $entry->id,
                'ledger_account_id' => $assetAccount->id,
                'type' => 'credit',
                'amount' => $amount,
            ]);

            // 3. Strict balancing verification
            $this->assertBalanced($entry);

            return $entry;
        });
    }

    /**
     * Apply itemized multi-party credits guaranteeing exact zero mathematical imbalance.
     */
    protected function applyItemizedCredits(LedgerEntry $entry, array $distribution, float $totalDebit): void
    {
        // 1. Host Escrow Liability Account
        if (isset($distribution['host_escrow']) && (float) $distribution['host_escrow'] > 0) {
            $escrowAccount = LedgerAccount::firstOrCreate(
                ['code' => '2030'],
                ['name' => 'Host Funds Held in Escrow', 'type' => 'Liability']
            );
            LedgerEntryLine::create([
                'ledger_entry_id' => $entry->id,
                'ledger_account_id' => $escrowAccount->id,
                'type' => 'credit',
                'amount' => round((float) $distribution['host_escrow'], 2),
            ]);
        }

        // 2. Platform Service Fee Revenue
        if (isset($distribution['platform_fee']) && (float) $distribution['platform_fee'] > 0) {
            $platformFeeAccount = LedgerAccount::firstOrCreate(
                ['code' => '4030'],
                ['name' => 'Short-Stay Accommodation Platform Fee', 'type' => 'Revenue']
            );
            LedgerEntryLine::create([
                'ledger_entry_id' => $entry->id,
                'ledger_account_id' => $platformFeeAccount->id,
                'type' => 'credit',
                'amount' => round((float) $distribution['platform_fee'], 2),
            ]);
        }

        // 3. Tax / VAT Payable Liability
        if (isset($distribution['tax']) && (float) $distribution['tax'] > 0) {
            $taxAccount = LedgerAccount::firstOrCreate(
                ['code' => '2020'],
                ['name' => 'Value Added Tax (VAT) Payable', 'type' => 'Liability']
            );
            LedgerEntryLine::create([
                'ledger_entry_id' => $entry->id,
                'ledger_account_id' => $taxAccount->id,
                'type' => 'credit',
                'amount' => round((float) $distribution['tax'], 2),
            ]);
        }

        // 4. Other custom revenue
        if (isset($distribution['other_revenue']) && (float) $distribution['other_revenue'] > 0) {
            $otherAccount = LedgerAccount::firstOrCreate(
                ['code' => '4000'],
                ['name' => 'General Operating Revenue', 'type' => 'Revenue']
            );
            LedgerEntryLine::create([
                'ledger_entry_id' => $entry->id,
                'ledger_account_id' => $otherAccount->id,
                'type' => 'credit',
                'amount' => round((float) $distribution['other_revenue'], 2),
            ]);
        }

        // Guarantee exact zero mathematical imbalance by reconciling penny difference
        $credited = (float) LedgerEntryLine::where('ledger_entry_id', $entry->id)->where('type', 'credit')->sum('amount');
        $difference = round($totalDebit - $credited, 2);

        if (abs($difference) > 0.001) {
            $adjAccount = LedgerAccount::firstOrCreate(
                ['code' => '4030'],
                ['name' => 'Short-Stay Accommodation Platform Fee', 'type' => 'Revenue']
            );
            LedgerEntryLine::create([
                'ledger_entry_id' => $entry->id,
                'ledger_account_id' => $adjAccount->id,
                'type' => 'credit',
                'amount' => $difference,
            ]);
        }
    }

    /**
     * Apply default single revenue credit when distribution is not itemized.
     */
    protected function applyDefaultRevenueCredit(LedgerEntry $entry, string $payableClass, float $amount): void
    {
        $revenueCode = match ($payableClass) {
            'AccommodationBooking' => '4030',
            'Lease', 'RentSchedule' => '4010',
            'DevelopmentUnit', 'PaymentPlanInstallment' => '4020',
            default => '4000',
        };
        $revenueName = match ($revenueCode) {
            '4030' => 'Short-Stay Accommodation Revenue',
            '4010' => 'Rental Lease Revenue',
            '4020' => 'Development Sales Commission',
            default => 'General Operating Revenue',
        };

        $revenueAccount = LedgerAccount::firstOrCreate(
            ['code' => $revenueCode],
            ['name' => $revenueName, 'type' => 'Revenue']
        );

        LedgerEntryLine::create([
            'ledger_entry_id' => $entry->id,
            'ledger_account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => $amount,
        ]);
    }

    /**
     * Assert that total debits strictly equal total credits.
     *
     * @throws DomainException
     */
    public function assertBalanced(LedgerEntry $entry): void
    {
        $debits = round((float) LedgerEntryLine::where('ledger_entry_id', $entry->id)->where('type', 'debit')->sum('amount'), 2);
        $credits = round((float) LedgerEntryLine::where('ledger_entry_id', $entry->id)->where('type', 'credit')->sum('amount'), 2);

        if (abs($debits - $credits) > 0.001) {
            throw new DomainException("Ledger entry [{$entry->id}] is out of balance! Debits: {$debits}, Credits: {$credits}.");
        }
    }
}
