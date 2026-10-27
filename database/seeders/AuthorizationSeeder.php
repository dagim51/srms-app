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
            'Administrator',
            'Buyer',
            'Tenant',
            'Guest / Traveler',
            'Property Owner',
            'Host',
            'Landlord',
            'Agent',
            'Real Estate Company',
            'Developer',
            'Property Manager',
            'Professional Service Provider',
            'Moderator',
            'Verification Officer',
            'Finance Officer',
            'Customer Support',
            // Legacy aliases for backward compatibility with existing tests
            'User',
            'Seller',
        ];

        $roleModels = [];
        foreach ($roles as $roleName) {
            $roleModels[$roleName] = Role::firstOrCreate(['name' => $roleName]);
        }

        $allPermissions = [
            // Properties & Inventory
            'properties.view' => 'View Properties and details',
            'properties.create' => 'Draft new residential/commercial properties',
            'properties.edit' => 'Modify property specifications, media, and features',
            'properties.delete' => 'Archive or remove properties from catalog',
            'properties.gis' => 'Edit spatial boundaries, polygons, and coordinates',
            'units.manage' => 'Manage individual development units, phases, and inventory',

            // Marketplace & Listings
            'listings.submit' => 'Publish listings for sale, rent, or lease',
            'listings.moderate' => 'Approve, reject, or request revisions on pending listings',
            'listings.feature' => 'Assign premium promotional badges on marketplace',
            'listings.expire' => 'Manually delist properties or mark them sold/rented',

            // Short-Stay & Hospitality
            'accommodations.manage' => 'Manage short-stay units, rates, rules, and amenities',
            'bookings.create' => 'Book accommodations and short-stay properties',
            'bookings.manage' => 'Manage bookings, check-in, check-out, and guest reservations',

            // Tenancies, Leases & Maintenance
            'leases.view' => 'View residential and commercial lease agreements',
            'leases.create' => 'Create residential and commercial lease agreements',
            'leases.sign' => 'Execute legal digital signatures on contracts',
            'leases.terminate' => 'Conclude tenancies, conduct checkouts, and process deposits',
            'maintenance.request' => 'Submit maintenance and repair requests for occupied units',
            'maintenance.manage' => 'Assign work orders and contractors for unit repairs',

            // Developments & Projects
            'developments.manage' => 'Create and configure off-plan real estate development projects',
            'handovers.manage' => 'Track construction milestones and buyer handover records',

            // Agency & CRM
            'agency.manage' => 'Manage real estate company branches, agent roster, and brand settings',
            'crm.leads.manage' => 'Manage client leads, deal pipelines, and client inquiries',
            'commissions.view' => 'View and audit commission splits and deal payouts',

            // Professional Services
            'services.provide' => 'Offer legal conveyancing, surveying, architectural, valuation, or inspection services',
            'inspections.manage' => 'Conduct physical inspections, upload condition reports, and certify compliance',

            // Verification, Documents & KYC
            'kyc.verify' => 'Review national IDs, TINs, and municipal land titles',
            'documents.verify' => 'Verify digital title deeds, ownership documents, and developer licenses',

            // Escrow, Ledger & Finance
            'finance.ledger.view' => 'Audit ledger account balances and journal entries',
            'finance.invoices.create' => 'Issue tax-compliant invoices and payment demands',
            'finance.escrow.release' => 'Authorize release of held escrow funds upon verified handover',
            'finance.payouts.manage' => 'Disburse payments via Telebirr, CBE Birr, or bank wires',

            // Moderation, Disputes & Support
            'support.tickets' => 'Handle customer support tickets, inquiries, and technical issues',
            'disputes.arbitrate' => 'Intervene and resolve buyer-seller transaction claims',
            'fraud.flag' => 'Freeze compromised accounts and blacklist bad actors',

            // System Administration
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
            'Buyer' => [
                'properties.view',
                'leases.view',
                'bookings.create',
                'maintenance.request',
            ],
            'Tenant' => [
                'properties.view',
                'leases.view',
                'leases.sign',
                'maintenance.request',
            ],
            'Guest / Traveler' => [
                'properties.view',
                'bookings.create',
            ],
            'Property Owner' => [
                'properties.view', 'properties.create', 'properties.edit', 'properties.delete',
                'listings.submit', 'leases.view',
            ],
            'Host' => [
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit', 'accommodations.manage', 'bookings.manage',
            ],
            'Landlord' => [
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit', 'leases.view', 'leases.create', 'leases.sign',
                'leases.terminate', 'maintenance.manage',
            ],
            'Agent' => [
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit', 'leases.view', 'leases.create',
                'crm.leads.manage', 'commissions.view',
            ],
            'Real Estate Company' => [
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit', 'agency.manage', 'crm.leads.manage', 'commissions.view',
                'leases.create', 'finance.invoices.create',
            ],
            'Developer' => [
                'properties.view', 'properties.create', 'properties.edit',
                'developments.manage', 'units.manage', 'handovers.manage',
                'listings.submit', 'listings.feature', 'finance.invoices.create',
                'leases.create', 'leases.sign',
            ],
            'Property Manager' => [
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit', 'listings.expire',
                'finance.invoices.create', 'leases.view', 'leases.create', 'leases.sign',
                'leases.terminate', 'maintenance.manage', 'kyc.verify',
            ],
            'Professional Service Provider' => [
                'properties.view',
                'services.provide',
                'inspections.manage',
            ],
            'Moderator' => [
                'properties.view',
                'listings.moderate',
                'listings.expire',
                'fraud.flag',
            ],
            'Verification Officer' => [
                'properties.view',
                'kyc.verify',
                'documents.verify',
            ],
            'Finance Officer' => [
                'finance.ledger.view',
                'finance.invoices.create',
                'finance.escrow.release',
                'finance.payouts.manage',
                'audit.view',
            ],
            'Customer Support' => [
                'properties.view',
                'support.tickets',
                'disputes.arbitrate',
                'fraud.flag',
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
