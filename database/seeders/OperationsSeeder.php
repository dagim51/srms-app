<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Property;
use App\Models\MaintenanceTicket;
use App\Models\CleaningTask;
use Illuminate\Support\Facades\Hash;

class OperationsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure technician & cleaner users exist
        $tech = User::firstOrCreate(
            ['email' => 'tech@srms.et'],
            [
                'name' => 'Amanuel Tadesse (Senior Technician)',
                'phone' => '+251 911 345678',
                'password' => Hash::make('password123'),
            ]
        );

        $electrician = User::firstOrCreate(
            ['email' => 'electrician@srms.et'],
            [
                'name' => 'Dawit Mengistu (Master Electrician)',
                'phone' => '+251 912 456789',
                'password' => Hash::make('password123'),
            ]
        );

        $cleaner = User::firstOrCreate(
            ['email' => 'cleaning@srms.et'],
            [
                'name' => 'Selamawit Tadesse (Housekeeper Lead)',
                'phone' => '+251 913 567890',
                'password' => Hash::make('password123'),
            ]
        );

        $reporter = User::where('email', 'abebe.kebede@example.com')->first();
        if (!$reporter) {
            $reporter = User::firstOrCreate(
                ['email' => 'resident@srms.et'],
                [
                    'name' => 'Abebe Kebede (Resident)',
                    'phone' => '+251 919 888777',
                    'password' => Hash::make('password123'),
                ]
            );
        }

        $properties = Property::all();
        if ($properties->isEmpty()) {
            return;
        }

        // 2. Create Maintenance Tickets
        $ticketsData = [
            [
                'title' => 'Water Leak in Bathroom',
                'description' => 'The tenant reported a severe water leak under the master bathroom sink. Needs urgent pipe seal replacement.',
                'priority' => 'high',
                'status' => 'open',
                'assigned_to' => $tech->id,
            ],
            [
                'title' => 'AC Not Cooling in Master Bedroom',
                'description' => 'Air conditioning unit blowing ambient air instead of cold. Compressor and refrigerant need diagnosis.',
                'priority' => 'medium',
                'status' => 'in_progress',
                'assigned_to' => $tech->id,
            ],
            [
                'title' => 'Power Outlet Sparking',
                'description' => 'Kitchen heavy appliance wall socket sparking when electric stove is turned on. Hazard alert.',
                'priority' => 'critical',
                'status' => 'assigned',
                'assigned_to' => $electrician->id,
            ],
            [
                'title' => 'Broken Door Handle & Balcony Lock',
                'description' => 'Balcony sliding door latch is jammed and exterior door handle is loose.',
                'priority' => 'low',
                'status' => 'waiting',
                'assigned_to' => null,
            ],
            [
                'title' => 'Water Heater Not Heating',
                'description' => 'Solar-electric hybrid boiler thermostat replaced and certified operational.',
                'priority' => 'high',
                'status' => 'completed',
                'assigned_to' => $tech->id,
            ],
        ];

        foreach ($ticketsData as $idx => $t) {
            $prop = $properties[$idx % $properties->count()];
            MaintenanceTicket::firstOrCreate(
                [
                    'property_id' => $prop->id,
                    'title' => $t['title'],
                ],
                [
                    'description' => $t['description'],
                    'reported_by' => $reporter->id,
                    'assigned_to' => $t['assigned_to'],
                    'priority' => $t['priority'],
                    'status' => $t['status'],
                ]
            );
        }

        // 3. Create Cleaning Tasks
        $cleaningData = [
            ['notes' => 'Checkout Cleaning - Full sanitization and linen replacement for incoming guest.', 'status' => 'pending'],
            ['notes' => 'Routine Maintenance Deep Clean - Living room, balcony, and kitchen exhaust hood.', 'status' => 'assigned'],
            ['notes' => 'Deep Clean - Post-monsoon window exterior and floor polishing.', 'status' => 'in_progress'],
            ['notes' => 'Post-Construction Snagging & Dust Removal.', 'status' => 'inspection'],
            ['notes' => 'Turnover Cleaning completed and approved for guest arrival.', 'status' => 'ready'],
        ];

        foreach ($cleaningData as $idx => $c) {
            $prop = $properties[$idx % $properties->count()];
            CleaningTask::firstOrCreate(
                [
                    'property_id' => $prop->id,
                    'notes' => $c['notes'],
                ],
                [
                    'assigned_cleaner_id' => $cleaner->id,
                    'inspector_id' => $tech->id,
                    'status' => $c['status'],
                ]
            );
        }
    }
}
