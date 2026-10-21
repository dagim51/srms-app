<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModerationUserSeeder extends Seeder
{
    public function run(): void
    {
        // Users are now created by DemoUserSeeder
        // This seeder just sets up moderation states on existing listings

        $admin = User::where('email', 'admin@srms.et')->first();
        $seller = User::where('email', 'seller@srms.et')->first();
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $sellerRole = Role::firstOrCreate(['name' => 'Seller']);

        // Ensure roles are assigned (idempotent)
        if ($admin) {
            $admin->assignRole('Administrator');
        }
        if ($seller) {
            $seller->assignRole('Seller');
        }

        // Get all listings
        $listings = Listing::with('property')->get();

        if ($listings->count() >= 3 && $seller && $admin) {
            // First listing: published
            $listings[0]->update([
                'status' => 'published',
                'published_at' => now(),
                'owner_actor_id' => $seller->id,
            ]);

            // Second listing: pending_review (with submission request)
            $listings[1]->update([
                'status' => 'pending_review',
                'owner_actor_id' => $seller->id,
            ]);

            // Third listing: rejected with moderation reason
            $listings[2]->update([
                'status' => 'rejected',
                'owner_actor_id' => $seller->id,
            ]);
            if ($listings[2]->moderations()->count() === 0) {
                $listings[2]->moderations()->create([
                    'moderator_id' => $admin->id,
                    'action' => 'reject',
                    'comments' => 'The asking price appears disproportionate to the stated building area, and high-resolution photos of the interior bathrooms are missing. Please update and resubmit.',
                ]);
            }

            if ($listings->count() >= 4) {
                // Fourth listing: pending_review
                $listings[3]->update([
                    'status' => 'pending_review',
                    'owner_actor_id' => $seller->id,
                ]);
            }
        }

        $this->command?->info('Moderation states applied to listings.');
    }
}
