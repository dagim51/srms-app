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
        // Normalize phone number: if empty or only the "+251" country code prefix with no subscriber digits, treat as null
        $rawPhone = $request->input('phone');
        $phone = null;
        if (! empty($rawPhone)) {
            $cleaned = trim((string) $rawPhone);
            $digitsOnly = preg_replace('/[^\d]/', '', $cleaned);
            if ($cleaned === '+251' || $cleaned === '+251 ' || strlen($digitsOnly) < 7) {
                $phone = null;
            } else {
                $phone = $cleaned;
            }
        }
        $request->merge(['phone' => $phone]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:30|unique:users,phone',
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

        // Security Policy: Protect against lockout of the last active Administrator
        if ($user->hasRole('Administrator') && ! in_array('Administrator', $rolesToSync)) {
            $activeAdminCount = User::where('status', 'active')
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'Administrator')->whereNull('user_roles.organization_id');
                })
                ->count();

            if ($activeAdminCount <= 1) {
                return redirect()->back()->with('error', 'Cannot remove Administrator role from the only active platform administrator. Safety policy prevents administrative lockout.');
            }
        }

        $oldRoles = $user->roles()->pluck('name')->all();
        $user->syncRoles($rolesToSync, $validated['organization_id'] ?? null);

        // Security Audit Trail
        AuditLog::log(
            event: 'user_roles_updated',
            auditable: $user,
            newValues: ['roles' => $rolesToSync, 'organization_id' => $validated['organization_id'] ?? null],
            oldValues: ['roles' => $oldRoles],
            notes: "Updated role assignments for user {$user->email}",
            actorId: auth()->id()
        );

        $rolesSummary = ! empty($rolesToSync) ? implode(', ', $rolesToSync) : 'None';

        return redirect()->back()->with('success', "User '{$user->name}' role(s) successfully updated to: {$rolesSummary}.");
    }

    public function roles(Request $request)
    {
        $query = Role::withCount(['users', 'permissions']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->orderBy('is_system', 'desc')->orderBy('name', 'asc')->get();

        return view('frontend.admin.roles', compact('roles'));
    }

    public function roleStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:500',
        ]);

        $role = Role::create([
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'guard_name' => 'api',
            'status' => 'active',
            'is_system' => false,
        ]);

        AuditLog::log(
            event: 'role_created',
            auditable: $role,
            newValues: ['name' => $role->name, 'description' => $role->description],
            notes: "Created custom role {$role->name}",
            actorId: auth()->id()
        );

        return redirect()->route('dashboard.admin.roles')->with('success', "Role '{$role->name}' was successfully created.");
    }

    public function roleUpdate(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,'.$role->id,
            'description' => 'nullable|string|max:500',
        ]);

        $oldValues = ['name' => $role->name, 'description' => $role->description];

        // System roles cannot be renamed
        $nameToSet = $role->isSystem() ? $role->name : trim($validated['name']);

        $role->update([
            'name' => $nameToSet,
            'description' => $validated['description'] ?? $role->description,
        ]);

        AuditLog::log(
            event: 'role_updated',
            auditable: $role,
            newValues: ['name' => $role->name, 'description' => $role->description],
            oldValues: $oldValues,
            notes: "Updated role {$role->name}",
            actorId: auth()->id()
        );

        return redirect()->route('dashboard.admin.roles')->with('success', "Role '{$role->name}' was successfully updated.");
    }

    public function roleDestroy(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if ($role->isSystem()) {
            return redirect()->back()->with('error', "System role '{$role->name}' cannot be deleted because it is required by core platform operations.");
        }

        if ($role->users()->count() > 0) {
            $userCount = $role->users()->count();

            return redirect()->back()->with('error', "Cannot delete role '{$role->name}' because it currently has {$userCount} assigned user(s). Reassign these users first.");
        }

        $oldName = $role->name;
        $role->permissions()->detach();
        $role->delete();

        AuditLog::log(
            event: 'role_deleted',
            auditable: null,
            oldValues: ['name' => $oldName, 'id' => $id],
            notes: "Deleted custom role {$oldName}",
            actorId: auth()->id()
        );

        return redirect()->route('dashboard.admin.roles')->with('success', "Role '{$oldName}' was successfully deleted.");
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

        $roles = Role::orderBy('is_system', 'desc')->orderBy('name', 'asc')->get();
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
                    ['code' => 'properties.media', 'name' => 'Media & Floorplans', 'desc' => 'Upload and curate property photos, floorplans, and video tours.'],
                    ['code' => 'properties.analytics', 'name' => 'Property Analytics', 'desc' => 'View property performance, view counts, and engagement metrics.'],
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
            'engagement' => [
                'name' => 'Search, Engagement & Inquiries',
                'icon' => 'chat-bubble-left-right',
                'description' => 'Favorites, inquiries, viewing appointments, offers, and client communications.',
                'permissions' => [
                    ['code' => 'favorites.manage', 'name' => 'Save Favorites', 'desc' => 'Save, categorize, and organize favorite properties.'],
                    ['code' => 'properties.compare', 'name' => 'Compare Properties', 'desc' => 'Compare side-by-side property specifications.'],
                    ['code' => 'messages.send', 'name' => 'Direct Messaging', 'desc' => 'Send direct messages and inquiries to listing managers.'],
                    ['code' => 'inquiries.manage', 'name' => 'Manage Inquiries', 'desc' => 'Manage and respond to buyer and tenant inquiries.'],
                    ['code' => 'viewings.request', 'name' => 'Request Viewings', 'desc' => 'Request in-person or virtual property viewings.'],
                    ['code' => 'viewings.manage', 'name' => 'Viewing Calendars', 'desc' => 'Schedule, confirm, and manage property viewing calendars.'],
                    ['code' => 'offers.submit', 'name' => 'Submit Offers', 'desc' => 'Submit formal purchase or lease transaction offers.'],
                    ['code' => 'offers.manage', 'name' => 'Negotiate Offers', 'desc' => 'Review, counter, accept, or reject incoming transaction offers.'],
                    ['code' => 'reviews.submit', 'name' => 'Submit Reviews', 'desc' => 'Submit ratings and reviews for hosts and stays.'],
                    ['code' => 'reviews.manage', 'name' => 'Review Moderation', 'desc' => 'Moderate and respond to customer reviews.'],
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
                    ['code' => 'housekeeping.manage', 'name' => 'Housekeeping Turnovers', 'desc' => 'Schedule and coordinate cleaning and housekeeping turnovers.'],
                ],
            ],
            'tenancy' => [
                'name' => 'Leases, Tenancy & Maintenance',
                'icon' => 'document-text',
                'description' => 'Drafting tenancy agreements, digital signing, and maintenance dispatch.',
                'permissions' => [
                    ['code' => 'rental.apply', 'name' => 'Apply for Rental', 'desc' => 'Submit prospective tenant rental applications.'],
                    ['code' => 'rental.review', 'name' => 'Review Applications', 'desc' => 'Review and process prospective tenant rental applications.'],
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
                    ['code' => 'documents.upload', 'name' => 'Upload Documents', 'desc' => 'Upload transaction, identity, and property documents.'],
                    ['code' => 'documents.view', 'name' => 'View Documents', 'desc' => 'View documents in accordance with document access levels.'],
                    ['code' => 'kyc.verify', 'name' => 'Verify KYC & Identities', 'desc' => 'Review national IDs, TINs, and municipal certificates.'],
                    ['code' => 'documents.verify', 'name' => 'Verify Title Deeds', 'desc' => 'Verify digital title deeds, ownership documents, and licenses.'],
                    ['code' => 'kyc.history', 'name' => 'KYC Audit Trails', 'desc' => 'Inspect historical verification checks and audit trails.'],
                ],
            ],
            'finance' => [
                'name' => 'Escrow, Ledger & Financial Payouts',
                'icon' => 'banknotes',
                'description' => 'Double-entry accounting, VAT invoicing, escrow release, and telebirr/CBE disbursements.',
                'permissions' => [
                    ['code' => 'finance.ledger.view', 'name' => 'Inspect General Ledger', 'desc' => 'Audit ledger account balances and journal entries.'],
                    ['code' => 'finance.payments.view', 'name' => 'Payment Records', 'desc' => 'Inspect payment transactions and receipt logs.'],
                    ['code' => 'finance.invoices.create', 'name' => 'Generate Invoices', 'desc' => 'Issue tax-compliant invoices and payment demands.'],
                    ['code' => 'finance.invoices.manage', 'name' => 'Manage Invoices', 'desc' => 'Manage invoices, payment statuses, and reminders.'],
                    ['code' => 'finance.refunds.process', 'name' => 'Process Refunds', 'desc' => 'Review, approve, and disburse customer refunds.'],
                    ['code' => 'finance.escrow.release', 'name' => 'Authorize Escrow Release', 'desc' => 'Release held funds to sellers upon verified handover.'],
                    ['code' => 'finance.payouts.manage', 'name' => 'Execute Disbursements', 'desc' => 'Disburse payments via Telebirr, CBE Birr, or bank wires.'],
                    ['code' => 'finance.reconciliation', 'name' => 'Financial Reconciliation', 'desc' => 'Reconcile gateway disbursements against bank statements.'],
                    ['code' => 'finance.reports.generate', 'name' => 'Financial Reports', 'desc' => 'Generate accounting, VAT, and transaction financial reports.'],
                ],
            ],
            'support' => [
                'name' => 'Customer Support, Disputes & Moderation',
                'icon' => 'lifebuoy',
                'description' => 'Support ticketing, customer dispute mediation, and trust & safety enforcement.',
                'permissions' => [
                    ['code' => 'support.tickets', 'name' => 'Support Tickets', 'desc' => 'Handle customer support tickets, inquiries, and technical issues.'],
                    ['code' => 'support.context', 'name' => 'Customer Context', 'desc' => 'Access transaction and customer context needed for resolution.'],
                    ['code' => 'disputes.arbitrate', 'name' => 'Arbitrate Disputes', 'desc' => 'Intervene and resolve buyer-seller transaction claims.'],
                    ['code' => 'fraud.flag', 'name' => 'Anti-Fraud Enforcement', 'desc' => 'Freeze compromised accounts and blacklist bad actors.'],
                    ['code' => 'moderation.actions', 'name' => 'Moderation Actions', 'desc' => 'Execute moderation actions against listings and accounts.'],
                    ['code' => 'support.escalate', 'name' => 'Case Escalation', 'desc' => 'Escalate complex cases to senior officers or legal specialists.'],
                ],
            ],
            'admin' => [
                'name' => 'System & User Administration',
                'icon' => 'cog',
                'description' => 'Platform user provisioning, role assignments, security logs, and fee settings.',
                'permissions' => [
                    ['code' => 'profile.update', 'name' => 'Update Profile', 'desc' => 'View and update personal user profile information.'],
                    ['code' => 'profile.preferences', 'name' => 'Account Preferences', 'desc' => 'Manage personal account preferences and notifications.'],
                    ['code' => 'profile.security', 'name' => 'Security & 2FA', 'desc' => 'View and update account credentials and 2FA security.'],
                    ['code' => 'users.view', 'name' => 'View Users', 'desc' => 'View user rosters and account profiles.'],
                    ['code' => 'users.manage', 'name' => 'Manage Users', 'desc' => 'Provision accounts, update statuses, and reset passwords.'],
                    ['code' => 'roles.assign', 'name' => 'Assign Roles', 'desc' => 'Assign and revoke user roles and custom overrides.'],
                    ['code' => 'roles.manage', 'name' => 'Manage Roles', 'desc' => 'Create, configure, and maintain custom and system roles.'],
                    ['code' => 'settings.update', 'name' => 'Platform Settings', 'desc' => 'Configure platform parameters, fee structures, and integrations.'],
                    ['code' => 'manage_locations', 'name' => 'Manage Regions', 'desc' => 'Create and modify administrative geographical regions.'],
                    ['code' => 'categories.manage', 'name' => 'Manage Categories', 'desc' => 'Manage property categories, amenities, and listing taxonomies.'],
                    ['code' => 'analytics.view', 'name' => 'Platform Analytics', 'desc' => 'Access platform-wide business analytics and KPI dashboards.'],
                    ['code' => 'audit.view', 'name' => 'Security Audit Logs', 'desc' => 'Inspect administrative audit trails and security events.'],
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

        $oldGrantedCodes = $role->permissions()->pluck('code')->toArray();

        // Sync role permissions in database
        $permissionIds = Permission::whereIn('code', $grantedCodes)->pluck('id');

        \DB::table('role_permissions')->where('role_id', $role->id)->delete();
        foreach ($permissionIds as $pId) {
            \DB::table('role_permissions')->insert([
                'role_id' => $role->id,
                'permission_id' => $pId,
            ]);
        }

        // Security Audit Trail with Old vs New Diffing (SRS Section 142)
        AuditLog::log(
            event: 'permissions_updated',
            auditable: $role,
            newValues: ['permissions_count' => count($grantedCodes), 'permissions' => array_values($grantedCodes)],
            oldValues: ['permissions_count' => count($oldGrantedCodes), 'permissions' => array_values($oldGrantedCodes)],
            notes: "Updated capabilities for role '{$role->name}' from ".count($oldGrantedCodes)." to ".count($grantedCodes)." permissions",
            actorId: auth()->id()
        );

        return redirect()->route('dashboard.admin.permissions', ['role_id' => $role->id])
            ->with('success', "Permissions for role '{$role->name}' were successfully updated ({$permissionIds->count()} capabilities enabled).");
    }

    protected function ensureDefaultPermissionsExist(): void
    {
        $allCodes = [
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
                    'properties.view', 'properties.create', 'properties.edit', 'properties.delete', 'properties.gis', 'properties.media', 'properties.analytics', 'units.manage',
                    'listings.submit', 'listings.moderate', 'listings.feature', 'listings.expire',
                    'favorites.manage', 'properties.compare', 'messages.send', 'crm.leads.manage', 'inquiries.manage', 'viewings.request', 'viewings.manage', 'offers.submit', 'offers.manage', 'reviews.submit', 'reviews.manage',
                    'accommodations.manage', 'bookings.create', 'bookings.manage', 'housekeeping.manage',
                    'rental.apply', 'rental.review', 'leases.view', 'leases.create', 'leases.sign', 'leases.terminate', 'maintenance.request', 'maintenance.manage',
                    'developments.manage', 'handovers.manage',
                    'agency.manage', 'commissions.view',
                    'services.provide', 'inspections.manage',
                    'documents.upload', 'documents.view', 'kyc.verify', 'documents.verify', 'kyc.history',
                    'finance.ledger.view', 'finance.payments.view', 'finance.invoices.create', 'finance.invoices.manage', 'finance.refunds.process', 'finance.escrow.release', 'finance.payouts.manage', 'finance.reconciliation', 'finance.reports.generate',
                    'support.tickets', 'support.context', 'disputes.arbitrate', 'fraud.flag', 'moderation.actions', 'support.escalate',
                    'profile.update', 'profile.preferences', 'profile.security', 'users.view', 'users.manage', 'roles.assign', 'roles.manage', 'settings.update', 'manage_locations', 'categories.manage', 'analytics.view', 'audit.view', 'admin',
                ];
            case 'Guest':
                return [
                    'properties.view', 'properties.compare',
                ];
            case 'Buyer':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'properties.compare', 'favorites.manage',
                    'messages.send', 'viewings.request', 'offers.submit',
                    'leases.view', 'bookings.create', 'maintenance.request',
                    'documents.upload', 'documents.view',
                ];
            case 'Tenant':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'favorites.manage', 'messages.send',
                    'viewings.request', 'rental.apply', 'leases.view', 'leases.sign',
                    'maintenance.request', 'documents.upload', 'documents.view',
                ];
            case 'Guest / Traveler':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'favorites.manage', 'messages.send',
                    'bookings.create', 'reviews.submit',
                    'documents.upload', 'documents.view',
                ];
            case 'Property Owner':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'properties.create', 'properties.edit', 'properties.delete',
                    'properties.media', 'properties.analytics', 'listings.submit',
                    'messages.send', 'inquiries.manage', 'viewings.manage',
                    'offers.manage', 'leases.view', 'documents.upload', 'documents.view',
                ];
            case 'Host':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'properties.create', 'properties.edit',
                    'properties.media', 'listings.submit', 'accommodations.manage',
                    'bookings.manage', 'housekeeping.manage', 'messages.send',
                    'reviews.manage', 'documents.upload', 'documents.view',
                ];
            case 'Landlord':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'properties.create', 'properties.edit',
                    'properties.media', 'listings.submit', 'rental.review',
                    'leases.view', 'leases.create', 'leases.sign', 'leases.terminate',
                    'maintenance.manage', 'messages.send', 'inquiries.manage',
                    'documents.upload', 'documents.view',
                ];
            case 'Agent':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'properties.create', 'properties.edit',
                    'properties.media', 'listings.submit', 'leases.view', 'leases.create',
                    'crm.leads.manage', 'inquiries.manage', 'viewings.manage',
                    'offers.manage', 'commissions.view', 'messages.send',
                    'documents.upload', 'documents.view',
                ];
            case 'Real Estate Company':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'properties.create', 'properties.edit',
                    'properties.media', 'properties.analytics', 'listings.submit',
                    'agency.manage', 'crm.leads.manage', 'inquiries.manage',
                    'commissions.view', 'leases.create', 'finance.invoices.create',
                    'finance.payments.view', 'documents.upload', 'documents.view',
                ];
            case 'Developer':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'properties.create', 'properties.edit',
                    'properties.media', 'properties.analytics', 'developments.manage',
                    'units.manage', 'handovers.manage', 'listings.submit', 'listings.feature',
                    'finance.invoices.create', 'finance.payments.view', 'leases.create', 'leases.sign',
                    'documents.upload', 'documents.view',
                ];
            case 'Property Manager':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'properties.create', 'properties.edit',
                    'listings.submit', 'listings.expire', 'finance.invoices.create',
                    'leases.view', 'leases.create', 'leases.sign', 'leases.terminate',
                    'maintenance.manage', 'housekeeping.manage', 'kyc.verify',
                    'documents.upload', 'documents.view',
                ];
            case 'Professional Service Provider':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'services.provide', 'inspections.manage',
                    'documents.upload', 'documents.view',
                ];
            case 'Moderator':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'listings.moderate', 'listings.expire',
                    'fraud.flag', 'moderation.actions', 'reviews.manage',
                ];
            case 'Verification Officer':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'kyc.verify', 'documents.verify', 'kyc.history',
                    'documents.view',
                ];
            case 'Finance Officer':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'finance.ledger.view', 'finance.payments.view', 'finance.invoices.create',
                    'finance.invoices.manage', 'finance.refunds.process', 'finance.escrow.release',
                    'finance.payouts.manage', 'finance.reconciliation', 'finance.reports.generate',
                    'commissions.view', 'audit.view',
                ];
            case 'Customer Support':
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'support.tickets', 'support.context',
                    'disputes.arbitrate', 'fraud.flag', 'support.escalate',
                ];
            case 'Seller':
                return [
                    'properties.view', 'properties.create', 'properties.edit',
                    'listings.submit', 'messages.send', 'documents.upload', 'documents.view',
                ];
            case 'User':
            default:
                return [
                    'profile.update', 'profile.preferences', 'profile.security',
                    'properties.view', 'favorites.manage',
                ];
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
