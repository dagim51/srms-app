<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist (AuthorizationSeeder should have run first)
        $roles = [
            'Administrator',
            'Property Manager',
            'Agent',
            'Developer',
            'Host',
            'Seller',
            'User',
        ];

        $roleModels = [];
        foreach ($roles as $roleName) {
            $roleModels[$roleName] = Role::firstOrCreate(['name' => $roleName]);
        }

        // =============================================
        // CORE ADMIN & MODERATION USERS
        // =============================================

        // 1. Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@srms.et'],
            [
                'name' => 'Sara Haile (Lead Moderator)',
                'phone' => '+251 911 000001',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
        $admin->assignRole('Administrator');

        // 2. Property Manager
        $propertyManager = User::firstOrCreate(
            ['email' => 'pm@srms.et'],
            [
                'name' => 'Mekdes Tadesse (Property Manager)',
                'phone' => '+251 911 000002',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
        $propertyManager->assignRole('Property Manager');

        // =============================================
        // AGENCY / SALES USERS
        // =============================================

        // 3. Senior Agent
        $agent = User::firstOrCreate(
            ['email' => 'agent@srms.et'],
            [
                'name' => 'Abel Kassa (Senior Agent)',
                'phone' => '+251 911 000003',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
        $agent->assignRole('Agent');

        // 4. Seller / Property Owner
        $seller = User::firstOrCreate(
            ['email' => 'seller@srms.et'],
            [
                'name' => 'Dawit Mengistu (Property Owner)',
                'phone' => '+251 911 000004',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
        $seller->assignRole('Seller');

        // 5. Host (Short Stay)
        $host = User::firstOrCreate(
            ['email' => 'host@srms.et'],
            [
                'name' => 'Hana Assefa (Short Stay Host)',
                'phone' => '+251 911 000005',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
        $host->assignRole('Host');

        // 6. Developer
        $developer = User::firstOrCreate(
            ['email' => 'developer@srms.et'],
            [
                'name' => 'Yonas Tsegaye (Developer)',
                'phone' => '+251 911 000006',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
        $developer->assignRole('Developer');

        // =============================================
        // OPERATIONS USERS
        // =============================================

        // 7. Maintenance Technician
        $tech = User::firstOrCreate(
            ['email' => 'tech@srms.et'],
            [
                'name' => 'Amanuel Tadesse (Senior Technician)',
                'phone' => '+251 911 345678',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
        $tech->assignRole('User');

        // 8. Electrician
        $electrician = User::firstOrCreate(
            ['email' => 'electrician@srms.et'],
            [
                'name' => 'Dawit Mengistu (Master Electrician)',
                'phone' => '+251 912 456789',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
        $electrician->assignRole('User');

        // 9. Cleaner Lead
        $cleaner = User::firstOrCreate(
            ['email' => 'cleaning@srms.et'],
            [
                'name' => 'Selamawit Tadesse (Housekeeper Lead)',
                'phone' => '+251 913 567890',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
        $cleaner->assignRole('User');

        // =============================================
        // TENANT USERS (for Rental domain)
        // =============================================

        $tenantsData = [
            [
                'name' => 'Abebe Kebede',
                'email' => 'abebe.kebede@example.com',
                'phone' => '+251 911 234567',
            ],
            [
                'name' => 'Sara Tadesse',
                'email' => 'sara.tadesse@example.com',
                'phone' => '+251 912 345678',
            ],
            [
                'name' => 'Yonas Alemu',
                'email' => 'yonas.alemu@example.com',
                'phone' => '+251 913 456789',
            ],
            [
                'name' => 'Meron Bekele',
                'email' => 'meron.bekele@example.com',
                'phone' => '+251 914 567890',
            ],
            [
                'name' => 'Dawit Assefa',
                'email' => 'dawit.assefa@example.com',
                'phone' => '+251 915 678901',
            ],
        ];

        foreach ($tenantsData as $tData) {
            $user = User::firstOrCreate(
                ['email' => $tData['email']],
                [
                    'name' => $tData['name'],
                    'phone' => $tData['phone'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'locale' => 'en',
                ]
            );
            $user->assignRole('User');
        }

        // =============================================
        // SERVICE PROVIDER USERS
        // =============================================

        $serviceProviders = [
            [
                'name' => 'Samuel Tekle (Plumbing Pro)',
                'email' => 'plumber@srms.et',
                'phone' => '+251 920 111222',
            ],
            [
                'name' => 'Rahel Bekele (Cleaning Services)',
                'email' => 'cleaningsvc@srms.et',
                'phone' => '+251 920 333444',
            ],
            [
                'name' => 'Tesfaye Alemu (HVAC Specialist)',
                'email' => 'hvac@srms.et',
                'phone' => '+251 920 555666',
            ],
        ];

        foreach ($serviceProviders as $spData) {
            $user = User::firstOrCreate(
                ['email' => $spData['email']],
                [
                    'name' => $spData['name'],
                    'phone' => $spData['phone'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'locale' => 'en',
                ]
            );
            $user->assignRole('User');
        }

        // =============================================
        // ORGANIZATION MEMBER USERS
        // =============================================

        $orgUsers = [
            [
                'name' => 'Bethlehem Alemu (Skyline Sales Lead)',
                'email' => 'sales@skylinedevelopers.et',
                'phone' => '+251 911 888999',
            ],
            [
                'name' => 'Daniel Kebede (Project Coordinator)',
                'email' => 'projects@skylinedevelopers.et',
                'phone' => '+251 911 777888',
            ],
        ];

        foreach ($orgUsers as $ouData) {
            $user = User::firstOrCreate(
                ['email' => $ouData['email']],
                [
                    'name' => $ouData['name'],
                    'phone' => $ouData['phone'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'locale' => 'en',
                ]
            );
            $user->assignRole('User');
        }

        // =============================================
        // FINANCE / ACCOUNTING USERS
        // =============================================

        $financeUser = User::firstOrCreate(
            ['email' => 'finance@srms.et'],
            [
                'name' => 'Eden Teshome (Finance Officer)',
                'phone' => '+251 911 444555',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
        $financeUser->assignRole('User');

        // =============================================
        // RESIDENT / GENERAL USER
        // =============================================

        $resident = User::firstOrCreate(
            ['email' => 'resident@srms.et'],
            [
                'name' => 'Abebe Kebede (Resident)',
                'phone' => '+251 919 888777',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'locale' => 'en',
            ]
        );
        $resident->assignRole('User');

        $this->command?->info('Demo users seeded: Admin, PM, Agent, Seller, Host, Developer, Tech, Electrician, Cleaner, 5 Tenants, 3 Service Providers, 2 Org Members, Finance, Resident');
    }
}
