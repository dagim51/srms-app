<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Listing;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Models\ListingFlag;
use App\Models\UserFlag;
use Carbon\Carbon;

class TrustSafetySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $listings = Listing::all();

        if ($users->count() < 2 || $listings->isEmpty()) {
            return;
        }

        $complainant = $users[0];
        $respondent = $users[1];
        $moderator = $users->firstWhere('email', 'admin@srms.et') ?? $complainant;

        // 1. Create Disputes
        $dispute = Dispute::firstOrCreate(
            ['subject' => 'Unfair Deposit Deduction for Apartment 405'],
            [
                'complainant_id' => $complainant->id,
                'respondent_id' => $respondent->id,
                'assigned_moderator_id' => $moderator->id,
                'disputable_type' => Listing::class,
                'disputable_id' => $listings[0]->id,
                'type' => 'deposit_dispute',
                'description' => 'Tenant claims that the 5,000 ETB deduction for painting was unjustified as the apartment was returned in pristine condition according to the move-in protocol.',
                'amount_in_dispute' => 5000.00,
                'currency' => 'ETB',
                'status' => 'escalated',
                'priority' => 'high',
                'opened_at' => Carbon::now()->subDays(3),
                'assigned_at' => Carbon::now()->subDays(2),
            ]
        );

        DisputeEvidence::firstOrCreate(
            ['dispute_id' => $dispute->id, 'title' => 'Move-out Living Room Condition Photos'],
            [
                'submitted_by' => $complainant->id,
                'type' => 'screenshot',
                'file_path' => 'evidence/move_out_living_room.jpg',
                'description' => 'High-res photos taken right before keys were handed over.',
            ]
        );

        // 2. Create Listing Flags
        ListingFlag::firstOrCreate(
            [
                'listing_id' => $listings[0]->id,
                'reporter_id' => $complainant->id,
                'reason' => 'Scam/Fraudulent Price',
            ],
            [
                'assigned_moderator_id' => $moderator->id,
                'description' => 'Listing price was advertised at 25,000 ETB, but upon contact owner demanded 45,000 ETB in cash.',
                'severity' => 'high',
                'status' => 'open',
                'reported_at' => Carbon::now()->subDays(1),
            ]
        );

        if ($listings->count() > 1) {
            ListingFlag::firstOrCreate(
                [
                    'listing_id' => $listings[1]->id,
                    'reporter_id' => $respondent->id,
                    'reason' => 'Duplicate Listing',
                ],
                [
                    'assigned_moderator_id' => $moderator->id,
                    'description' => 'Same property images and location appear under two different user accounts.',
                    'severity' => 'medium',
                    'status' => 'reviewed',
                    'reported_at' => Carbon::now()->subDays(4),
                ]
            );
        }

        // 3. Create User Flags
        UserFlag::firstOrCreate(
            [
                'user_id' => $respondent->id,
                'reporter_id' => $complainant->id,
                'reason' => 'Abusive Communication',
            ],
            [
                'assigned_moderator_id' => $moderator->id,
                'description' => 'User sent harassing messages in the negotiation chat.',
                'severity' => 'medium',
                'status' => 'open',
                'reported_at' => Carbon::now()->subDays(2),
            ]
        );

        // 4. Seed Governance Audit Logs
        \App\Models\AuditLog::firstOrCreate(
            ['event' => 'user.suspended', 'notes' => 'Suspended User USR-904 for abusive behavior'],
            [
                'actor_id' => $moderator->id,
                'auditable_type' => User::class,
                'auditable_id' => $respondent->id,
                'ip_address' => '10.0.0.5',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'new_values' => ['status' => 'suspended'],
                'old_values' => ['status' => 'active'],
            ]
        );

        \App\Models\AuditLog::firstOrCreate(
            ['event' => 'system.invoices', 'notes' => 'Generated monthly rent invoices for active tenancies'],
            [
                'actor_id' => null,
                'auditable_type' => null,
                'auditable_id' => null,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'CLI / Cron',
                'new_values' => ['generated_count' => 14],
            ]
        );
    }
}
