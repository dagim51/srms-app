<?php

namespace App\Domains\Development\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DevelopmentUnit;
use App\Models\PaymentPlan;
use App\Models\User;
use Illuminate\Http\Request;

class DeveloperSalesWebController extends Controller
{
    protected function getUnitsQuery()
    {
        return DevelopmentUnit::with(['building.project', 'property'])->latest();
    }

    protected function formatUnit($u)
    {
        return [
            'id' => $u->id,
            'project' => $u->building?->project?->name ?? 'Skyline Towers',
            'building' => $u->building?->name ?? 'Tower A',
            'number' => $u->unit_number ?? 'Unit',
            'type' => $u->floor_name ?? 'Apartment',
            'floor' => 1,
            'price' => number_format($u->price ?? 8500000).' ETB',
            'status' => $u->status ?? 'available',
            'buyer' => in_array($u->status, ['reserved', 'contracted', 'sold']) ? 'Abebe Kebede' : null,
        ];
    }

    public function units(Request $request)
    {
        $dbUnits = $this->getUnitsQuery()->get();
        if ($dbUnits->isNotEmpty()) {
            $units = $dbUnits->map(fn ($u) => $this->formatUnit($u));
        } else {
            $units = collect([
                ['id' => 101, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-101', 'type' => '2 Bedroom', 'floor' => 1, 'price' => '8,500,000 ETB', 'status' => 'available'],
                ['id' => 102, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-102', 'type' => '3 Bedroom', 'floor' => 1, 'price' => '12,000,000 ETB', 'status' => 'reserved', 'buyer' => 'Abebe Kebede'],
                ['id' => 201, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-201', 'type' => '2 Bedroom', 'floor' => 2, 'price' => '8,600,000 ETB', 'status' => 'contracted', 'buyer' => 'Sara Tadesse'],
                ['id' => 202, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-202', 'type' => '3 Bedroom', 'floor' => 2, 'price' => '12,200,000 ETB', 'status' => 'sold', 'buyer' => 'Dawit Girma'],
                ['id' => 301, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-301', 'type' => 'Penthouse', 'floor' => 3, 'price' => '25,000,000 ETB', 'status' => 'blocked'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $units = $units->filter(function ($u) use ($q) {
                return str_contains(strtolower($u['number']), $q)
                    || str_contains(strtolower($u['type']), $q)
                    || str_contains(strtolower($u['status']), $q)
                    || str_contains(strtolower($u['project'] ?? ''), $q)
                    || str_contains(strtolower($u['buyer'] ?? ''), $q);
            });
        }

        if ($request->filled('status')) {
            $units = $units->where('status', $request->status);
        }

        return view('frontend.developer.sales.units', [
            'units' => $units->values(),
        ]);
    }

    public function unitDetail($id)
    {
        $dbUnit = DevelopmentUnit::with(['building.project.paymentPlans.installments', 'property'])->find($id);
        if (! $dbUnit) {
            $dbUnit = DevelopmentUnit::with(['building.project.paymentPlans.installments', 'property'])->first();
        }

        if ($dbUnit) {
            $unit = $this->formatUnit($dbUnit);
            $plan = $dbUnit->building?->project?->paymentPlans?->first() ?? PaymentPlan::with('installments')->first();
        } else {
            $unit = ['id' => $id, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-102', 'type' => '3 Bedroom', 'floor' => 1, 'price' => '12,000,000 ETB', 'status' => 'reserved', 'buyer' => 'Abebe Kebede'];
            $plan = null;
        }

        $paymentPlan = null;
        $installments = [];

        if ($plan) {
            $paymentPlan = [
                'name' => $plan->name,
                'down_payment' => '30%',
                'installments' => $plan->installments->count() > 0 ? $plan->installments->count() : 4,
                'total_price' => $unit['price'],
            ];

            $installments = $plan->installments->map(function ($inst, $idx) use ($dbUnit) {
                $basePrice = $dbUnit ? $dbUnit->price : 12000000;
                $pct = $inst->percentage ?? 25;
                $amt = ($basePrice * $pct) / 100;

                return [
                    'id' => $idx + 1,
                    'description' => $inst->name.' ('.$pct.'%)',
                    'amount' => number_format($amt).' ETB',
                    'due_date' => now()->addDays($inst->due_days_after_reservation ?? ($idx * 90))->toDateString(),
                    'status' => $idx === 0 ? 'paid' : 'pending',
                    'paid_date' => $idx === 0 ? now()->subDays(10)->toDateString() : null,
                ];
            })->toArray();
        }

        if (empty($installments)) {
            $installments = [
                ['id' => 1, 'description' => 'Down Payment (30%)', 'amount' => '3,600,000 ETB', 'due_date' => '2026-08-01', 'status' => 'paid', 'paid_date' => '2026-07-25'],
                ['id' => 2, 'description' => 'Foundation Stage (20%)', 'amount' => '2,400,000 ETB', 'due_date' => '2026-12-01', 'status' => 'paid', 'paid_date' => '2026-11-15'],
                ['id' => 3, 'description' => 'Structural Completion (20%)', 'amount' => '2,400,000 ETB', 'due_date' => '2027-04-01', 'status' => 'pending', 'paid_date' => null],
                ['id' => 4, 'description' => 'Handover (30%)', 'amount' => '3,600,000 ETB', 'due_date' => '2027-12-01', 'status' => 'pending', 'paid_date' => null],
            ];
            $paymentPlan = [
                'name' => 'Standard 30/70 Plan',
                'down_payment' => '30%',
                'installments' => 4,
                'total_price' => $unit['price'],
            ];
        }

        return view('frontend.developer.sales.unit_detail', compact('unit', 'paymentPlan', 'installments'));
    }

    public function reservation($id)
    {
        $dbUnit = DevelopmentUnit::with(['building.project'])->find($id);
        $unit = $dbUnit ? $this->formatUnit($dbUnit) : ['id' => $id, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-101', 'price' => '8,500,000 ETB'];

        return view('frontend.developer.sales.reservation', compact('unit'));
    }

    public function buyers(Request $request)
    {
        $users = User::take(5)->get();
        if ($users->isNotEmpty()) {
            $buyers = $users->map(function ($u, $idx) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'phone' => $u->phone ?? '+251 911 '.rand(100000, 999999),
                    'email' => $u->email,
                    'units' => ($idx % 2) + 1,
                    'status' => $idx === 2 ? 'completed' : 'active',
                ];
            });
        } else {
            $buyers = collect([
                ['id' => 1, 'name' => 'Abebe Kebede', 'phone' => '+251 911 234567', 'email' => 'abebe@example.com', 'units' => 1, 'status' => 'active'],
                ['id' => 2, 'name' => 'Sara Tadesse', 'phone' => '+251 912 345678', 'email' => 'sara@example.com', 'units' => 2, 'status' => 'active'],
                ['id' => 3, 'name' => 'Dawit Girma', 'phone' => '+251 913 456789', 'email' => 'dawit@example.com', 'units' => 1, 'status' => 'completed'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $buyers = $buyers->filter(function ($b) use ($q) {
                return str_contains(strtolower($b['name']), $q)
                    || str_contains(strtolower($b['email']), $q)
                    || str_contains(strtolower($b['phone']), $q)
                    || str_contains(strtolower($b['status']), $q);
            });
        }

        return view('frontend.developer.sales.buyers', [
            'buyers' => $buyers->values(),
        ]);
    }

    public function buyerDetail($id)
    {
        $user = User::find($id);
        $buyer = $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone ?? '+251 911 234567',
            'email' => $user->email,
            'units' => 1,
            'status' => 'active',
        ] : [
            'id' => 1,
            'name' => 'Abebe Kebede',
            'phone' => '+251 911 234567',
            'email' => 'abebe@example.com',
            'units' => 1,
            'status' => 'active',
        ];

        $dbUnit = DevelopmentUnit::with(['building.project'])->first();
        $purchased_units = [
            $dbUnit ? $this->formatUnit($dbUnit) : [
                'id' => 102, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-102', 'type' => '3 Bedroom', 'floor' => 1, 'price' => '12,000,000 ETB', 'status' => 'reserved', 'buyer' => 'Abebe Kebede',
            ],
        ];

        return view('frontend.developer.sales.buyer_detail', compact('buyer', 'purchased_units'));
    }

    public function paymentPlans()
    {
        $dbPlans = PaymentPlan::with('installments')->get();
        if ($dbPlans->isNotEmpty()) {
            $plans = $dbPlans->map(function ($p, $idx) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'down_payment' => '30%',
                    'milestones' => $p->installments->count() > 0 ? $p->installments->count() : 4,
                    'active_contracts' => 45 - ($idx * 15),
                ];
            })->toArray();
        } else {
            $plans = [
                ['id' => 1, 'name' => 'Standard 30/70 Plan', 'down_payment' => '30%', 'milestones' => 4, 'active_contracts' => 45],
                ['id' => 2, 'name' => 'Early Bird 50/50', 'down_payment' => '50%', 'milestones' => 2, 'active_contracts' => 12],
                ['id' => 3, 'name' => 'Full Payment (Discounted)', 'down_payment' => '100%', 'milestones' => 1, 'active_contracts' => 8],
            ];
        }

        return view('frontend.developer.sales.payment_plans', compact('plans'));
    }

    public function contracts(Request $request)
    {
        $units = DevelopmentUnit::with(['building.project'])->whereIn('status', ['reserved', 'contracted', 'sold'])->get();
        if ($units->isNotEmpty()) {
            $contracts = $units->map(function ($u, $idx) {
                return [
                    'id' => 'CNT-2026-00'.($idx + 1),
                    'unit' => $u->unit_number,
                    'buyer' => 'Buyer #'.($idx + 1),
                    'date' => now()->subDays($idx * 15)->toDateString(),
                    'value' => number_format($u->price).' ETB',
                    'status' => $u->status === 'sold' ? 'completed' : ($u->status === 'contracted' ? 'signed' : 'draft'),
                ];
            });
        } else {
            $contracts = collect([
                ['id' => 'CNT-2026-001', 'unit' => 'A-201', 'buyer' => 'Sara Tadesse', 'date' => '2026-09-10', 'value' => '8,600,000 ETB', 'status' => 'signed'],
                ['id' => 'CNT-2026-002', 'unit' => 'A-202', 'buyer' => 'Dawit Girma', 'date' => '2026-08-15', 'value' => '12,200,000 ETB', 'status' => 'completed'],
                ['id' => 'CNT-2026-003', 'unit' => 'A-102', 'buyer' => 'Abebe Kebede', 'date' => '2026-10-05', 'value' => '12,000,000 ETB', 'status' => 'draft'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $contracts = $contracts->filter(function ($c) use ($q) {
                return str_contains(strtolower($c['id']), $q)
                    || str_contains(strtolower($c['unit']), $q)
                    || str_contains(strtolower($c['buyer']), $q)
                    || str_contains(strtolower($c['status']), $q);
            });
        }

        return view('frontend.developer.sales.contracts', [
            'contracts' => $contracts->values(),
        ]);
    }

    public function handovers()
    {
        $units = DevelopmentUnit::with(['building.project'])->where('status', 'sold')->get();
        if ($units->isNotEmpty()) {
            $handovers = $units->map(function ($u, $idx) {
                return [
                    'id' => $u->id,
                    'unit' => $u->unit_number,
                    'buyer' => 'Buyer #'.($idx + 1),
                    'scheduled_date' => now()->addMonths(6 + $idx)->toDateString(),
                    'status' => $idx === 0 ? 'scheduled' : 'pending_snagging',
                ];
            })->toArray();
        } else {
            $handovers = [
                ['id' => 1, 'unit' => 'A-202', 'buyer' => 'Dawit Girma', 'scheduled_date' => '2027-12-05', 'status' => 'scheduled'],
                ['id' => 2, 'unit' => 'B-105', 'buyer' => 'Yohannes Alemu', 'scheduled_date' => '2027-12-10', 'status' => 'pending_snagging'],
            ];
        }

        return view('frontend.developer.sales.handovers', compact('handovers'));
    }
}
