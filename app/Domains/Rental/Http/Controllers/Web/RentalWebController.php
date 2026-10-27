<?php

namespace App\Domains\Rental\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\RentSchedule;
use App\Models\TenantProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RentalWebController extends Controller
{
    public function index()
    {
        $totalTenants = TenantProfile::count();
        $activeLeases = Lease::where('status', 'active')->count();
        $pendingApps = Lease::where('status', 'pending')->count();
        $overdueRentSum = RentSchedule::where('status', 'overdue')->sum('amount');

        $stats = [
            'total_tenants' => $totalTenants,
            'active_leases' => $activeLeases,
            'pending_applications' => $pendingApps,
            'overdue_rent' => number_format($overdueRentSum) . ' ETB',
        ];

        $recentApplications = Lease::with(['tenantProfile.user', 'property'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($l, $idx) {
                return [
                    'id' => $l->id,
                    'applicant' => $l->tenantProfile?->user?->name ?? 'Applicant #' . ($idx + 1),
                    'unit' => $l->property?->title ?? 'Apartment Unit',
                    'date' => $l->created_at ? $l->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                    'status' => $l->status === 'active' ? 'approved' : ($l->status === 'pending' ? 'pending' : ($l->status === 'rejected' ? 'rejected' : 'under_review')),
                ];
            })->toArray();

        $properties = Property::orderBy('title')->get(['id', 'title']);
        $tenants = User::orderBy('name')->get(['id', 'name', 'phone']);

        return view('frontend.rental.index', compact('stats', 'recentApplications', 'properties', 'tenants'));
    }

    public function applications(Request $request)
    {
        $query = Lease::with(['tenantProfile.user', 'property'])->latest();

        if ($request->filled('status')) {
            $status = strtolower($request->status);
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('tenantProfile.user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%");
                })->orWhereHas('property', function ($p) use ($q) {
                    $p->where('title', 'like', "%{$q}%");
                });
            });
        }

        $applications = $query->paginate(20)->withQueryString()->through(function ($l, $idx) {
            return [
                'id' => $l->id,
                'applicant' => $l->tenantProfile?->user?->name ?? 'Applicant #' . ($idx + 1),
                'unit' => $l->property?->title ?? 'Apartment Unit',
                'monthly_rent' => number_format($l->rent_amount ?? 0) . ' ETB',
                'date' => $l->created_at ? $l->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'status' => $l->status === 'active' ? 'approved' : ($l->status === 'pending' ? 'pending' : ($l->status === 'rejected' ? 'rejected' : 'under_review')),
            ];
        });

        $properties = Property::orderBy('title')->get(['id', 'title']);
        $tenants = User::orderBy('name')->get(['id', 'name', 'phone']);

        return view('frontend.rental.applications', compact('applications', 'properties', 'tenants'));
    }

    public function storeApplication(Request $request)
    {
        $data = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'user_id' => 'required|exists:users,id',
            'rent_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'employment_status' => 'nullable|string|max:100',
            'annual_income' => 'nullable|numeric|min:0',
        ]);

        $tenantProfile = TenantProfile::firstOrCreate(
            ['user_id' => $data['user_id']],
            [
                'employment_status' => $data['employment_status'] ?? 'Employed',
                'annual_income' => $data['annual_income'] ?? ($data['rent_amount'] * 12),
                'background_check_status' => 'pending',
            ]
        );

        $property = Property::findOrFail($data['property_id']);

        Lease::create([
            'property_id' => $property->id,
            'tenant_profile_id' => $tenantProfile->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'rent_amount' => $data['rent_amount'],
            'deposit_amount' => $data['deposit_amount'] ?? ($data['rent_amount'] * 2),
            'status' => 'pending',
        ]);

        return redirect()->route('dashboard.rental.applications')
            ->with('success', __('Rental application submitted successfully.'));
    }

    public function updateApplicationStatus(Request $request, $id)
    {
        $lease = Lease::findOrFail($id);

        $request->validate([
            'status' => 'required|in:approved,rejected,under_review,pending',
        ]);

        $targetStatus = $request->status;

        if ($targetStatus === 'approved') {
            $lease->update(['status' => 'active']);

            // Generate monthly rent schedules if not already generated
            if ($lease->schedules()->count() === 0) {
                $start = Carbon::parse($lease->start_date);
                $end = Carbon::parse($lease->end_date);
                $current = $start->copy();
                while ($current->lte($end)) {
                    RentSchedule::create([
                        'lease_id' => $lease->id,
                        'due_date' => $current->toDateString(),
                        'amount' => $lease->rent_amount,
                        'status' => 'unpaid',
                    ]);
                    $current->addMonth();
                }
            }
        } elseif ($targetStatus === 'rejected') {
            $lease->update(['status' => 'rejected']);
        } else {
            $lease->update(['status' => $targetStatus]);
        }

        return back()->with('success', __('Application status updated to :status.', ['status' => ucfirst(str_replace('_', ' ', $targetStatus))]));
    }

    public function tenants(Request $request)
    {
        $query = Lease::with(['tenantProfile.user', 'property', 'schedules'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('tenantProfile.user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                })->orWhereHas('property', function ($p) use ($q) {
                    $p->where('title', 'like', "%{$q}%");
                });
            });
        }

        $tenants = $query->paginate(20)->withQueryString()->through(function ($l) {
            $user = $l->tenantProfile?->user;
            $unpaid = $l->schedules ? $l->schedules->where('status', 'overdue')->sum('amount') : 0;

            return [
                'id' => $user?->id ?? $l->id,
                'name' => $user?->name ?? 'Active Tenant',
                'unit' => $l->property?->title ?? 'Apartment Unit',
                'phone' => $user?->phone ?? '—',
                'lease_end' => $l->end_date ?? '—',
                'status' => $l->status ?? 'active',
                'balance' => number_format($unpaid) . ' ETB',
            ];
        });

        return view('frontend.rental.tenants', [
            'tenants' => $tenants,
        ]);
    }

    public function tenantShow($id)
    {
        $user = User::findOrFail($id);
        $lease = Lease::with(['property', 'tenantProfile', 'schedules'])
            ->whereHas('tenantProfile', function ($q) use ($id) {
                $q->where('user_id', $id);
            })->latest()->first();

        $unpaid = ($lease && $lease->schedules) ? $lease->schedules->where('status', 'overdue')->sum('amount') : 0;
        $tenant = [
            'id' => $user->id,
            'name' => $user->name,
            'unit' => $lease?->property?->title ?? 'No active unit',
            'phone' => $user->phone ?? '—',
            'email' => $user->email,
            'lease_end' => $lease?->end_date ?? '—',
            'status' => $lease?->status ?? 'inactive',
            'balance' => number_format($unpaid) . ' ETB',
        ];

        $payments = ($lease && $lease->schedules) ? $lease->schedules->where('status', 'paid')->map(function ($s) {
            return [
                'id' => 'RCP-' . strtoupper(substr($s->id, 0, 8)),
                'date' => $s->due_date,
                'amount' => number_format($s->amount) . ' ETB',
                'method' => 'Bank / Telebirr',
            ];
        })->values()->toArray() : [];

        $documents = [
            ['name' => 'National ID / Kebele Card Verification', 'date' => $user->created_at ? $user->created_at->format('Y-m-d') : now()->format('Y-m-d')],
            ['name' => 'Proof of Employment / Income Slip', 'date' => $user->created_at ? $user->created_at->format('Y-m-d') : now()->format('Y-m-d')],
        ];

        return view('frontend.rental.tenant_show', compact('tenant', 'documents', 'payments', 'lease'));
    }

    public function leases(Request $request)
    {
        $query = Lease::with(['tenantProfile.user', 'property'])->latest();

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('tenantProfile.user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%");
                })->orWhereHas('property', function ($p) use ($q) {
                    $p->where('title', 'like', "%{$q}%");
                });
            });
        }

        $leases = $query->paginate(20)->withQueryString()->through(function ($l) {
            return [
                'id' => $l->id,
                'tenant' => $l->tenantProfile?->user?->name ?? 'Tenant',
                'unit' => $l->property?->title ?? 'Unit',
                'start_date' => $l->start_date ?? '—',
                'end_date' => $l->end_date ?? '—',
                'rent' => number_format($l->rent_amount ?? 0) . ' ETB',
                'status' => $l->status ?? 'active',
            ];
        });

        $properties = Property::orderBy('title')->get(['id', 'title']);
        $tenants = User::orderBy('name')->get(['id', 'name', 'phone']);

        return view('frontend.rental.leases', compact('leases', 'properties', 'tenants'));
    }

    public function storeLease(Request $request)
    {
        $data = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'rent_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
        ]);

        $tenantProfile = TenantProfile::firstOrCreate(['user_id' => $data['user_id']]);
        $property = Property::findOrFail($data['property_id']);

        $lease = Lease::create([
            'property_id' => $property->id,
            'tenant_profile_id' => $tenantProfile->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'rent_amount' => $data['rent_amount'],
            'deposit_amount' => $data['deposit_amount'] ?? 0,
            'status' => 'active',
        ]);

        // Generate monthly rent schedules
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $current = $start->copy();
        while ($current->lte($end)) {
            RentSchedule::create([
                'lease_id' => $lease->id,
                'due_date' => $current->toDateString(),
                'amount' => $data['rent_amount'],
                'status' => 'unpaid',
            ]);
            $current->addMonth();
        }

        return redirect()->route('dashboard.rental.leases.show', $lease->id)
            ->with('success', __('Lease agreement executed successfully.'));
    }

    public function leaseShow($id)
    {
        $dbLease = Lease::with(['tenantProfile.user', 'property', 'schedules'])->findOrFail($id);
        $lease = [
            'id' => $dbLease->id,
            'tenant' => $dbLease->tenantProfile?->user?->name ?? 'Tenant',
            'tenant_user_id' => $dbLease->tenantProfile?->user?->id,
            'unit' => $dbLease->property?->title ?? 'Apartment Unit',
            'start_date' => $dbLease->start_date ?? '—',
            'end_date' => $dbLease->end_date ?? '—',
            'rent' => number_format($dbLease->rent_amount ?? 0) . ' ETB',
            'deposit' => number_format($dbLease->deposit_amount ?? 0) . ' ETB',
            'status' => $dbLease->status ?? 'active',
            'payment_frequency' => 'Monthly',
        ];

        $schedule = $dbLease->schedules ? $dbLease->schedules->map(function ($s) {
            return [
                'id' => $s->id,
                'month' => date('M Y', strtotime($s->due_date)),
                'due_date' => $s->due_date,
                'amount' => number_format($s->amount) . ' ETB',
                'status' => $s->status,
                'paid_date' => $s->status === 'paid' ? ($s->updated_at ? $s->updated_at->format('Y-m-d') : $s->due_date) : null,
            ];
        })->toArray() : [];

        return view('frontend.rental.lease_show', compact('dbLease', 'lease', 'schedule'));
    }

    public function renewLease(Request $request, $id)
    {
        $lease = Lease::findOrFail($id);

        $data = $request->validate([
            'new_end_date' => 'required|date|after:' . $lease->end_date,
            'new_rent_amount' => 'nullable|numeric|min:0',
        ]);

        $oldEnd = Carbon::parse($lease->end_date);
        $newEnd = Carbon::parse($data['new_end_date']);
        $rentAmount = $data['new_rent_amount'] ?? $lease->rent_amount;

        $lease->update([
            'end_date' => $data['new_end_date'],
            'rent_amount' => $rentAmount,
            'status' => 'active',
        ]);

        // Generate schedule for renewal period
        $current = $oldEnd->copy()->addMonth();
        while ($current->lte($newEnd)) {
            RentSchedule::create([
                'lease_id' => $lease->id,
                'due_date' => $current->toDateString(),
                'amount' => $rentAmount,
                'status' => 'unpaid',
            ]);
            $current->addMonth();
        }

        return back()->with('success', __('Lease renewed until :date.', ['date' => $data['new_end_date']]));
    }

    public function terminateLease(Request $request, $id)
    {
        $lease = Lease::findOrFail($id);
        $lease->update(['status' => 'terminated']);

        return redirect()->route('dashboard.rental.leases')
            ->with('success', __('Lease agreement has been terminated.'));
    }

    public function recordPayment(Request $request, $id)
    {
        $lease = Lease::with('tenantProfile.user')->findOrFail($id);

        $data = $request->validate([
            'schedule_id' => 'required|exists:rent_schedules,id',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $schedule = RentSchedule::where('lease_id', $lease->id)->findOrFail($data['schedule_id']);
        $schedule->update([
            'status' => 'paid',
        ]);

        if ($lease->tenantProfile?->user_id) {
            Invoice::create([
                'user_id' => $lease->tenantProfile->user_id,
                'amount' => $schedule->amount,
                'due_date' => $schedule->due_date,
                'status' => 'paid',
                'notes' => 'Rent payment via ' . $data['payment_method'] . ($data['reference_number'] ? ' (Ref: ' . $data['reference_number'] . ')' : ''),
            ]);
        }

        return back()->with('success', __('Rent payment of :amount ETB recorded successfully via :method.', [
            'amount' => number_format($schedule->amount),
            'method' => $data['payment_method'],
        ]));
    }

    public function payments(Request $request)
    {
        $query = RentSchedule::with(['lease.tenantProfile.user', 'lease.property'])->latest();

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('lease.tenantProfile.user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%");
                })->orWhereHas('lease.property', function ($p) use ($q) {
                    $p->where('title', 'like', "%{$q}%");
                });
            });
        }

        $payments = $query->paginate(20)->withQueryString()->through(function ($s) {
            return [
                'id' => 'REC-' . strtoupper(substr($s->id, 0, 8)),
                'tenant' => $s->lease?->tenantProfile?->user?->name ?? 'Tenant',
                'unit' => $s->lease?->property?->title ?? 'Apartment Unit',
                'amount' => number_format($s->amount ?? 0) . ' ETB',
                'date' => $s->due_date,
                'method' => 'Bank Transfer / Telebirr',
                'status' => $s->status === 'paid' ? 'completed' : ($s->status === 'overdue' ? 'overdue' : 'pending'),
            ];
        });

        return view('frontend.rental.payments', [
            'payments' => $payments,
        ]);
    }
}
