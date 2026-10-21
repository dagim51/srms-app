<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentSchedule;
use App\Models\TenantProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RentalSeeder extends Seeder
{
    public function run(): void
    {
        // Tenant data with lease status and balance (users created by DemoUserSeeder)
        $tenantsData = [
            [
                'email' => 'abebe.kebede@example.com',
                'status' => 'active',
                'balance' => 0,
            ],
            [
                'email' => 'sara.tadesse@example.com',
                'status' => 'active',
                'balance' => 25000,
            ],
            [
                'email' => 'yonas.alemu@example.com',
                'status' => 'notice',
                'balance' => 0,
            ],
            [
                'email' => 'meron.bekele@example.com',
                'status' => 'active',
                'balance' => 0,
            ],
            [
                'email' => 'dawit.assefa@example.com',
                'status' => 'pending',
                'balance' => 0,
            ],
        ];

        $properties = Property::all();
        if ($properties->isEmpty()) {
            return;
        }

        foreach ($tenantsData as $idx => $tData) {
            $user = User::where('email', $tData['email'])->first();
            if (! $user) {
                continue;
            }

            $tenantProfile = TenantProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employment_status' => 'Employed Full-Time',
                    'annual_income' => 750000,
                    'background_check_status' => 'verified',
                ]
            );

            $property = $properties[$idx % $properties->count()];
            $monthlyRent = 25000 + ($idx * 5000);

            $lease = Lease::firstOrCreate(
                [
                    'property_id' => $property->id,
                    'tenant_profile_id' => $tenantProfile->id,
                ],
                [
                    'start_date' => Carbon::now()->subMonths(3)->toDateString(),
                    'end_date' => Carbon::now()->addMonths(9)->toDateString(),
                    'rent_amount' => $monthlyRent,
                    'deposit_amount' => $monthlyRent * 2,
                    'status' => $tData['status'],
                ]
            );

            // Generate monthly rent schedules
            for ($m = -2; $m <= 3; $m++) {
                $dueDate = Carbon::now()->startOfMonth()->addMonths($m)->toDateString();
                $isPast = $m < 0;
                $isCurrent = $m === 0;

                $status = 'pending';
                if ($isPast) {
                    $status = 'paid';
                } elseif ($isCurrent && $tData['balance'] > 0) {
                    $status = 'overdue';
                }

                RentSchedule::firstOrCreate(
                    [
                        'lease_id' => $lease->id,
                        'due_date' => $dueDate,
                    ],
                    [
                        'amount' => $monthlyRent,
                        'status' => $status,
                    ]
                );
            }
        }
    }
}
