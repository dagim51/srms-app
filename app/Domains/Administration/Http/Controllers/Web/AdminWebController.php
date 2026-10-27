<?php

namespace App\Domains\Administration\Http\Controllers\Web;

use App\Domains\Administration\Models\SystemSetting;
use App\Domains\Identity\Models\Role;
use App\Domains\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminWebController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => 12500,
            'active_properties' => 840,
            'monthly_revenue' => '24.5M ETB',
            'system_health' => '99.9%',
        ];

        $recentSignups = [
            ['id' => 'USR-901', 'name' => 'Abebe Kebede', 'role' => 'Tenant', 'date' => '2026-10-08'],
            ['id' => 'USR-902', 'name' => 'Sara Tadesse', 'role' => 'Property Manager', 'date' => '2026-10-08'],
            ['id' => 'USR-903', 'name' => 'Dawit Girma', 'role' => 'Tenant', 'date' => '2026-10-07'],
        ];

        return view('frontend.admin.index', compact('stats', 'recentSignups'));
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->whereHas('roles', function ($rq) use ($role) {
                $rq->where('name', $role);
            });
        }

        $dbUsers = $query->latest()->get()->map(function (User $u) {
            $roleNames = $u->roles()->pluck('name')->all();
            if (empty($roleNames)) {
                $roleNames = ['User'];
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => '+251 911 234 567',
                'role' => implode(', ', $roleNames),
                'roles' => $roleNames,
                'status' => 'active',
                'joined' => $u->created_at ? $u->created_at->format('Y-m-d') : '2026-10-08',
            ];
        })->toArray();

        $mockUsers = [
            ['id' => 'USR-901', 'name' => 'Abebe Kebede', 'email' => 'abebe@example.com', 'phone' => '+251 911 234 567', 'role' => 'Tenant', 'roles' => ['Tenant'], 'status' => 'active', 'joined' => '2026-10-08'],
            ['id' => 'USR-902', 'name' => 'Sara Tadesse', 'email' => 'sara@example.com', 'phone' => '+251 922 456 789', 'role' => 'Property Manager, Landlord', 'roles' => ['Property Manager', 'Landlord'], 'status' => 'active', 'joined' => '2026-10-08'],
            ['id' => 'USR-903', 'name' => 'Kenenisa Bekele', 'email' => 'kenenisa@example.com', 'phone' => '+251 911 888 999', 'role' => 'Property Owner, Host', 'roles' => ['Property Owner', 'Host'], 'status' => 'active', 'joined' => '2026-03-15'],
            ['id' => 'USR-904', 'name' => 'Meron Alemu', 'email' => 'meron@example.com', 'phone' => '+251 933 678 901', 'role' => 'Agent', 'roles' => ['Agent'], 'status' => 'suspended', 'joined' => '2026-05-12'],
            ['id' => 'USR-001', 'name' => 'Super Admin', 'email' => 'admin@srms.et', 'phone' => '+251 911 000 111', 'role' => 'Administrator', 'roles' => ['Administrator'], 'status' => 'active', 'joined' => '2025-01-01'],
        ];

        if ($search = $request->input('search')) {
            $s = strtolower($search);
            $mockUsers = array_filter($mockUsers, function ($u) use ($s) {
                return str_contains(strtolower($u['name']), $s) || str_contains(strtolower($u['email']), $s) || str_contains(strtolower($u['role']), $s);
            });
        }
        if ($role = $request->input('role')) {
            $mockUsers = array_filter($mockUsers, fn ($u) => strtolower($u['role']) === strtolower($role));
        }
        if ($status = $request->input('status')) {
            $mockUsers = array_filter($mockUsers, fn ($u) => strtolower($u['status']) === strtolower($status));
        }

        // Merge without duplicating emails
        $existingEmails = collect($dbUsers)->pluck('email')->all();
        $filteredMock = collect($mockUsers)->reject(fn ($u) => in_array($u['email'], $existingEmails))->all();
        $users = array_merge($dbUsers, $filteredMock);

        $roles = Role::pluck('name')->toArray();
        if (empty($roles)) {
            $roles = ['Administrator', 'Property Manager', 'Agent', 'Developer', 'Host', 'Seller', 'User'];
        }

        return view('frontend.admin.users', compact('users', 'roles'));
    }

    public function userCreate()
    {
        $roles = Role::all();
        if ($roles->isEmpty()) {
            $roles = collect([
                (object) ['id' => '1', 'name' => 'Administrator'],
                (object) ['id' => '2', 'name' => 'Property Manager'],
                (object) ['id' => '3', 'name' => 'Agent'],
                (object) ['id' => '4', 'name' => 'Developer'],
                (object) ['id' => '5', 'name' => 'Tenant'],
            ]);
        }

        $organizations = Organization::take(10)->get();

        return view('frontend.admin.users_create', compact('roles', 'organizations'));
    }

    public function userStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string',
            'roles' => 'nullable|array',
            'roles.*' => 'string',
            'organization_id' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'locale' => 'en',
            ]);

            // Assign role(s) using HasRoles trait
            $rolesToAssign = [];
            if (! empty($validated['roles'])) {
                $rolesToAssign = (array) $validated['roles'];
            } elseif (! empty($validated['role'])) {
                $rolesToAssign = [$validated['role']];
            } else {
                $rolesToAssign = ['Buyer'];
            }

            foreach ($rolesToAssign as $roleName) {
                $user->assignRole($roleName, $validated['organization_id'] ?? null);
            }

            $rolesSummary = implode(', ', $rolesToAssign);
            return redirect()->route('dashboard.admin.users')->with('success', "User '{$user->name}' was successfully created with role(s): {$rolesSummary}.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Could not create user: '.$e->getMessage());
        }
    }

    public function updateUserRole(Request $request, $id)
    {
        $validated = $request->validate([
            'role' => 'nullable|string',
            'roles' => 'nullable|array',
            'roles.*' => 'string',
            'organization_id' => 'nullable|string',
        ]);

        $rolesToSync = [];
        if (! empty($validated['roles'])) {
            $rolesToSync = (array) $validated['roles'];
        } elseif (! empty($validated['role'])) {
            $rolesToSync = [$validated['role']];
        }

        $user = User::findOrFail($id);
        $user->syncRoles($rolesToSync, $validated['organization_id'] ?? null);

        $rolesSummary = ! empty($rolesToSync) ? implode(', ', $rolesToSync) : 'None';
        return redirect()->back()->with('success', "User '{$user->name}' role(s) successfully updated to: {$rolesSummary}.");
    }

    public function userShow($id)
    {
        $user = User::find($id);

        if (! $user) {
            $user = (object) [
                'id' => $id,
                'name' => 'Abebe Kebede',
                'email' => 'abebe@example.com',
                'phone' => '+251 911 234 567',
                'role' => 'Tenant',
                'status' => 'active',
                'joined' => '2026-10-08 10:00 AM',
                'kyc_status' => 'verified',
                'last_login' => '2026-10-08 14:30 PM (197.156.92.12)',
            ];
        } else {
            $user = (object) [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '+251 911 234 567',
                'role' => $user->roles()->first()?->name ?? 'User',
                'status' => 'active',
                'joined' => $user->created_at ? $user->created_at->format('Y-m-d H:i') : '2026-10-08 10:00 AM',
                'kyc_status' => 'verified',
                'last_login' => '2026-10-08 14:30 PM',
            ];
        }

        return view('frontend.admin.user_show', compact('user'));
    }

    public function permissions(Request $request)
    {
        $this->ensureDefaultPermissionsExist();

        $roles = Role::all();
        $selectedRoleId = $request->query('role_id', $roles->first()?->id);
        $selectedRole = $roles->firstWhere('id', $selectedRoleId) ?? $roles->first();

        // Load all system permissions categorized by domain
        $categories = [
            'properties' => [
                'name' => 'Property & Inventory Management',
                'icon' => 'building',
                'description' => 'Create, manage, and catalog real estate assets and PostGIS geocoded locations.',
                'permissions' => [
                    ['code' => 'properties.view', 'name' => 'View Properties', 'desc' => 'Inspect property records and characteristic details.'],
                    ['code' => 'properties.create', 'name' => 'Create Properties', 'desc' => 'Draft new residential or commercial property records.'],
                    ['code' => 'properties.edit', 'name' => 'Edit Properties', 'desc' => 'Modify property specifications, media, and features.'],
                    ['code' => 'properties.delete', 'name' => 'Delete / Archive', 'desc' => 'Archive or permanently delete property inventory.'],
                    ['code' => 'properties.gis', 'name' => 'GIS & Geocoding', 'desc' => 'Edit spatial boundaries, polygons, and coordinates.'],
                    ['code' => 'units.manage', 'name' => 'Manage Units & Inventory', 'desc' => 'Manage individual development units, phases, and floorplans.'],
                ],
            ],
            'listings' => [
                'name' => 'Marketplace Listings & Moderation',
                'icon' => 'tag',
                'description' => 'Publishing to public discovery, moderation queue decisions, and promotions.',
                'permissions' => [
                    ['code' => 'listings.submit', 'name' => 'Submit Listings', 'desc' => 'Publish listings for sale, rent, or lease.'],
                    ['code' => 'listings.moderate', 'name' => 'Review & Moderate', 'desc' => 'Approve, reject, or request revisions on pending listings.'],
                    ['code' => 'listings.feature', 'name' => 'Feature Listings', 'desc' => 'Assign premium promotional badges on marketplace.'],
                    ['code' => 'listings.expire', 'name' => 'Delist / Expire', 'desc' => 'Manually delist properties or mark them sold/rented.'],
                ],
            ],
            'stays' => [
                'name' => 'Short-Stay Hospitality & Booking',
                'icon' => 'home',
                'description' => 'Accommodations, guest reservations, booking calendars, and check-in/out.',
                'permissions' => [
                    ['code' => 'accommodations.manage', 'name' => 'Manage Accommodations', 'desc' => 'Manage short-stay units, rates, rules, and amenities.'],
                    ['code' => 'bookings.create', 'name' => 'Book Accommodations', 'desc' => 'Reserve vacation stays and short-term accommodations.'],
                    ['code' => 'bookings.manage', 'name' => 'Manage Guest Bookings', 'desc' => 'Manage reservations, guest check-ins, check-outs, and calendar.'],
                ],
            ],
            'tenancy' => [
                'name' => 'Leases, Tenancy & Maintenance',
                'icon' => 'document-text',
                'description' => 'Drafting tenancy agreements, digital signing, and maintenance dispatch.',
                'permissions' => [
                    ['code' => 'leases.view', 'name' => 'View Leases', 'desc' => 'Inspect active and historical tenancy lease contracts.'],
                    ['code' => 'leases.create', 'name' => 'Draft Leases', 'desc' => 'Create residential and commercial lease agreements.'],
                    ['code' => 'leases.sign', 'name' => 'Digital Signatures', 'desc' => 'Execute legal digital signatures on contracts.'],
                    ['code' => 'leases.terminate', 'name' => 'Terminate Leases', 'desc' => 'Conclude tenancies, conduct checkouts, and process deposits.'],
                    ['code' => 'maintenance.request', 'name' => 'Submit Repair Requests', 'desc' => 'Submit unit maintenance tickets and issue reports.'],
                    ['code' => 'maintenance.manage', 'name' => 'Dispatch Maintenance', 'desc' => 'Assign work orders and contractors for unit repairs.'],
                ],
            ],
            'developments' => [
                'name' => 'Real Estate Developments & Off-Plan',
                'icon' => 'office-building',
                'description' => 'Off-plan developments, phase milestones, and unit handover management.',
                'permissions' => [
                    ['code' => 'developments.manage', 'name' => 'Manage Projects', 'desc' => 'Create and configure off-plan real estate projects.'],
                    ['code' => 'handovers.manage', 'name' => 'Milestones & Handover', 'desc' => 'Track construction progress and buyer handover records.'],
                ],
            ],
            'agency' => [
                'name' => 'Agency, Brokerage & CRM',
                'icon' => 'user-group',
                'description' => 'Agency branch management, broker leads, deal pipelines, and commission audits.',
                'permissions' => [
                    ['code' => 'agency.manage', 'name' => 'Agency Management', 'desc' => 'Manage company branches, agent roster, and brand settings.'],
                    ['code' => 'crm.leads.manage', 'name' => 'CRM Leads & Pipeline', 'desc' => 'Manage client leads, inquiries, and viewing appointments.'],
                    ['code' => 'commissions.view', 'name' => 'Commission Tracking', 'desc' => 'Audit commission splits, payouts, and deal ledger.'],
                ],
            ],
            'services' => [
                'name' => 'Professional Real Estate Services',
                'icon' => 'briefcase',
                'description' => 'Legal conveyancing, surveying, architectural review, and physical inspections.',
                'permissions' => [
                    ['code' => 'services.provide', 'name' => 'Offer Pro Services', 'desc' => 'Provide legal, surveying, architectural, or valuation services.'],
                    ['code' => 'inspections.manage', 'name' => 'Conduct Inspections', 'desc' => 'Conduct property inspections and upload certified condition reports.'],
                ],
            ],
            'trust' => [
                'name' => 'Trust, KYC & Document Verification',
                'icon' => 'shield-check',
                'description' => 'Title deed verification, national ID review, dispute arbitration, and anti-fraud.',
                'permissions' => [
                    ['code' => 'kyc.verify', 'name' => 'Verify KYC & Identities', 'desc' => 'Review national IDs, TINs, and municipal certificates.'],
                    ['code' => 'documents.verify', 'name' => 'Verify Title Deeds', 'desc' => 'Verify digital title deeds, ownership documents, and licenses.'],
                    ['code' => 'support.tickets', 'name' => 'Customer Support', 'desc' => 'Handle customer support tickets and help requests.'],
                    ['code' => 'disputes.arbitrate', 'name' => 'Arbitrate Disputes', 'desc' => 'Intervene and resolve buyer-seller transaction claims.'],
                    ['code' => 'fraud.flag', 'name' => 'Anti-Fraud Enforcement', 'desc' => 'Freeze compromised accounts and blacklist bad actors.'],
                ],
            ],
            'finance' => [
                'name' => 'Escrow, Ledger & Financial Payouts',
                'icon' => 'banknotes',
                'description' => 'Double-entry accounting, VAT invoicing, escrow release, and telebirr/CBE disbursements.',
                'permissions' => [
                    ['code' => 'finance.ledger.view', 'name' => 'Inspect General Ledger', 'desc' => 'Audit ledger account balances and journal entries.'],
                    ['code' => 'finance.invoices.create', 'name' => 'Generate Invoices', 'desc' => 'Issue tax-compliant invoices and payment demands.'],
                    ['code' => 'finance.escrow.release', 'name' => 'Authorize Escrow Release', 'desc' => 'Release held funds to sellers upon verified handover.'],
                    ['code' => 'finance.payouts.manage', 'name' => 'Execute Disbursements', 'desc' => 'Disburse payments via Telebirr, CBE Birr, or bank wires.'],
                ],
            ],
            'admin' => [
                'name' => 'System & User Administration',
                'icon' => 'cog',
                'description' => 'Platform user provisioning, role assignments, security logs, and fee settings.',
                'permissions' => [
                    ['code' => 'users.manage', 'name' => 'Manage Users', 'desc' => 'Provision accounts, invite members, and reset passwords.'],
                    ['code' => 'roles.assign', 'name' => 'Configure Permissions', 'desc' => 'Modify role capabilities and custom user overrides.'],
                    ['code' => 'settings.update', 'name' => 'Platform Configuration', 'desc' => 'Configure fee structures, tax rates, and integrations.'],
                    ['code' => 'audit.view', 'name' => 'Security Audit Logs', 'desc' => 'Inspect administrative audit trails and security events.'],
                    ['code' => 'manage_locations', 'name' => 'Manage Geographic Regions', 'desc' => 'Create and modify administrative geographical regions.'],
                    ['code' => 'admin', 'name' => 'Super Administrator', 'desc' => 'Full administrative access and platform-wide capabilities.'],
                ],
            ],
        ];

        // Get permissions assigned to selected role
        $rolePermissions = [];
        if ($selectedRole) {
            $rolePermissions = \DB::table('role_permissions')
                ->where('role_id', $selectedRole->id)
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->pluck('permissions.code')
                ->toArray();
        }

        // If no permissions mapped in DB yet, compute logical defaults based on role name
        if (empty($rolePermissions) && $selectedRole) {
            $rolePermissions = $this->getDefaultPermissionsForRole($selectedRole->name);
        }

        // All users for the User Inspector tab
        $allUsers = User::take(25)->get()->map(function (User $u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->roles()->pluck('name')->join(', ') ?: 'User',
            ];
        })->toArray();

        return view('frontend.admin.permissions', compact('roles', 'selectedRole', 'categories', 'rolePermissions', 'allUsers'));
    }

    public function updatePermissions(Request $request)
    {
        $roleId = $request->input('role_id');
        $grantedCodes = $request->input('permissions', []);

        $role = Role::find($roleId);
        if (! $role) {
            return redirect()->back()->with('error', 'Selected role not found.');
        }

        // Sync role permissions in database
        $permissionIds = Permission::whereIn('code', $grantedCodes)->pluck('id');

        \DB::table('role_permissions')->where('role_id', $role->id)->delete();
        foreach ($permissionIds as $pId) {
            \DB::table('role_permissions')->insert([
                'role_id' => $role->id,
                'permission_id' => $pId,
            ]);
        }

        return redirect()->route('dashboard.admin.permissions', ['role_id' => $role->id])
            ->with('success', "Permissions for role '{$role->name}' were successfully updated ({$permissionIds->count()} capabilities enabled).");
    }

    protected function ensureDefaultPermissionsExist(): void
    {
        $allCodes = [
            'properties.view' => 'View Properties and details',
            'properties.create' => 'Draft new residential/commercial properties',
            'properties.edit' => 'Modify property specifications, media, and features',
            'properties.delete' => 'Archive or remove properties from catalog',
            'properties.gis' => 'Edit spatial boundaries, polygons, and coordinates',
            'units.manage' => 'Manage individual development units, phases, and inventory',

            'listings.submit' => 'Publish listings for sale, rent, or lease',
            'listings.moderate' => 'Approve, reject, or request revisions on pending listings',
            'listings.feature' => 'Assign premium promotional badges on marketplace',
            'listings.expire' => 'Manually delist properties or mark them sold/rented',

            'accommodations.manage' => 'Manage short-stay units, rates, rules, and amenities',
            'bookings.create' => 'Book accommodations and short-stay properties',
            'bookings.manage' => 'Manage bookings, check-in, check-out, and guest reservations',

            'leases.view' => 'View residential and commercial lease agreements',
            'leases.create' => 'Create residential and commercial lease agreements',
            'leases.sign' => 'Execute legal digital signatures on contracts',
            'leases.terminate' => 'Conclude tenancies, conduct checkouts, and process deposits',
            'maintenance.request' => 'Submit maintenance and repair requests for occupied units',
            'maintenance.manage' => 'Assign work orders and contractors for unit repairs',

            'developments.manage' => 'Create and configure off-plan real estate development projects',
            'handovers.manage' => 'Track construction milestones and buyer handover records',

            'agency.manage' => 'Manage real estate company branches, agent roster, and brand settings',
            'crm.leads.manage' => 'Manage client leads, deal pipelines, and client inquiries',
            'commissions.view' => 'View and audit commission splits and deal payouts',

            'services.provide' => 'Offer legal conveyancing, surveying, architectural, valuation, or inspection services',
            'inspections.manage' => 'Conduct physical inspections, upload condition reports, and certify compliance',

            'kyc.verify' => 'Review national IDs, TINs, and municipal land titles',
            'documents.verify' => 'Verify digital title deeds, ownership documents, and developer licenses',

            'finance.ledger.view' => 'Audit ledger account balances and journal entries',
            'finance.invoices.create' => 'Issue tax-compliant invoices and payment demands',
            'finance.escrow.release' => 'Authorize release of held escrow funds upon verified handover',
            'finance.payouts.manage' => 'Disburse payments via Telebirr, CBE Birr, or bank wires',

            'support.tickets' => 'Handle customer support tickets, inquiries, and technical issues',
            'disputes.arbitrate' => 'Intervene and resolve buyer-seller transaction claims',
            'fraud.flag' => 'Freeze compromised accounts and blacklist bad actors',

            'users.manage' => 'Provision accounts, invite members, and reset passwords',
            'roles.assign' => 'Modify role capabilities and custom user overrides',
            'settings.update' => 'Configure fee structures, tax rates, and integrations',
            'audit.view' => 'Inspect administrative audit trails and security events',
            'manage_locations' => 'Create and modify administrative geographical regions',
            'admin' => 'Full administrative access and disputes arbitration',
        ];

        foreach ($allCodes as $code => $desc) {
            Permission::firstOrCreate(
                ['code' => $code],
                ['id' => (string) Str::uuid(), 'description' => $desc]
            );
        }
    }

    protected function getDefaultPermissionsForRole(string $roleName): array
    {
        switch ($roleName) {
            case 'Administrator':
                return [
                    'properties.view', 'properties.create', 'properties.edit', 'properties.delete', 'properties.gis', 'units.manage',
                    'listings.submit', 'listings.moderate', 'listings.feature', 'listings.expire',
                    'accommodations.manage', 'bookings.create', 'bookings.manage',
                    'leases.view', 'leases.create', 'leases.sign', 'leases.terminate', 'maintenance.request', 'maintenance.manage',
                    'developments.manage', 'handovers.manage',
                    'agency.manage', 'crm.leads.manage', 'commissions.view',
                    'services.provide', 'inspections.manage',
                    'kyc.verify', 'documents.verify',
                    'finance.ledger.view', 'finance.invoices.create', 'finance.escrow.release', 'finance.payouts.manage',
                    'support.tickets', 'disputes.arbitrate', 'fraud.flag',
                    'users.manage', 'roles.assign', 'settings.update', 'audit.view', 'manage_locations', 'admin',
                ];
            case 'Buyer':
                return ['properties.view', 'leases.view', 'bookings.create', 'maintenance.request'];
            case 'Tenant':
                return ['properties.view', 'leases.view', 'leases.sign', 'maintenance.request'];
            case 'Guest / Traveler':
                return ['properties.view', 'bookings.create'];
            case 'Property Owner':
                return ['properties.view', 'properties.create', 'properties.edit', 'properties.delete', 'listings.submit', 'leases.view'];
            case 'Host':
                return ['properties.view', 'properties.create', 'properties.edit', 'listings.submit', 'accommodations.manage', 'bookings.manage'];
            case 'Landlord':
                return ['properties.view', 'properties.create', 'properties.edit', 'listings.submit', 'leases.view', 'leases.create', 'leases.sign', 'leases.terminate', 'maintenance.manage'];
            case 'Agent':
                return ['properties.view', 'properties.create', 'properties.edit', 'listings.submit', 'leases.view', 'leases.create', 'crm.leads.manage', 'commissions.view'];
            case 'Real Estate Company':
                return ['properties.view', 'properties.create', 'properties.edit', 'listings.submit', 'agency.manage', 'crm.leads.manage', 'commissions.view', 'leases.create', 'finance.invoices.create'];
            case 'Developer':
                return ['properties.view', 'properties.create', 'properties.edit', 'developments.manage', 'units.manage', 'handovers.manage', 'listings.submit', 'listings.feature', 'finance.invoices.create', 'leases.create', 'leases.sign'];
            case 'Property Manager':
                return ['properties.view', 'properties.create', 'properties.edit', 'listings.submit', 'listings.expire', 'finance.invoices.create', 'leases.view', 'leases.create', 'leases.sign', 'leases.terminate', 'maintenance.manage', 'kyc.verify'];
            case 'Professional Service Provider':
                return ['properties.view', 'services.provide', 'inspections.manage'];
            case 'Moderator':
                return ['properties.view', 'listings.moderate', 'listings.expire', 'fraud.flag'];
            case 'Verification Officer':
                return ['properties.view', 'kyc.verify', 'documents.verify'];
            case 'Finance Officer':
                return ['finance.ledger.view', 'finance.invoices.create', 'finance.escrow.release', 'finance.payouts.manage', 'audit.view'];
            case 'Customer Support':
                return ['properties.view', 'support.tickets', 'disputes.arbitrate', 'fraud.flag'];
            case 'Seller':
                return ['properties.view', 'properties.create', 'properties.edit', 'listings.submit'];
            case 'User':
            default:
                return ['properties.view'];
        }
    }

    public function audit(Request $request)
    {
        if (AuditLog::count() === 0) {
            AuditLog::log(
                event: 'system_initialized',
                auditable: null,
                newValues: ['status' => 'operational'],
                oldValues: [],
                notes: 'System audit ledger initialized',
                actorId: auth()->id()
            );
        }

        $query = AuditLog::with('actor')->latest();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('actor', function ($aq) use ($search) {
                        $aq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('frontend.admin.audit', compact('logs'));
    }

    public function settings()
    {
        $settings = SystemSetting::all()->keyBy('key');

        return view('frontend.admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $type = match (true) {
                $value === 'true' || $value === 'false' || is_bool($value) => 'boolean',
                is_int($value) || (is_string($value) && ctype_digit($value)) => 'integer',
                is_array($value) => 'json',
                default => 'string',
            };

            SystemSetting::set(
                key: $key,
                value: $value,
                type: $type,
                userId: auth()->id()
            );
        }

        return redirect()->back()->with('success', 'System settings updated successfully.');
    }
}
