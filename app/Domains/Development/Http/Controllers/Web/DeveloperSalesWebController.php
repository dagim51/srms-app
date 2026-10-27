<?php

namespace App\Domains\Development\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DevelopmentUnit;
use App\Models\HandoverRecord;
use App\Models\PaymentPlan;
use App\Models\User;
use Illuminate\Http\Request;

class DeveloperSalesWebController extends Controller
{
    protected function getUnitsQuery()
    {
        return DevelopmentUnit::with(['building.project', 'property', 'handoverRecord.buyer'])->latest();
    }

    protected function formatUnit($u)
    {
        $buyerName = $u->handoverRecord?->buyer?->name ?? ($u->status !== 'available' ? 'Registered Buyer' : null);

        return [
            'id' => $u->id,
            'project' => $u->building?->project?->name ?? 'Skyline Towers',
            'building' => $u->building?->name ?? 'Tower A',
            'number' => $u->unit_number ?? 'Unit',
            'type' => $u->floor_name ?? 'Apartment',
            'floor' => 1,
            'price' => number_format($u->price ?? 8500000).' ETB',
            'status' => $u->status ?? 'available',
            'buyer' => $buyerName,
        ];
    }

    public function units(Request $request)
    {
        $query = $this->getUnitsQuery();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('unit_number', 'like', "%{$q}%")
                    ->orWhere('floor_name', 'like', "%{$q}%")
                    ->orWhereHas('building.project', function ($p) use ($q) {
                        $p->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $units = $query->paginate(20)->withQueryString()->through(fn ($u) => $this->formatUnit($u));

        return view('frontend.developer.sales.units', [
            'units' => $units,
        ]);
    }

    public function unitDetail($id)
    {
        $dbUnit = DevelopmentUnit::with(['building.project.paymentPlans.installments', 'property', 'handoverRecord.buyer'])->findOrFail($id);
        $unit = $this->formatUnit($dbUnit);
        $plan = $dbUnit->building?->project?->paymentPlans?->first() ?? PaymentPlan::with('installments')->first();

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
                $basePrice = $dbUnit ? $dbUnit->price : 0;
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

        return view('frontend.developer.sales.unit_detail', compact('unit', 'paymentPlan', 'installments', 'dbUnit'));
    }

    public function reservation($id)
    {
        $dbUnit = DevelopmentUnit::with(['building.project'])->findOrFail($id);
        $unit = $this->formatUnit($dbUnit);

        $buyers = User::orderBy('name')->get(['id', 'name', 'phone', 'email']);
        $paymentPlans = PaymentPlan::with('installments')->get();

        return view('frontend.developer.sales.reservation', compact('dbUnit', 'unit', 'buyers', 'paymentPlans'));
    }

    public function storeReservation(Request $request, $id)
    {
        $dbUnit = DevelopmentUnit::findOrFail($id);

        $data = $request->validate([
            'buyer_id' => 'required|exists:users,id',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $dbUnit->update(['status' => 'reserved']);

        HandoverRecord::updateOrCreate(
            ['unit_id' => $dbUnit->id],
            [
                'buyer_id' => $data['buyer_id'],
                'status' => 'pending',
                'keys_released_at' => null,
            ]
        );

        return redirect()->route('dashboard.sales.units.show', $dbUnit->id)
            ->with('success', __('Unit :number reserved successfully.', ['number' => $dbUnit->unit_number]));
    }

    public function buyers(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $query->where(function ($sub) use ($q) {
                $sub->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$q}%"])
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $query->withCount(['handoverRecords as units_count']);

        $buyers = $query->paginate(20)->withQueryString()->through(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'phone' => $u->phone ?? '—',
                'email' => $u->email,
                'units' => $u->units_count ?? 0,
                'status' => $u->status ?? 'active',
            ];
        });

        return view('frontend.developer.sales.buyers', [
            'buyers' => $buyers,
        ]);
    }

    public function storeBuyer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:50',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => bcrypt('BuyerPass@2026'),
            'status' => 'active',
        ]);

        return redirect()->route('dashboard.sales.buyers')
            ->with('success', __('Off-plan prospective buyer registered successfully.'));
    }

    public function buyerDetail($id)
    {
        $user = User::withCount(['handoverRecords as units_count'])->findOrFail($id);
        $buyer = [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone ?? '—',
            'email' => $user->email,
            'units' => $user->units_count ?? 0,
            'status' => $user->status ?? 'active',
        ];

        $purchasedRecords = HandoverRecord::with(['unit.building.project'])
            ->where('buyer_id', $user->id)
            ->get();

        $purchased_units = $purchasedRecords->map(function ($record) {
            return [
                'id' => $record->unit_id,
                'number' => $record->unit?->unit_number ?? 'Unit',
                'project' => $record->unit?->building?->project?->name ?? 'Project',
                'building' => $record->unit?->building?->name ?? 'Building',
                'type' => $record->unit?->floor_name ?? 'Apartment',
                'status' => $record->status ?? 'pending',
                'price' => number_format($record->unit?->price ?? 0).' ETB',
            ];
        })->toArray();

        return view('frontend.developer.sales.buyer_detail', compact('buyer', 'purchased_units'));
    }

    public function paymentPlans()
    {
        $dbPlans = PaymentPlan::with('installments')->get();
        $plans = $dbPlans->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'down_payment' => '30%',
                'milestones' => $p->installments->count(),
                'active_contracts' => 0,
            ];
        })->toArray();

        return view('frontend.developer.sales.payment_plans', compact('plans'));
    }

    public function contracts(Request $request)
    {
        $query = DevelopmentUnit::with(['building.project', 'handoverRecord.buyer'])
            ->whereIn('status', ['reserved', 'contracted', 'sold']);

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('unit_number', 'like', "%{$q}%")
                    ->orWhereHas('building.project', function ($p) use ($q) {
                        $p->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $contracts = $query->paginate(20)->withQueryString()->through(function ($u) {
            $buyerName = $u->handoverRecord?->buyer?->name ?? 'Registered Buyer';

            return [
                'id' => 'CNT-'.strtoupper(substr($u->id, 0, 8)),
                'unit_id' => $u->id,
                'unit' => $u->unit_number ?? 'Unit',
                'buyer' => $buyerName,
                'date' => $u->updated_at ? $u->updated_at->toDateString() : now()->toDateString(),
                'value' => number_format($u->price ?? 0).' ETB',
                'status' => $u->status === 'sold' ? 'completed' : ($u->status === 'contracted' ? 'signed' : 'draft'),
            ];
        });

        return view('frontend.developer.sales.contracts', [
            'contracts' => $contracts,
        ]);
    }

    public function updateContractStatus(Request $request, $id)
    {
        $unit = DevelopmentUnit::where('id', $id)->orWhere('unit_number', $id)->firstOrFail();

        $request->validate([
            'status' => 'required|in:draft,signed,contracted,completed,sold',
        ]);

        $status = match ($request->status) {
            'draft' => 'reserved',
            'signed', 'contracted' => 'contracted',
            'completed', 'sold' => 'sold',
            default => 'contracted',
        };

        $unit->update(['status' => $status]);

        if ($status === 'sold' && $unit->handoverRecord) {
            $unit->handoverRecord->update(['status' => 'inspected']);
        }

        return back()->with('success', __('Sales contract status updated to :status.', ['status' => ucfirst($status)]));
    }

    public function handovers()
    {
        $records = HandoverRecord::with(['unit.building.project', 'buyer'])->latest()->paginate(20);
        $handovers = $records->through(function ($record) {
            return [
                'id' => $record->id,
                'unit' => $record->unit?->unit_number ?? 'Unit',
                'buyer' => $record->buyer?->name ?? 'Unit Owner',
                'scheduled_date' => $record->keys_released_at ? $record->keys_released_at->toDateString() : ($record->created_at ? $record->created_at->addMonths(1)->toDateString() : now()->toDateString()),
                'status' => $record->status ?? 'pending',
                'checklist' => $record->inspection_checklist ?? ['plumbing' => true, 'electrical' => true, 'finishes' => false],
            ];
        });

        return view('frontend.developer.sales.handovers', compact('handovers'));
    }

    public function completeHandover(Request $request, $id)
    {
        $record = HandoverRecord::with('unit')->findOrFail($id);

        $record->update([
            'status' => 'completed',
            'keys_released_at' => now(),
            'inspection_checklist' => [
                'plumbing' => (bool) $request->input('plumbing', true),
                'electrical' => (bool) $request->input('electrical', true),
                'finishes' => (bool) $request->input('finishes', true),
                'notes' => $request->input('notes', 'Inspection completed and keys officially released to buyer.'),
            ],
        ]);

        if ($record->unit) {
            $record->unit->update(['status' => 'sold']);
        }

        return redirect()->route('dashboard.sales.handovers')
            ->with('success', __('Key handover completed and keys officially released.'));
    }
}
