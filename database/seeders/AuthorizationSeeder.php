<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AuthorizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'User',
            'Seller',
            'Host',
            'Agent',
            'Developer',
            'Property Manager',
            'Administrator',
        ];

        $roleModels = [];
        foreach ($roles as $roleName) {
            $roleModels[$roleName] = Role::firstOrCreate(['name' => $roleName]);
        }

        $allPermissions = [
            'properties.view' => 'View Properties and details',
            'properties.create' => 'Draft new residential/commercial properties',
            'properties.edit' => 'Modify property specifications, media, and features',
            'properties.delete' => 'Archive or remove properties from catalog',
            'properties.gis' => 'Edit spatial boundaries, polygons, and coordinates',
            'listings.submit' => 'Publish listings for sale, rent, or lease',
            'listings.moderate' => 'Approve, reject, or request revisions on pending listings',
            'listings.feature' => 'Assign premium promotional badges on marketplace',
            'listings.expire' => 'Manually delist properties or mark them sold/rented',
            'finance.ledger.view' => 'Audit ledger account balances and journal entries',
            'finance.invoices.create' => 'Issue tax-compliant invoices and payment demands',
            'finance.escrow.release' => 'Authorize release of held escrow funds upon verified handover',
            'finance.payouts.manage' => 'Disburse payments via Telebirr, CBE Birr, or bank wires',
            'leases.create' => 'Create residential and commercial lease agreements',
            'leases.sign' => 'Execute legal digital signatures on contracts',
            'leases.terminate' => 'Conclude tenancies, conduct checkouts, and process deposits',
            'maintenance.manage' => 'Assign work orders and contractors for unit repairs',
            'kyc.verify' => 'Review national IDs, TINs, and municipal land titles',
            'disputes.arbitrate' => 'Intervene and resolve buyer-seller transaction claims',
            'fraud.flag' => 'Freeze compromised accounts and blacklist bad actors',
            'users.manage' => 'Provision accounts, invite members, and reset passwords',
            'roles.assign' => 'Modify role capabilities and custom user overrides',
            'settings.update' => 'Configure fee structures, tax rates, and integrations',
            'audit.view' => 'Inspect administrative audit trails and security events',
            'manage_locations' => 'Create and modify administrative geographical regions',
            'admin' => 'Full administrative access and disputes arbitration',
        ];

        $permissionModels = [];
        foreach ($allPermissions as $code => $description) {
            $permissionModels[$code] = Permission::firstOrCreate(
                ['code' => $code],
                ['description' => $description]
            );
        }

        // Define Role Capability Mappings
        $rolePermissions = [
            'Administrator' => array_keys($allPermissions),
            'Property Manager' => [
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit', 'listings.expire',
                'finance.invoices.create', 'leases.create', 'leases.sign',
                'leases.terminate', 'maintenance.manage', 'kyc.verify',
            ],
            'Agent' => [
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit', 'leases.create',
            ],
            'Developer' => [
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit', 'listings.feature', 'finance.invoices.create',
                'leases.create', 'leases.sign',
            ],
            'Host' => [
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit',
            ],
            'Seller' => [
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit',
            ],
            'User' => [
                'properties.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $codes) {
            if (isset($roleModels[$roleName])) {
                $pIds = collect($codes)->map(fn ($c) => $permissionModels[$c]->id)->all();
                $roleModels[$roleName]->permissions()->syncWithoutDetaching($pIds);
            }
        }
    }
}
