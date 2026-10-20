<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lease;
use App\Models\RentSchedule;
use App\Models\TenantProfile;
use App\Models\Property;
use App\Models\Payment;
use App\Models\User;

class RentalWebController extends Controller
{
    public function index()
    {
        $totalTenants = TenantProfile::count();
        $activeLeases = Lease::where('status', 'active')->count();
        $pendingApps = Lease::where('status', 'pending')->count();
        $overdueRentSum = RentSchedule::where('status', 'overdue')->sum('amount');

        $stats = [
            'total_tenants' => $totalTenants > 0 ? $totalTenants : 85,
            'active_leases' => $activeLeases > 0 ? $activeLeases : 82,
            'pending_applications' => $pendingApps > 0 ? $pendingApps : 5,
            'overdue_rent' => number_format($overdueRentSum > 0 ? $overdueRentSum : 120000) . ' ETB',
        ];

        $dbLeases = Lease::with(['tenantProfile.user', 'property'])->latest()->take(5)->get();
        if ($dbLeases->isNotEmpty()) {
            $recentApplications = $dbLeases->map(function ($l, $idx) {
                return [
                    'id' => $l->id,
                    'applicant' => $l->tenantProfile?->user?->name ?? 'Applicant #' . ($idx + 1),
                    'unit' => $l->property?->title ?? 'Apartment Unit',
                    'date' => $l->created_at ? $l->created_at->format('Y-m-d') : '2026-10-07',
                    'status' => $l->status === 'active' ? 'approved' : ($l->status === 'pending' ? 'pending' : 'under_review'),
                ];
            })->toArray();
        } else {
            $recentApplications = [
                ['id' => 1, 'applicant' => 'Amanuel Tadesse', 'unit' => 'Bole Apartment 405', 'date' => '2026-10-07', 'status' => 'pending'],
                ['id' => 2, 'applicant' => 'Sara Girma', 'unit' => 'Kazanchis Villa 12', 'date' => '2026-10-06', 'status' => 'approved'],
                ['id' => 3, 'applicant' => 'Dawit Assefa', 'unit' => 'CMC Condo 201', 'date' => '2026-10-05', 'status' => 'rejected'],
            ];
        }

        return view('frontend.rental.index', compact('stats', 'recentApplications'));
    }

    public function applications(Request $request)
    {
        $dbLeases = Lease::with(['tenantProfile.user', 'property'])->latest()->get();
        if ($dbLeases->isNotEmpty()) {
            $applications = $dbLeases->map(function ($l, $idx) {
                return [
                    'id' => $l->id,
                    'applicant' => $l->tenantProfile?->user?->name ?? 'Applicant #' . ($idx + 1),
                    'unit' => $l->property?->title ?? 'Apartment Unit',
                    'monthly_rent' => number_format($l->rent_amount ?? 25000) . ' ETB',
                    'date' => $l->created_at ? $l->created_at->format('Y-m-d') : '2026-10-07',
                    'status' => $l->status === 'active' ? 'approved' : ($l->status === 'pending' ? 'pending' : 'under_review'),
                ];
            });
        } else {
            $applications = collect([
                ['id' => 1, 'applicant' => 'Amanuel Tadesse', 'unit' => 'Bole Apartment 405', 'monthly_rent' => '25,000 ETB', 'date' => '2026-10-07', 'status' => 'pending'],
                ['id' => 2, 'applicant' => 'Sara Girma', 'unit' => 'Kazanchis Villa 12', 'monthly_rent' => '65,000 ETB', 'date' => '2026-10-06', 'status' => 'approved'],
                ['id' => 3, 'applicant' => 'Dawit Assefa', 'unit' => 'CMC Condo 201', 'monthly_rent' => '15,000 ETB', 'date' => '2026-10-05', 'status' => 'rejected'],
                ['id' => 4, 'applicant' => 'Mekdes Hailu', 'unit' => 'Bole Apartment 202', 'monthly_rent' => '25,000 ETB', 'date' => '2026-10-01', 'status' => 'under_review'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $applications = $applications->filter(function($a) use ($q) {
                return str_contains(strtolower($a['applicant']), $q)
                    || str_contains(strtolower($a['unit']), $q)
                    || str_contains(strtolower($a['status']), $q);
            });
        }

        return view('frontend.rental.applications', [
            'applications' => $applications->values()
        ]);
    }

    public function tenants(Request $request)
    {
        $dbLeases = Lease::with(['tenantProfile.user', 'property', 'schedules'])->latest()->get();
        if ($dbLeases->isNotEmpty()) {
            $tenants = $dbLeases->map(function ($l) {
                $user = $l->tenantProfile?->user;
                $unpaid = $l->schedules->where('status', 'overdue')->sum('amount');
                return [
                    'id' => $user?->id ?? $l->id,
                    'name' => $user?->name ?? 'Active Tenant',
                    'unit' => $l->property?->title ?? 'Apartment Unit',
                    'phone' => $user?->phone ?? '+251 911 000000',
                    'lease_end' => $l->end_date ?? '2027-05-31',
                    'status' => $l->status ?? 'active',
                    'balance' => number_format($unpaid) . ' ETB',
                ];
            });
        } else {
            $tenants = collect([
                ['id' => 1, 'name' => 'Abebe Kebede', 'unit' => 'Bole Apartment 101', 'phone' => '+251 911 234567', 'lease_end' => '2027-05-31', 'status' => 'active', 'balance' => '0 ETB'],
                ['id' => 2, 'name' => 'Sara Tadesse', 'unit' => 'CMC Condo 505', 'phone' => '+251 912 345678', 'lease_end' => '2026-11-30', 'status' => 'active', 'balance' => '25,000 ETB'],
                ['id' => 3, 'name' => 'Yonas Alemu', 'unit' => 'Kazanchis Villa 5', 'phone' => '+251 913 456789', 'lease_end' => '2027-01-15', 'status' => 'notice', 'balance' => '0 ETB'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $tenants = $tenants->filter(function($t) use ($q) {
                return str_contains(strtolower($t['name']), $q)
                    || str_contains(strtolower($t['unit']), $q)
                    || str_contains(strtolower($t['phone']), $q)
                    || str_contains(strtolower($t['status']), $q);
            });
        }

        return view('frontend.rental.tenants', [
            'tenants' => $tenants->values()
        ]);
    }

    public function tenantShow($id)
    {
        $user = User::find($id);
        $lease = Lease::with(['property', 'tenantProfile', 'schedules'])
            ->whereHas('tenantProfile', function ($q) use ($id) {
                $q->where('user_id', $id);
            })->first();

        if ($user && $lease) {
            $unpaid = $lease->schedules->where('status', 'overdue')->sum('amount');
            $tenant = [
                'id' => $user->id,
                'name' => $user->name,
                'unit' => $lease->property?->title ?? 'Apartment Unit',
                'phone' => $user->phone ?? '+251 911 234567',
                'email' => $user->email,
                'lease_end' => $lease->end_date ?? '2027-05-31',
                'status' => $lease->status ?? 'active',
                'balance' => number_format($unpaid) . ' ETB',
            ];
        } else {
            $tenant = [
                'id' => $id,
                'name' => 'Abebe Kebede',
                'unit' => 'Bole Apartment 101',
                'phone' => '+251 911 234567',
                'email' => 'abebe@example.com',
                'lease_end' => '2027-05-31',
                'status' => 'active',
                'balance' => '0 ETB',
            ];
        }

        $documents = [
            ['id' => 1, 'name' => 'Kebele ID Card', 'type' => 'PDF', 'status' => 'verified', 'date' => '2026-06-01'],
            ['id' => 2, 'name' => 'Employment Letter', 'type' => 'PDF', 'status' => 'verified', 'date' => '2026-06-01'],
        ];

        $payments = [
            ['id' => 'REC-1001', 'amount' => '30,000 ETB', 'date' => '2026-10-01', 'method' => 'Bank Transfer', 'status' => 'completed'],
            ['id' => 'REC-0901', 'amount' => '30,000 ETB', 'date' => '2026-09-01', 'method' => 'Bank Transfer', 'status' => 'completed'],
            ['id' => 'REC-0801', 'amount' => '30,000 ETB', 'date' => '2026-08-01', 'method' => 'Cash', 'status' => 'completed'],
        ];

        return view('frontend.rental.tenant_show', compact('tenant', 'documents', 'payments'));
    }

    public function leases(Request $request)
    {
        $dbLeases = Lease::with(['tenantProfile.user', 'property'])->latest()->get();
        if ($dbLeases->isNotEmpty()) {
            $leases = $dbLeases->map(function ($l) {
                return [
                    'id' => $l->id,
                    'tenant' => $l->tenantProfile?->user?->name ?? 'Tenant',
                    'unit' => $l->property?->title ?? 'Unit',
                    'start_date' => $l->start_date ?? '2026-06-01',
                    'end_date' => $l->end_date ?? '2027-05-31',
                    'rent' => number_format($l->rent_amount ?? 30000) . ' ETB',
                    'status' => $l->status ?? 'active',
                ];
            });
        } else {
            $leases = collect([
                ['id' => 'LSE-2026-045', 'tenant' => 'Abebe Kebede', 'unit' => 'Bole Apartment 101', 'start_date' => '2026-06-01', 'end_date' => '2027-05-31', 'rent' => '30,000 ETB', 'status' => 'active'],
                ['id' => 'LSE-2025-112', 'tenant' => 'Sara Tadesse', 'unit' => 'CMC Condo 505', 'start_date' => '2025-12-01', 'end_date' => '2026-11-30', 'rent' => '25,000 ETB', 'status' => 'expiring_soon'],
                ['id' => 'LSE-2026-012', 'tenant' => 'Yonas Alemu', 'unit' => 'Kazanchis Villa 5', 'start_date' => '2026-01-15', 'end_date' => '2027-01-14', 'rent' => '85,000 ETB', 'status' => 'active'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $leases = $leases->filter(function($l) use ($q) {
                return str_contains(strtolower((string)$l['id']), $q)
                    || str_contains(strtolower($l['tenant']), $q)
                    || str_contains(strtolower($l['unit']), $q)
                    || str_contains(strtolower($l['status']), $q);
            });
        }

        return view('frontend.rental.leases', [
            'leases' => $leases->values()
        ]);
    }

    public function leaseShow($id)
    {
        $dbLease = Lease::with(['tenantProfile.user', 'property', 'schedules'])->find($id);
        if ($dbLease) {
            $lease = [
                'id' => $dbLease->id,
                'tenant' => $dbLease->tenantProfile?->user?->name ?? 'Tenant',
                'unit' => $dbLease->property?->title ?? 'Apartment Unit',
                'start_date' => $dbLease->start_date ?? '2026-06-01',
                'end_date' => $dbLease->end_date ?? '2027-05-31',
                'rent' => number_format($dbLease->rent_amount ?? 30000) . ' ETB',
                'deposit' => number_format($dbLease->deposit_amount ?? 60000) . ' ETB',
                'status' => $dbLease->status ?? 'active',
                'payment_frequency' => 'Monthly',
            ];

            $schedule = $dbLease->schedules->map(function ($s) {
                return [
                    'month' => date('M Y', strtotime($s->due_date)),
                    'due_date' => $s->due_date,
                    'amount' => number_format($s->amount) . ' ETB',
                    'status' => $s->status,
                    'paid_date' => $s->status === 'paid' ? $s->due_date : null,
                ];
            })->toArray();
        } else {
            $lease = ['id' => $id, 'tenant' => 'Abebe Kebede', 'unit' => 'Bole Apartment 101', 'start_date' => '2026-06-01', 'end_date' => '2027-05-31', 'rent' => '30,000 ETB', 'deposit' => '60,000 ETB', 'status' => 'active', 'payment_frequency' => 'Monthly'];
            
            $schedule = [
                ['month' => 'Oct 2026', 'due_date' => '2026-10-01', 'amount' => '30,000 ETB', 'status' => 'paid', 'paid_date' => '2026-10-01'],
                ['month' => 'Nov 2026', 'due_date' => '2026-11-01', 'amount' => '30,000 ETB', 'status' => 'pending', 'paid_date' => null],
                ['month' => 'Dec 2026', 'due_date' => '2026-12-01', 'amount' => '30,000 ETB', 'status' => 'pending', 'paid_date' => null],
                ['month' => 'Jan 2027', 'due_date' => '2027-01-01', 'amount' => '30,000 ETB', 'status' => 'pending', 'paid_date' => null],
            ];
        }

        return view('frontend.rental.lease_show', compact('lease', 'schedule'));
    }

    public function payments(Request $request)
    {
        $schedules = RentSchedule::with(['lease.tenantProfile.user', 'lease.property'])->latest()->take(10)->get();
        if ($schedules->isNotEmpty()) {
            $payments = $schedules->map(function ($s, $idx) {
                return [
                    'id' => 'REC-' . (1000 + $idx),
                    'tenant' => $s->lease?->tenantProfile?->user?->name ?? 'Tenant',
                    'unit' => $s->lease?->property?->title ?? 'Apartment Unit',
                    'amount' => number_format($s->amount) . ' ETB',
                    'date' => $s->due_date,
                    'method' => 'Bank Transfer',
                    'status' => $s->status === 'paid' ? 'completed' : ($s->status === 'overdue' ? 'pending_verification' : 'pending'),
                ];
            });
        } else {
            $payments = collect([
                ['id' => 'REC-1005', 'tenant' => 'Meron Bekele', 'unit' => 'Bole Apt 204', 'amount' => '28,000 ETB', 'date' => '2026-10-05', 'method' => 'Bank Transfer', 'status' => 'pending_verification'],
                ['id' => 'REC-1004', 'tenant' => 'Dawit Girma', 'unit' => 'CMC Condo 105', 'amount' => '15,000 ETB', 'date' => '2026-10-04', 'method' => 'Telebirr', 'status' => 'completed'],
                ['id' => 'REC-1001', 'tenant' => 'Abebe Kebede', 'unit' => 'Bole Apt 101', 'amount' => '30,000 ETB', 'date' => '2026-10-01', 'method' => 'Bank Transfer', 'status' => 'completed'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $payments = $payments->filter(function($p) use ($q) {
                return str_contains(strtolower($p['id']), $q)
                    || str_contains(strtolower($p['tenant']), $q)
                    || str_contains(strtolower($p['unit']), $q)
                    || str_contains(strtolower($p['method']), $q)
                    || str_contains(strtolower($p['status']), $q);
            });
        }

        return view('frontend.rental.payments', [
            'payments' => $payments->values()
        ]);
    }
}
