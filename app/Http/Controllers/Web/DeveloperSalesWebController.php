<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeveloperSalesWebController extends Controller
{
    protected $units = [
        ['id' => 101, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-101', 'type' => '2 Bedroom', 'floor' => 1, 'price' => '8,500,000 ETB', 'status' => 'available'],
        ['id' => 102, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-102', 'type' => '3 Bedroom', 'floor' => 1, 'price' => '12,000,000 ETB', 'status' => 'reserved', 'buyer' => 'Abebe Kebede'],
        ['id' => 201, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-201', 'type' => '2 Bedroom', 'floor' => 2, 'price' => '8,600,000 ETB', 'status' => 'contracted', 'buyer' => 'Sara Tadesse'],
        ['id' => 202, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-202', 'type' => '3 Bedroom', 'floor' => 2, 'price' => '12,200,000 ETB', 'status' => 'sold', 'buyer' => 'Dawit Girma'],
        ['id' => 301, 'project' => 'Skyline Towers', 'building' => 'Tower A', 'number' => 'A-301', 'type' => 'Penthouse', 'floor' => 3, 'price' => '25,000,000 ETB', 'status' => 'blocked'],
    ];

    public function units()
    {
        $units = $this->units;
        return view('frontend.developer.sales.units', compact('units'));
    }

    public function unitDetail($id)
    {
        $unit = collect($this->units)->firstWhere('id', (int) $id);
        if (!$unit) abort(404);

        $paymentPlan = null;
        $installments = [];

        if ($unit['status'] === 'reserved' || $unit['status'] === 'contracted' || $unit['status'] === 'sold') {
            $paymentPlan = [
                'name' => 'Standard 30/70 Plan',
                'down_payment' => '30%',
                'installments' => 4,
                'total_price' => $unit['price']
            ];
            
            $installments = [
                ['id' => 1, 'description' => 'Down Payment (30%)', 'amount' => '3,600,000 ETB', 'due_date' => '2026-08-01', 'status' => 'paid', 'paid_date' => '2026-07-25'],
                ['id' => 2, 'description' => 'Foundation Stage (20%)', 'amount' => '2,400,000 ETB', 'due_date' => '2026-12-01', 'status' => 'paid', 'paid_date' => '2026-11-15'],
                ['id' => 3, 'description' => 'Structural Completion (20%)', 'amount' => '2,400,000 ETB', 'due_date' => '2027-04-01', 'status' => 'pending', 'paid_date' => null],
                ['id' => 4, 'description' => 'Handover (30%)', 'amount' => '3,600,000 ETB', 'due_date' => '2027-12-01', 'status' => 'pending', 'paid_date' => null],
            ];
        }

        return view('frontend.developer.sales.unit_detail', compact('unit', 'paymentPlan', 'installments'));
    }

    public function reservation($id)
    {
        $unit = collect($this->units)->firstWhere('id', (int) $id);
        if (!$unit) abort(404);

        return view('frontend.developer.sales.reservation', compact('unit'));
    }

    public function buyers()
    {
        $buyers = [
            ['id' => 1, 'name' => 'Abebe Kebede', 'phone' => '+251 911 234567', 'email' => 'abebe@example.com', 'units' => 1, 'status' => 'active'],
            ['id' => 2, 'name' => 'Sara Tadesse', 'phone' => '+251 912 345678', 'email' => 'sara@example.com', 'units' => 2, 'status' => 'active'],
            ['id' => 3, 'name' => 'Dawit Girma', 'phone' => '+251 913 456789', 'email' => 'dawit@example.com', 'units' => 1, 'status' => 'completed'],
        ];
        
        return view('frontend.developer.sales.buyers', compact('buyers'));
    }
    
    public function buyerDetail($id)
    {
        $buyers = [
            ['id' => 1, 'name' => 'Abebe Kebede', 'phone' => '+251 911 234567', 'email' => 'abebe@example.com', 'units' => 1, 'status' => 'active'],
        ];
        $buyer = collect($buyers)->firstWhere('id', (int) $id);
        if (!$buyer) $buyer = collect($buyers)->first();
        
        $purchased_units = [
            $this->units[1]
        ];

        return view('frontend.developer.sales.buyer_detail', compact('buyer', 'purchased_units'));
    }

    public function paymentPlans()
    {
        $plans = [
            ['id' => 1, 'name' => 'Standard 30/70 Plan', 'down_payment' => '30%', 'milestones' => 4, 'active_contracts' => 45],
            ['id' => 2, 'name' => 'Early Bird 50/50', 'down_payment' => '50%', 'milestones' => 2, 'active_contracts' => 12],
            ['id' => 3, 'name' => 'Full Payment (Discounted)', 'down_payment' => '100%', 'milestones' => 1, 'active_contracts' => 8],
        ];
        
        return view('frontend.developer.sales.payment_plans', compact('plans'));
    }

    public function contracts()
    {
        $contracts = [
            ['id' => 'CNT-2026-001', 'unit' => 'A-201', 'buyer' => 'Sara Tadesse', 'date' => '2026-09-10', 'value' => '8,600,000 ETB', 'status' => 'signed'],
            ['id' => 'CNT-2026-002', 'unit' => 'A-202', 'buyer' => 'Dawit Girma', 'date' => '2026-08-15', 'value' => '12,200,000 ETB', 'status' => 'completed'],
            ['id' => 'CNT-2026-003', 'unit' => 'A-102', 'buyer' => 'Abebe Kebede', 'date' => '2026-10-05', 'value' => '12,000,000 ETB', 'status' => 'draft'],
        ];
        
        return view('frontend.developer.sales.contracts', compact('contracts'));
    }

    public function handovers()
    {
        $handovers = [
            ['id' => 1, 'unit' => 'A-202', 'buyer' => 'Dawit Girma', 'scheduled_date' => '2027-12-05', 'status' => 'scheduled'],
            ['id' => 2, 'unit' => 'B-105', 'buyer' => 'Yohannes Alemu', 'scheduled_date' => '2027-12-10', 'status' => 'pending_snagging'],
        ];
        
        return view('frontend.developer.sales.handovers', compact('handovers'));
    }
}
