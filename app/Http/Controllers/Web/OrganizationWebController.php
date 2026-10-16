<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrganizationWebController extends Controller
{
    // Mock data for UI demonstrations
    protected $organization = [
        'id' => 'org_123',
        'name' => 'Addis Estates Ltd.',
        'email' => 'contact@addisestates.com',
        'phone' => '+251 91 123 4567',
        'address' => 'Bole Road, Addis Ababa',
        'status' => 'active',
        'plan' => 'Enterprise',
    ];

    public function index()
    {
        $dbOrgs = \App\Models\Organization::latest()->get();

        if ($dbOrgs->isNotEmpty()) {
            $first = $dbOrgs->first();
            $settings = $first->settings ?? [];
            $organization = [
                'id' => $first->id,
                'name' => $first->name,
                'email' => $settings['email'] ?? 'contact@' . ($first->slug ?: 'org') . '.et',
                'phone' => $settings['phone'] ?? '+251 91 123 4567',
                'address' => $settings['address'] ?? 'Addis Ababa, Ethiopia',
                'status' => $first->status ?: 'active',
                'plan' => $settings['plan'] ?? 'Enterprise',
            ];
        } else {
            $organization = $this->organization;
        }

        $allOrganizations = $dbOrgs->map(function ($o) {
            $s = $o->settings ?? [];
            return [
                'id' => $o->id,
                'name' => $o->name,
                'slug' => $o->slug,
                'email' => $s['email'] ?? '',
                'phone' => $s['phone'] ?? '',
                'address' => $s['address'] ?? '',
                'status' => $o->status ?: 'active',
                'plan' => $s['plan'] ?? 'Enterprise',
            ];
        })->toArray();

        $stats = [
            'total_properties' => 142,
            'active_agents' => 18,
            'branches' => 3,
            'total_leads' => 845,
        ];
        
        return view('frontend.organization.index', compact('organization', 'stats', 'allOrganizations'));
    }

    public function create()
    {
        return view('frontend.organization.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'tin' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'plan' => 'nullable|string|max:50',
        ]);

        try {
            $slugBase = \Illuminate\Support\Str::slug($validated['name']) ?: 'org-' . substr(uniqid(), 0, 6);
            $slug = $slugBase;
            $count = 1;
            while (\App\Models\Organization::where('slug', $slug)->exists()) {
                $slug = $slugBase . '-' . $count++;
            }

            $settings = [
                'type' => $validated['type'] ?? 'Real Estate Agency',
                'tin' => $validated['tin'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? 'Addis Ababa, Ethiopia',
                'plan' => $validated['plan'] ?? 'Enterprise',
            ];

            $org = \App\Models\Organization::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'status' => 'active',
                'settings' => $settings,
            ]);

            // Provision initial branch
            \App\Models\Branch::create([
                'organization_id' => $org->id,
                'name' => $validated['branch_name'] ?: 'Addis Ababa HQ',
                'status' => 'active',
            ]);

            // Attach creator user if logged in
            if (\Illuminate\Support\Facades\Auth::check()) {
                \App\Models\OrganizationMember::firstOrCreate([
                    'organization_id' => $org->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ], [
                    'role' => 'Owner',
                    'status' => 'active',
                ]);
            }

            return redirect()->route('dashboard.organization.index')
                ->with('success', "Organization '{$org->name}' was successfully created with default HQ branch.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Could not create organization: ' . $e->getMessage());
        }
    }

    public function profile()
    {
        $organization = $this->organization;
        return view('frontend.organization.profile', compact('organization'));
    }

    public function branches()
    {
        $branches = [
            ['id' => 1, 'name' => 'Bole HQ', 'address' => 'Bole, Addis Ababa', 'status' => 'active', 'agents' => 12],
            ['id' => 2, 'name' => 'Kazanchis Branch', 'address' => 'Kazanchis, Addis Ababa', 'status' => 'active', 'agents' => 4],
            ['id' => 3, 'name' => 'Piassa Office', 'address' => 'Piassa, Addis Ababa', 'status' => 'inactive', 'agents' => 2],
        ];
        
        return view('frontend.organization.branches', compact('branches'));
    }

    public function teams()
    {
        $teams = [
            ['id' => 1, 'name' => 'Residential Sales', 'members' => 8, 'manager' => 'Abebe Kebede'],
            ['id' => 2, 'name' => 'Commercial Leasing', 'members' => 5, 'manager' => 'Hanna Tadesse'],
            ['id' => 3, 'name' => 'Property Management', 'members' => 5, 'manager' => 'Dawit Girma'],
        ];
        
        return view('frontend.organization.teams', compact('teams'));
    }

    public function members()
    {
        $members = [
            ['id' => 1, 'name' => 'Abebe Kebede', 'role' => 'Admin / Owner', 'branch' => 'Bole HQ', 'status' => 'active'],
            ['id' => 2, 'name' => 'Hanna Tadesse', 'role' => 'Manager', 'branch' => 'Kazanchis Branch', 'status' => 'active'],
            ['id' => 3, 'name' => 'Dawit Girma', 'role' => 'Manager', 'branch' => 'Bole HQ', 'status' => 'active'],
            ['id' => 4, 'name' => 'Meron Alemu', 'role' => 'Agent', 'branch' => 'Bole HQ', 'status' => 'active'],
            ['id' => 5, 'name' => 'Yonas Tesfaye', 'role' => 'Agent', 'branch' => 'Piassa Office', 'status' => 'inactive'],
        ];
        
        return view('frontend.organization.members', compact('members'));
    }
}
