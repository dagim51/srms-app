<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthorizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. All 17 Canonical SRS Roles + Compatibility Aliases
        $roleDefinitions = [
            'Administrator' => [
                'description' => 'Platform super-admin with global configuration, user governance, security audits, and full system capabilities.',
                'is_system' => true,
            ],
            'Buyer' => [
                'description' => 'Marketplace buyer who can save favorites, submit inquiries, request viewings, and place transaction offers.',
                'is_system' => true,
            ],
            'Tenant' => [
                'description' => 'Residential or commercial tenant managing active leases, rent records, agreements, and maintenance requests.',
                'is_system' => true,
            ],
            'Guest / Traveler' => [
                'description' => 'Authenticated traveler reserving short-stay accommodations, managing check-ins, and submitting host reviews.',
                'is_system' => true,
            ],
            'Guest' => [
                'description' => 'Unauthenticated or basic marketplace visitor exploring published listings, public photos, and map discovery.',
                'is_system' => true,
            ],
            'Property Owner' => [
                'description' => 'Individual asset owner managing properties, listings, viewing schedules, and inbound purchaser inquiries.',
                'is_system' => true,
            ],
            'Host' => [
                'description' => 'Hospitality short-stay host managing vacation rental units, dynamic pricing, house rules, and guest turnovers.',
                'is_system' => true,
            ],
            'Landlord' => [
                'description' => 'Long-term rental property owner managing tenant rosters, lease contracts, deposits, and unit repairs.',
                'is_system' => true,
            ],
            'Agent' => [
                'description' => 'Licensed real estate broker managing assigned client leads, viewing appointments, offers, and commissions.',
                'is_system' => true,
            ],
            'Real Estate Company' => [
                'description' => 'Corporate brokerage managing multiple branch offices, agent rosters, enterprise listings, and deal pipelines.',
                'is_system' => true,
            ],
            'Developer' => [
                'description' => 'Real estate development firm configuring off-plan projects, unit inventories, milestone billings, and handovers.',
                'is_system' => true,
            ],
            'Property Manager' => [
                'description' => 'Professional facility manager maintaining assigned buildings, units, tenant operations, and service work orders.',
                'is_system' => true,
            ],
            'Professional Service Provider' => [
                'description' => 'Legal conveyancer, licensed surveyor, architect, or certified property valuation inspector.',
                'is_system' => true,
            ],
            'Moderator' => [
                'description' => 'Trust and safety specialist reviewing listings, advertisements, public complaints, and reported content.',
                'is_system' => true,
            ],
            'Verification Officer' => [
                'description' => 'Compliance officer verifying Kebele national IDs, TIN certificates, business licenses, and municipal title deeds.',
                'is_system' => true,
            ],
            'Finance Officer' => [
                'description' => 'Financial accountant managing tax invoices, escrow release authorizations, disbursements, and refunds.',
                'is_system' => true,
            ],
            'Customer Support' => [
                'description' => 'Support representative handling customer help tickets, platform dispute mediation, and user inquiries.',
                'is_system' => true,
            ],
            // Backward-compatibility aliases for existing test fixtures
            'User' => [
                'description' => 'Standard authenticated platform user account.',
                'is_system' => true,
            ],
            'Seller' => [
                'description' => 'Legacy seller role for listing marketplace properties.',
                'is_system' => true,
            ],
        ];

        $roleModels = [];
        foreach ($roleDefinitions as $roleName => $meta) {
            $roleModels[$roleName] = Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'guard_name' => 'api',
                    'status' => 'active',
                    'description' => $meta['description'],
                    'is_system' => $meta['is_system'],
                ]
            );

            // Ensure is_system and description are kept up to date
            $roleModels[$roleName]->update([
                'description' => $meta['description'],
                'is_system' => $meta['is_system'],
            ]);
        }

        // 2. Granular Permission Catalog by Domain
        $allPermissions = [
            // Properties & Inventory
            'properties.view' => 'View Properties and characteristic details',
            'properties.create' => 'Draft new residential/commercial properties',
            'properties.edit' => 'Modify property specifications, media, and features',
            'properties.delete' => 'Archive or remove properties from catalog',
            'properties.gis' => 'Edit spatial boundaries, polygons, and coordinates',
            'properties.media' => 'Upload and curate property photos, floorplans, and video tours',
            'properties.analytics' => 'View property performance, view counts, and engagement metrics',
            'units.manage' => 'Manage individual development units, phases, and inventory',

            // Marketplace & Listings
            'listings.submit' => 'Publish listings for sale, rent, or lease',
            'listings.moderate' => 'Approve, reject, or request revisions on pending listings',
            'listings.feature' => 'Assign premium promotional badges on marketplace',
            'listings.expire' => 'Manually delist properties or mark them sold/rented',

            // Search & Customer Engagement
            'favorites.manage' => 'Save, categorize, and organize favorite properties',
            'properties.compare' => 'Compare side-by-side property specifications',
            'messages.send' => 'Send direct messages and inquiries to listing managers',
            'crm.leads.manage' => 'Manage client leads, deal pipelines, and client inquiries',
            'inquiries.manage' => 'Manage and respond to buyer and tenant inquiries',
            'viewings.request' => 'Request in-person or virtual property viewings',
            'viewings.manage' => 'Schedule, confirm, and manage property viewing calendars',
            'offers.submit' => 'Submit formal purchase or lease transaction offers',
            'offers.manage' => 'Review, counter, accept, or reject incoming transaction offers',
            'reviews.submit' => 'Submit ratings and reviews for hosts and stays',
            'reviews.manage' => 'Moderate and respond to customer reviews',

            // Short-Stay & Hospitality
            'accommodations.manage' => 'Manage short-stay units, rates, rules, and amenities',
            'bookings.create' => 'Book accommodations and short-stay properties',
            'bookings.manage' => 'Manage bookings, check-in, check-out, and guest reservations',
            'housekeeping.manage' => 'Schedule and coordinate cleaning and housekeeping turnovers',

            // Tenancies, Leases & Maintenance
            'rental.apply' => 'Submit tenant rental applications',
            'rental.review' => 'Review and process prospective tenant rental applications',
            'leases.view' => 'View residential and commercial lease agreements',
            'leases.create' => 'Create residential and commercial lease agreements',
            'leases.sign' => 'Execute legal digital signatures on contracts',
            'leases.terminate' => 'Conclude tenancies, conduct checkouts, and process deposits',
            'maintenance.request' => 'Submit maintenance and repair requests for occupied units',
            'maintenance.manage' => 'Assign work orders and contractors for unit repairs',

            // Developments & Projects
            'developments.manage' => 'Create and configure off-plan real estate development projects',
            'handovers.manage' => 'Track construction milestones and buyer handover records',

            // Agency & Brokerage
            'agency.manage' => 'Manage real estate company branches, agent roster, and brand settings',
            'commissions.view' => 'View and audit commission splits and deal payouts',

            // Professional Services
            'services.provide' => 'Offer legal conveyancing, surveying, architectural, valuation, or inspection services',
            'inspections.manage' => 'Conduct physical inspections, upload condition reports, and certify compliance',

            // Verification, Documents & KYC
            'documents.upload' => 'Upload transaction, identity, and property documents',
            'documents.view' => 'View documents in accordance with document access levels',
            'kyc.verify' => 'Review national IDs, TINs, and municipal land titles',
            'documents.verify' => 'Verify digital title deeds, ownership documents, and developer licenses',
            'kyc.history' => 'Inspect historical verification checks and audit trails',

            // Escrow, Ledger & Finance
            'finance.ledger.view' => 'Audit ledger account balances and journal entries',
            'finance.payments.view' => 'Inspect payment transactions and receipt logs',
            'finance.invoices.create' => 'Issue tax-compliant invoices and payment demands',
            'finance.invoices.manage' => 'Manage invoices, payment statuses, and reminders',
            'finance.refunds.process' => 'Review, approve, and disburse customer refunds',
            'finance.escrow.release' => 'Authorize release of held escrow funds upon verified handover',
            'finance.payouts.manage' => 'Disburse payments via Telebirr, CBE Birr, or bank wires',
            'finance.reconciliation' => 'Reconcile gateway disbursements against bank statements',
            'finance.reports.generate' => 'Generate accounting, VAT, and transaction financial reports',

            // Moderation, Disputes & Support
            'support.tickets' => 'Handle customer support tickets, inquiries, and technical issues',
            'support.context' => 'Access transaction and customer context needed for support resolution',
            'disputes.arbitrate' => 'Intervene and resolve buyer-seller transaction claims',
            'fraud.flag' => 'Freeze compromised accounts and blacklist bad actors',
            'moderation.actions' => 'Execute moderation actions against listings and accounts',
            'support.escalate' => 'Escalate complex cases to senior officers or legal specialists',

            // System Administration & Security
            'profile.update' => 'View and update personal user profile information',
            'profile.preferences' => 'Manage personal account preferences and notification settings',
            'profile.security' => 'View and update account credentials and 2FA security',
            'users.view' => 'View user lists and account profiles',
            'users.manage' => 'Provision accounts, update statuses, and reset passwords',
            'roles.assign' => 'Assign and revoke user roles and custom permission overrides',
            'roles.manage' => 'Create, configure, and maintain custom and system roles',
            'settings.update' => 'Configure platform parameters, fee structures, and integrations',
            'manage_locations' => 'Create and modify administrative geographical regions',
            'categories.manage' => 'Manage property categories, amenities, and listing taxonomies',
            'analytics.view' => 'Access platform-wide business analytics and KPI dashboards',
            'audit.view' => 'Inspect administrative audit trails and security events',
            'admin' => 'Full administrative access across platform systems',
        ];

        $permissionModels = [];
        foreach ($allPermissions as $code => $description) {
            $permissionModels[$code] = Permission::firstOrCreate(
                ['code' => $code],
                ['description' => $description]
            );

            // Keep descriptions fresh
            $permissionModels[$code]->update(['description' => $description]);
        }

        // 3. Principle of Least Privilege: Role Capability Mappings
        $rolePermissions = [
            'Administrator' => array_keys($allPermissions),

            'Guest' => [
                'properties.view',
                'properties.compare',
            ],

            'Buyer' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'properties.compare', 'favorites.manage',
                'messages.send', 'viewings.request', 'offers.submit',
                'leases.view', 'bookings.create', 'maintenance.request',
                'documents.upload', 'documents.view',
            ],

            'Tenant' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'favorites.manage', 'messages.send',
                'viewings.request', 'rental.apply', 'leases.view', 'leases.sign',
                'maintenance.request', 'documents.upload', 'documents.view',
            ],

            'Guest / Traveler' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'favorites.manage', 'messages.send',
                'bookings.create', 'reviews.submit',
                'documents.upload', 'documents.view',
            ],

            'Property Owner' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'properties.create', 'properties.edit', 'properties.delete',
                'properties.media', 'properties.analytics', 'listings.submit',
                'messages.send', 'inquiries.manage', 'viewings.manage',
                'offers.manage', 'leases.view', 'documents.upload', 'documents.view',
            ],

            'Host' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'properties.create', 'properties.edit',
                'properties.media', 'listings.submit', 'accommodations.manage',
                'bookings.manage', 'housekeeping.manage', 'messages.send',
                'reviews.manage', 'documents.upload', 'documents.view',
            ],

            'Landlord' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'properties.create', 'properties.edit',
                'properties.media', 'listings.submit', 'rental.review',
                'leases.view', 'leases.create', 'leases.sign', 'leases.terminate',
                'maintenance.manage', 'messages.send', 'inquiries.manage',
                'documents.upload', 'documents.view',
            ],

            'Agent' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'properties.create', 'properties.edit',
                'properties.media', 'listings.submit', 'leases.view', 'leases.create',
                'crm.leads.manage', 'inquiries.manage', 'viewings.manage',
                'offers.manage', 'commissions.view', 'messages.send',
                'documents.upload', 'documents.view',
            ],

            'Real Estate Company' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'properties.create', 'properties.edit',
                'properties.media', 'properties.analytics', 'listings.submit',
                'agency.manage', 'crm.leads.manage', 'inquiries.manage',
                'commissions.view', 'leases.create', 'finance.invoices.create',
                'finance.payments.view', 'documents.upload', 'documents.view',
            ],

            'Developer' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'properties.create', 'properties.edit',
                'properties.media', 'properties.analytics', 'developments.manage',
                'units.manage', 'handovers.manage', 'listings.submit', 'listings.feature',
                'finance.invoices.create', 'finance.payments.view', 'leases.create', 'leases.sign',
                'documents.upload', 'documents.view',
            ],

            'Property Manager' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit', 'listings.expire', 'finance.invoices.create',
                'leases.view', 'leases.create', 'leases.sign', 'leases.terminate',
                'maintenance.manage', 'housekeeping.manage', 'kyc.verify',
                'documents.upload', 'documents.view',
            ],

            'Professional Service Provider' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'services.provide', 'inspections.manage',
                'documents.upload', 'documents.view',
            ],

            'Moderator' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'listings.moderate', 'listings.expire',
                'fraud.flag', 'moderation.actions', 'reviews.manage',
            ],

            'Verification Officer' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'kyc.verify', 'documents.verify', 'kyc.history',
                'documents.view',
            ],

            'Finance Officer' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'finance.ledger.view', 'finance.payments.view', 'finance.invoices.create',
                'finance.invoices.manage', 'finance.refunds.process', 'finance.escrow.release',
                'finance.payouts.manage', 'finance.reconciliation', 'finance.reports.generate',
                'commissions.view', 'audit.view',
            ],

            'Customer Support' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'support.tickets', 'support.context',
                'disputes.arbitrate', 'fraud.flag', 'support.escalate',
            ],

            'Seller' => [
                'properties.view', 'properties.create', 'properties.edit',
                'listings.submit', 'messages.send', 'documents.upload', 'documents.view',
            ],

            'User' => [
                'profile.update', 'profile.preferences', 'profile.security',
                'properties.view', 'favorites.manage',
            ],
        ];

        $existingRolePermissions = DB::table('role_permissions')
            ->get()
            ->groupBy('role_id')
            ->map(fn ($rows) => $rows->pluck('permission_id')->flip());

        $toInsert = [];
        foreach ($rolePermissions as $roleName => $codes) {
            if (isset($roleModels[$roleName])) {
                $roleId = $roleModels[$roleName]->id;
                $currentPerms = $existingRolePermissions->get($roleId, collect());

                foreach ($codes as $code) {
                    if (isset($permissionModels[$code])) {
                        $pId = $permissionModels[$code]->id;
                        if (! isset($currentPerms[$pId])) {
                            $toInsert[] = [
                                'role_id' => $roleId,
                                'permission_id' => $pId,
                            ];
                        }
                    }
                }
            }
        }

        if (! empty($toInsert)) {
            foreach (array_chunk($toInsert, 100) as $chunk) {
                DB::table('role_permissions')->insert($chunk);
            }
        }
    }
}
