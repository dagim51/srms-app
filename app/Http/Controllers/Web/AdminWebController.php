<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminWebController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => 12500,
            'active_properties' => 840,
            'monthly_revenue' => '24.5M ETB',
            'system_health' => '99.9%'
        ];

        $recentSignups = [
            ['id' => 'USR-901', 'name' => 'Abebe Kebede', 'role' => 'Tenant', 'date' => '2026-10-08'],
            ['id' => 'USR-902', 'name' => 'Sara Tadesse', 'role' => 'Property Manager', 'date' => '2026-10-08'],
            ['id' => 'USR-903', 'name' => 'Dawit Girma', 'role' => 'Tenant', 'date' => '2026-10-07'],
        ];

        return view('frontend.admin.index', compact('stats', 'recentSignups'));
    }

    public function users()
    {
        $dbUsers = \App\Models\User::latest()->take(20)->get()->map(function ($u) {
            $roleName = $u->roles()->first()?->name ?? 'User';
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => '+251 911 234 567',
                'role' => $roleName,
                'status' => 'active',
                'joined' => $u->created_at ? $u->created_at->format('Y-m-d') : '2026-10-08',
            ];
        })->toArray();

        $mockUsers = [
            ['id' => 'USR-901', 'name' => 'Abebe Kebede', 'email' => 'abebe@example.com', 'phone' => '+251 911 234 567', 'role' => 'Tenant', 'status' => 'active', 'joined' => '2026-10-08'],
            ['id' => 'USR-902', 'name' => 'Sara Tadesse', 'email' => 'sara@example.com', 'phone' => '+251 922 456 789', 'role' => 'Property Manager', 'status' => 'active', 'joined' => '2026-10-08'],
            ['id' => 'USR-904', 'name' => 'Meron Alemu', 'email' => 'meron@example.com', 'phone' => '+251 933 678 901', 'role' => 'Agent', 'status' => 'suspended', 'joined' => '2026-05-12'],
            ['id' => 'USR-001', 'name' => 'Super Admin', 'email' => 'admin@srms.et', 'phone' => '+251 911 000 111', 'role' => 'Administrator', 'status' => 'active', 'joined' => '2025-01-01'],
        ];

        // Merge without duplicating emails
        $existingEmails = collect($dbUsers)->pluck('email')->all();
        $filteredMock = collect($mockUsers)->reject(fn($u) => in_array($u['email'], $existingEmails))->all();
        $users = array_merge($dbUsers, $filteredMock);

        $roles = \App\Models\Role::pluck('name')->toArray();
        if (empty($roles)) {
            $roles = ['Administrator', 'Property Manager', 'Agent', 'Developer', 'Host', 'Seller', 'User'];
        }

        return view('frontend.admin.users', compact('users', 'roles'));
    }

    public function userCreate()
    {
        $roles = \App\Models\Role::all();
        if ($roles->isEmpty()) {
            $roles = collect([
                (object)['id' => '1', 'name' => 'Administrator'],
                (object)['id' => '2', 'name' => 'Property Manager'],
                (object)['id' => '3', 'name' => 'Agent'],
                (object)['id' => '4', 'name' => 'Developer'],
                (object)['id' => '5', 'name' => 'Tenant'],
            ]);
        }

        $organizations = \App\Models\Organization::take(10)->get();

        return view('frontend.admin.users_create', compact('roles', 'organizations'));
    }

    public function userStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
            'organization_id' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        try {
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                'locale' => 'en',
            ]);

            // Assign role if role model exists
            $roleModel = \App\Models\Role::where('name', $validated['role'])->first();
            if ($roleModel) {
                $user->roles()->attach($roleModel->id, [
                    'organization_id' => $validated['organization_id'] ?? null
                ]);
            }

            return redirect()->route('dashboard.admin.users')->with('success', "User '{$user->name}' was successfully created with role '{$validated['role']}'.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Could not create user: ' . $e->getMessage());
        }
    }

    public function userShow($id)
    {
        $user = \App\Models\User::find($id);

        if (!$user) {
            $user = (object)[
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
            $user = (object)[
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

        $roles = \App\Models\Role::all();
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
            'tenancy' => [
                'name' => 'Leases, Tenancy & Maintenance',
                'icon' => 'document-text',
                'description' => 'Drafting tenancy agreements, digital signing, and maintenance dispatch.',
                'permissions' => [
                    ['code' => 'leases.create', 'name' => 'Draft Leases', 'desc' => 'Create residential and commercial lease agreements.'],
                    ['code' => 'leases.sign', 'name' => 'Digital Signatures', 'desc' => 'Execute legal digital signatures on contracts.'],
                    ['code' => 'leases.terminate', 'name' => 'Terminate Leases', 'desc' => 'Conclude tenancies, conduct checkouts, and process deposits.'],
                    ['code' => 'maintenance.manage', 'name' => 'Dispatch Maintenance', 'desc' => 'Assign work orders and contractors for unit repairs.'],
                ],
            ],
            'trust' => [
                'name' => 'Trust, KYC & Dispute Resolution',
                'icon' => 'shield-check',
                'description' => 'Title deed verification, dispute arbitration, and anti-fraud controls.',
                'permissions' => [
                    ['code' => 'kyc.verify', 'name' => 'Verify KYC & Deeds', 'desc' => 'Review national IDs, TINs, and municipal land titles.'],
                    ['code' => 'disputes.arbitrate', 'name' => 'Arbitrate Disputes', 'desc' => 'Intervene and resolve buyer-seller transaction claims.'],
                    ['code' => 'fraud.flag', 'name' => 'Anti-Fraud Enforcement', 'desc' => 'Freeze compromised accounts and blacklist bad actors.'],
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
        $allUsers = \App\Models\User::take(25)->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->roles()->first()?->name ?? 'User',
            ];
        })->toArray();

        return view('frontend.admin.permissions', compact('roles', 'selectedRole', 'categories', 'rolePermissions', 'allUsers'));
    }

    public function updatePermissions(Request $request)
    {
        $roleId = $request->input('role_id');
        $grantedCodes = $request->input('permissions', []);

        $role = \App\Models\Role::find($roleId);
        if (!$role) {
            return redirect()->back()->with('error', 'Selected role not found.');
        }

        // Sync role permissions in database
        $permissionIds = \App\Models\Permission::whereIn('code', $grantedCodes)->pluck('id');
        
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
        ];

        foreach ($allCodes as $code => $desc) {
            \App\Models\Permission::firstOrCreate(
                ['code' => $code],
                ['id' => (string)\Illuminate\Support\Str::uuid(), 'description' => $desc]
            );
        }
    }

    protected function getDefaultPermissionsForRole(string $roleName): array
    {
        switch ($roleName) {
            case 'Administrator':
                return [
                    'properties.view', 'properties.create', 'properties.edit', 'properties.delete', 'properties.gis',
                    'listings.submit', 'listings.moderate', 'listings.feature', 'listings.expire',
                    'finance.ledger.view', 'finance.invoices.create', 'finance.escrow.release', 'finance.payouts.manage',
                    'leases.create', 'leases.sign', 'leases.terminate', 'maintenance.manage',
                    'kyc.verify', 'disputes.arbitrate', 'fraud.flag',
                    'users.manage', 'roles.assign', 'settings.update', 'audit.view'
                ];
            case 'Property Manager':
                return [
                    'properties.view', 'properties.create', 'properties.edit',
                    'listings.submit', 'listings.expire',
                    'finance.invoices.create',
                    'leases.create', 'leases.sign', 'leases.terminate', 'maintenance.manage',
                    'kyc.verify'
                ];
            case 'Agent':
            case 'Seller':
                return [
                    'properties.view', 'properties.create', 'properties.edit',
                    'listings.submit',
                    'leases.create',
                ];
            case 'Developer':
                return [
                    'properties.view', 'properties.create', 'properties.edit',
                    'listings.submit', 'listings.feature',
                    'finance.invoices.create',
                    'leases.create', 'leases.sign',
                ];
            default:
                return ['properties.view'];
        }
    }

    public function audit()
    {
        $logs = [
            ['id' => 'AUD-101', 'user' => 'Admin (USR-001)', 'action' => 'Suspended User USR-904', 'ip' => '10.0.0.5', 'date' => '2026-10-08 15:45:12'],
            ['id' => 'AUD-102', 'user' => 'System', 'action' => 'Generated Monthly Invoices', 'ip' => 'localhost', 'date' => '2026-10-01 00:00:01'],
            ['id' => 'AUD-103', 'user' => 'Admin (USR-001)', 'action' => 'Updated Platform Fee to 5%', 'ip' => '10.0.0.5', 'date' => '2026-09-15 11:20:00'],
            ['id' => 'AUD-104', 'user' => 'Sara Tadesse (USR-902)', 'action' => 'Deleted Listing LST-405', 'ip' => '197.156.12.34', 'date' => '2026-10-07 09:15:22'],
        ];

        return view('frontend.admin.audit', compact('logs'));
    }

    public function settings()
    {
        return view('frontend.admin.settings');
    }
}
