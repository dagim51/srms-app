<?php

namespace Database\Seeders;

use App\Models\DevelopmentBuilding;
use App\Models\DevelopmentProject;
use App\Models\DevelopmentUnit;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\PaymentPlan;
use App\Models\PaymentPlanInstallment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeveloperSalesSeeder extends Seeder
{
    public function run(): void
    {
        // Get developer and org users (created by DemoUserSeeder)
        $developer = User::where('email', 'developer@srms.et')->first();
        $salesUser = User::where('email', 'sales@skylinedevelopers.et')->first();
        $projectCoord = User::where('email', 'projects@skylinedevelopers.et')->first();

        if (! $developer || ! $salesUser || ! $projectCoord) {
            return;
        }

        // 1. Ensure an organization exists for Developer
        $org = Organization::firstOrCreate(
            ['slug' => 'skyline-developers'],
            [
                'name' => 'Skyline Real Estate Developers',
                'status' => 'active',
                'settings' => [
                    'type' => 'developer',
                    'email' => 'sales@skylinedevelopers.et',
                    'phone' => '+251 911 888999',
                ],
                'created_by' => $developer->id,
            ]
        );

        // Add org members
        OrganizationMember::firstOrCreate(
            ['organization_id' => $org->id, 'user_id' => $developer->id],
            ['status' => 'active', 'created_by' => $developer->id]
        );
        OrganizationMember::firstOrCreate(
            ['organization_id' => $org->id, 'user_id' => $salesUser->id],
            ['status' => 'active', 'created_by' => $developer->id]
        );
        OrganizationMember::firstOrCreate(
            ['organization_id' => $org->id, 'user_id' => $projectCoord->id],
            ['status' => 'active', 'created_by' => $developer->id]
        );

        // 2. Create Master Development Project
        $project = DevelopmentProject::firstOrCreate(
            ['name' => 'Skyline Towers'],
            [
                'organization_id' => $org->id,
                'description' => 'Ultra-modern twin residential towers with high-end luxury finishing, infinity pool, fitness center, and smart home automation in Bole Medhanialem.',
                'status' => 'under_construction',
                'handover_date' => now()->addMonths(14)->toDateString(),
                'progress_percentage' => 68,
            ]
        );

        // 3. Create Buildings
        $buildingA = DevelopmentBuilding::firstOrCreate(
            [
                'development_project_id' => $project->id,
                'name' => 'Tower A',
            ],
            [
                'total_floors' => 18,
                'status' => 'under_construction',
                'progress_percentage' => 70,
            ]
        );

        $buildingB = DevelopmentBuilding::firstOrCreate(
            [
                'development_project_id' => $project->id,
                'name' => 'Tower B',
            ],
            [
                'total_floors' => 15,
                'status' => 'under_construction',
                'progress_percentage' => 65,
            ]
        );

        // 4. Create Payment Plans
        $plan = PaymentPlan::firstOrCreate(
            [
                'development_project_id' => $project->id,
                'name' => 'Standard 30/70 Plan',
            ],
            [
                'description' => '30% down payment upon reservation and 70% in staged milestone installments.',
            ]
        );

        PaymentPlanInstallment::firstOrCreate(
            ['payment_plan_id' => $plan->id, 'name' => 'Down Payment (30%)'],
            ['percentage' => 30.00, 'due_days_after_reservation' => 0]
        );
        PaymentPlanInstallment::firstOrCreate(
            ['payment_plan_id' => $plan->id, 'name' => 'Foundation Stage (20%)'],
            ['percentage' => 20.00, 'due_days_after_reservation' => 90]
        );
        PaymentPlanInstallment::firstOrCreate(
            ['payment_plan_id' => $plan->id, 'name' => 'Structural Completion (20%)'],
            ['percentage' => 20.00, 'due_days_after_reservation' => 180]
        );
        PaymentPlanInstallment::firstOrCreate(
            ['payment_plan_id' => $plan->id, 'name' => 'Handover & Key Delivery (30%)'],
            ['percentage' => 30.00, 'due_days_after_reservation' => 365]
        );

        // 5. Create Units
        $properties = Property::all();
        $unitsData = [
            ['building' => $buildingA, 'floor' => 'Floor 1', 'number' => 'A-101', 'price' => 8500000, 'status' => 'available'],
            ['building' => $buildingA, 'floor' => 'Floor 1', 'number' => 'A-102', 'price' => 12000000, 'status' => 'reserved'],
            ['building' => $buildingA, 'floor' => 'Floor 2', 'number' => 'A-201', 'price' => 8600000, 'status' => 'contracted'],
            ['building' => $buildingA, 'floor' => 'Floor 2', 'number' => 'A-202', 'price' => 12200000, 'status' => 'sold'],
            ['building' => $buildingA, 'floor' => 'Floor 3', 'number' => 'A-301', 'price' => 25000000, 'status' => 'blocked'],
            ['building' => $buildingB, 'floor' => 'Floor 1', 'number' => 'B-101', 'price' => 9200000, 'status' => 'available'],
            ['building' => $buildingB, 'floor' => 'Floor 2', 'number' => 'B-201', 'price' => 9400000, 'status' => 'available'],
        ];

        foreach ($unitsData as $idx => $u) {
            $prop = $properties[$idx % $properties->count()] ?? null;
            DevelopmentUnit::firstOrCreate(
                [
                    'development_building_id' => $u['building']->id,
                    'unit_number' => $u['number'],
                ],
                [
                    'floor_name' => $u['floor'],
                    'price' => $u['price'],
                    'status' => $u['status'],
                    'property_id' => $prop?->id,
                ]
            );
        }
    }
}
