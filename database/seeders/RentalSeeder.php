<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Property;
use App\Models\TenantProfile;
use App\Models\Lease;
use App\Models\RentSchedule;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RentalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure tenant users exist
        $tenantsData = [
            [
                'name' => 'Abebe Kebede',
                'email' => 'abebe.kebede@example.com',
                'phone' => '+251 911 234567',
                'status' => 'active',
                'balance' => 0,
            ],
            [
                'name' => 'Sara Tadesse',
                'email' => 'sara.tadesse@example.com',
                'phone' => '+251 912 345678',
                'status' => 'active',
                'balance' => 25000,
            ],
            [
                'name' => 'Yonas Alemu',
                'email' => 'yonas.alemu@example.com',
                'phone' => '+251 913 456789',
                'status' => 'notice',
                'balance' => 0,
            ],
            [
                'name' => 'Meron Bekele',
                'email' => 'meron.bekele@example.com',
                'phone' => '+251 914 567890',
                'status' => 'active',
                'balance' => 0,
            ],
            [
                'name' => 'Dawit Assefa',
                'email' => 'dawit.assefa@example.com',
                'phone' => '+251 915 678901',
                'status' => 'pending',
                'balance' => 0,
            ],
        ];

        $properties = Property::all();
        if ($properties->isEmpty()) {
            return;
        }

        foreach ($tenantsData as $idx => $tData) {
            $user = User::firstOrCreate(
                ['email' => $tData['email']],
                [
                    'name' => $tData['name'],
                    'phone' => $tData['phone'],
                    'password' => Hash::make('password123'),
                ]
            );

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
