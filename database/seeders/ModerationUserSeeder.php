<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Listing;
use Illuminate\Support\Facades\Hash;

class ModerationUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $sellerRole = Role::firstOrCreate(['name' => 'Seller']);

        // 1. Create or get Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@srms.et'],
            [
                'name' => 'Sara Haile (Lead Moderator)',
                'password' => Hash::make('password123'),
            ]
        );
        $admin->assignRole('Administrator');

        // 2. Create or get Seller
        $seller = User::firstOrCreate(
            ['email' => 'seller@srms.et'],
            [
                'name' => 'Dawit Mengistu (Property Owner)',
                'password' => Hash::make('password123'),
            ]
        );
        $seller->assignRole('Seller');

        // 3. Make sure we have at least 2 listings in pending_review and 1 rejected for testing moderation queue
        $listings = Listing::with('property')->get();
        if ($listings->count() >= 3) {
            // First listing: published
            $listings[0]->update(['status' => 'published', 'published_at' => now()]);

            // Second listing: pending_review (with submission request)
            $listings[1]->update(['status' => 'pending_review', 'owner_actor_id' => $seller->id]);

            // Third listing: rejected with moderation reason
            $listings[2]->update(['status' => 'rejected', 'owner_actor_id' => $seller->id]);
            if ($listings[2]->moderations()->count() === 0) {
                $listings[2]->moderations()->create([
                    'moderator_id' => $admin->id,
                    'action' => 'reject',
                    'comments' => 'The asking price appears disproportionate to the stated building area, and high-resolution photos of the interior bathrooms are missing. Please update and resubmit.',
                ]);
            }

            if ($listings->count() >= 4) {
                // Fourth listing: pending_review
                $listings[3]->update(['status' => 'pending_review', 'owner_actor_id' => $seller->id]);
            }
        }
    }
}
