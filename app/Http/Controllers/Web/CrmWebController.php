<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrmWebController extends Controller
{
    protected $leads = [
        [
            'id' => 101, 'name' => 'Abebe Kebede', 'status' => 'New Lead', 
            'property' => 'Modern Apartment in Bole', 'value' => '45,000 ETB/mo',
            'last_contact' => '2026-10-07', 'assigned_to' => 'Meron Alemu'
        ],
        [
            'id' => 102, 'name' => 'Sara Tadesse', 'status' => 'Viewing', 
            'property' => 'Luxury Villa in Old Airport', 'value' => '15,000,000 ETB',
            'last_contact' => '2026-10-06', 'assigned_to' => 'Meron Alemu'
        ],
        [
            'id' => 103, 'name' => 'Dawit Girma', 'status' => 'Offer', 
            'property' => 'Commercial Space Kazanchis', 'value' => '120,000 ETB/mo',
            'last_contact' => '2026-10-08', 'assigned_to' => 'Yonas Tesfaye'
        ]
    ];

    public function index()
    {
        $stats = [
            'total_leads' => 45,
            'active_pipeline' => 12,
            'closed_this_month' => 4,
            'tasks_due' => 8,
        ];
        
        $leads = $this->leads;
        
        return view('frontend.crm.index', compact('stats', 'leads'));
    }

    public function leads(Request $request)
    {
        $leads = collect($this->leads);
        
        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $leads = $leads->filter(function($l) use ($q) {
                return str_contains(strtolower($l['name']), $q)
                    || str_contains(strtolower($l['property']), $q)
                    || str_contains(strtolower($l['assigned_to']), $q)
                    || str_contains(strtolower($l['status']), $q);
            });
        }

        if ($request->filled('status')) {
            $leads = $leads->where('status', $request->status);
        }

        return view('frontend.crm.leads', [
            'leads' => $leads->values()
        ]);
    }

    public function leadShow($id)
    {
        $lead = collect($this->leads)->firstWhere('id', (int) $id);
        
        if (!$lead) {
            abort(404);
        }
        
        $timeline = [
            ['type' => 'status', 'title' => 'Moved to Offer', 'date' => '2026-10-08 14:30', 'user' => 'Meron Alemu'],
            ['type' => 'call', 'title' => 'Follow-up call regarding the price', 'date' => '2026-10-08 10:00', 'user' => 'Meron Alemu'],
            ['type' => 'viewing', 'title' => 'Completed viewing for Commercial Space', 'date' => '2026-10-06 15:00', 'user' => 'Meron Alemu'],
            ['type' => 'email', 'title' => 'Sent property brochure', 'date' => '2026-10-05 09:15', 'user' => 'System'],
            ['type' => 'created', 'title' => 'Lead generated via website inquiry', 'date' => '2026-10-04 18:45', 'user' => 'System'],
        ];

        return view('frontend.crm.lead_show', compact('lead', 'timeline'));
    }

    public function pipeline()
    {
        $pipeline = [
            'New Lead' => [
                $this->leads[0]
            ],
            'Contacted' => [],
            'Qualified' => [],
            'Viewing' => [
                $this->leads[1]
            ],
            'Negotiation' => [],
            'Offer' => [
                $this->leads[2]
            ],
            'Closed' => []
        ];
        
        return view('frontend.crm.pipeline', compact('pipeline'));
    }

    public function clients(Request $request)
    {
        $clients = collect([
            ['id' => 1, 'name' => 'Abebe Kebede', 'email' => 'abebe@example.com', 'phone' => '+251 911 234567', 'type' => 'Buyer', 'properties' => 1],
            ['id' => 2, 'name' => 'Sara Tadesse', 'email' => 'sara@example.com', 'phone' => '+251 912 345678', 'type' => 'Tenant', 'properties' => 0],
            ['id' => 3, 'name' => 'Dawit Girma', 'email' => 'dawit@example.com', 'phone' => '+251 913 456789', 'type' => 'Seller', 'properties' => 2],
        ]);

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $clients = $clients->filter(function($c) use ($q) {
                return str_contains(strtolower($c['name']), $q)
                    || str_contains(strtolower($c['email']), $q)
                    || str_contains(strtolower($c['phone']), $q)
                    || str_contains(strtolower($c['type']), $q);
            });
        }

        return view('frontend.crm.clients', [
            'clients' => $clients->values()
        ]);
    }

    public function tasks()
    {
        $tasks = [
            ['id' => 1, 'title' => 'Send contract to Dawit', 'due_date' => '2026-10-09', 'status' => 'pending', 'priority' => 'high'],
            ['id' => 2, 'title' => 'Follow up with Sara on viewing feedback', 'due_date' => '2026-10-08', 'status' => 'pending', 'priority' => 'medium'],
            ['id' => 3, 'title' => 'Call Abebe regarding budget', 'due_date' => '2026-10-10', 'status' => 'completed', 'priority' => 'low'],
        ];
        return view('frontend.crm.tasks', compact('tasks'));
    }

    public function commissions()
    {
        $commissions = [
            ['id' => 1, 'property' => 'Luxury Villa in Old Airport', 'client' => 'Abebe Kebede', 'amount' => '300,000 ETB', 'status' => 'pending', 'date' => '2026-10-01'],
            ['id' => 2, 'property' => 'Modern Apartment in Bole', 'client' => 'Sara Tadesse', 'amount' => '45,000 ETB', 'status' => 'paid', 'date' => '2026-09-15'],
        ];
        return view('frontend.crm.commissions', compact('commissions'));
    }
}
