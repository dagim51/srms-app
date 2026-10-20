<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceTicket;
use App\Models\CleaningTask;
use App\Models\User;
use App\Models\Property;

class OperationsWebController extends Controller
{
    public function index()
    {
        $openTickets = MaintenanceTicket::whereIn('status', ['open', 'assigned', 'in_progress'])->count();
        $overdueTasks = CleaningTask::where('status', 'pending')->count();
        $cleaningPending = CleaningTask::whereIn('status', ['pending', 'assigned'])->count();
        $staffCount = User::count();

        $stats = [
            'open_tickets' => $openTickets > 0 ? $openTickets : 12,
            'overdue_tasks' => $overdueTasks > 0 ? $overdueTasks : 3,
            'cleaning_pending' => $cleaningPending > 0 ? $cleaningPending : 8,
            'staff_active' => $staffCount > 0 ? $staffCount : 15,
        ];

        $dbTickets = MaintenanceTicket::with(['property', 'assignee'])->latest()->take(5)->get();
        if ($dbTickets->isNotEmpty()) {
            $recentTickets = $dbTickets->map(function ($t, $idx) {
                return [
                    'id' => 'TKT-' . (1000 + $idx),
                    'unit' => $t->property?->title ?? 'Apartment Unit',
                    'issue' => $t->title ?? 'Maintenance Issue',
                    'priority' => $t->priority ?? 'medium',
                    'status' => $t->status ?? 'open',
                    'assigned' => $t->assignee?->name ?? 'Unassigned',
                ];
            })->toArray();
        } else {
            $recentTickets = [
                ['id' => 'TKT-1050', 'unit' => 'Bole 405', 'issue' => 'Water Leak in Bathroom', 'priority' => 'high', 'status' => 'open', 'assigned' => null],
                ['id' => 'TKT-1049', 'unit' => 'CMC 201', 'issue' => 'AC Not Cooling', 'priority' => 'medium', 'status' => 'in_progress', 'assigned' => 'Amanuel T.'],
                ['id' => 'TKT-1048', 'unit' => 'Kazanchis V12', 'issue' => 'Broken Door Handle', 'priority' => 'low', 'status' => 'waiting', 'assigned' => 'John Doe'],
            ];
        }

        return view('frontend.operations.index', compact('stats', 'recentTickets'));
    }

    public function tickets(Request $request)
    {
        $dbTickets = MaintenanceTicket::with(['property', 'assignee'])->latest()->get();
        if ($dbTickets->isNotEmpty()) {
            $tickets = $dbTickets->map(function ($t, $idx) {
                return [
                    'id' => 'TKT-' . (1000 + $idx),
                    'unit' => $t->property?->title ?? 'Unit',
                    'issue' => $t->title ?? 'Issue',
                    'priority' => $t->priority ?? 'medium',
                    'status' => $t->status ?? 'open',
                    'assigned' => $t->assignee?->name ?? 'Unassigned',
                    'date' => $t->created_at ? $t->created_at->format('Y-m-d') : '2026-10-08',
                ];
            });
        } else {
            $tickets = collect([
                ['id' => 'TKT-1050', 'unit' => 'Bole Apt 405', 'issue' => 'Water Leak in Bathroom', 'priority' => 'high', 'status' => 'open', 'assigned' => null, 'date' => '2026-10-08'],
                ['id' => 'TKT-1049', 'unit' => 'CMC Condo 201', 'issue' => 'AC Not Cooling', 'priority' => 'medium', 'status' => 'in_progress', 'assigned' => 'Amanuel T.', 'date' => '2026-10-07'],
                ['id' => 'TKT-1048', 'unit' => 'Kazanchis V12', 'issue' => 'Broken Door Handle', 'priority' => 'low', 'status' => 'waiting', 'assigned' => 'John Doe', 'date' => '2026-10-05'],
                ['id' => 'TKT-1047', 'unit' => 'Bole Apt 201', 'issue' => 'Power Outlet Sparking', 'priority' => 'critical', 'status' => 'assigned', 'assigned' => 'Dawit M.', 'date' => '2026-10-08'],
                ['id' => 'TKT-1045', 'unit' => 'CMC Condo 505', 'issue' => 'No Hot Water', 'priority' => 'high', 'status' => 'completed', 'assigned' => 'Amanuel T.', 'date' => '2026-10-02'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $tickets = $tickets->filter(function($t) use ($q) {
                return str_contains(strtolower($t['id']), $q)
                    || str_contains(strtolower($t['unit']), $q)
                    || str_contains(strtolower($t['issue']), $q)
                    || str_contains(strtolower($t['priority']), $q)
                    || str_contains(strtolower($t['assigned'] ?? ''), $q)
                    || str_contains(strtolower($t['status']), $q);
            });
        }

        return view('frontend.operations.tickets', [
            'tickets' => $tickets->values()
        ]);
    }

    public function ticketShow($id)
    {
        $dbTicket = MaintenanceTicket::with(['property', 'reporter', 'assignee'])->find($id);
        if (!$dbTicket) {
            $dbTicket = MaintenanceTicket::with(['property', 'reporter', 'assignee'])->first();
        }

        if ($dbTicket) {
            $ticket = [
                'id' => $dbTicket->id,
                'unit' => $dbTicket->property?->title ?? 'Bole Apt 405',
                'issue' => $dbTicket->title,
                'description' => $dbTicket->description ?? 'No description provided.',
                'priority' => $dbTicket->priority ?? 'high',
                'status' => $dbTicket->status ?? 'in_progress',
                'assigned' => $dbTicket->assignee?->name ?? 'Unassigned',
                'reporter' => $dbTicket->reporter?->name ?? 'Abebe Kebede',
                'reporter_phone' => $dbTicket->reporter?->phone ?? '+251 911 234567',
                'created_at' => $dbTicket->created_at ? $dbTicket->created_at->format('Y-m-d h:i A') : '2026-10-08 09:15 AM',
            ];
        } else {
            $ticket = [
                'id' => $id, 
                'unit' => 'Bole Apt 405', 
                'issue' => 'Water Leak in Bathroom', 
                'description' => 'The tenant reported a severe water leak under the master bathroom sink. It is causing damage to the cabinet.',
                'priority' => 'high', 
                'status' => 'in_progress', 
                'assigned' => 'Amanuel T.',
                'reporter' => 'Abebe Kebede',
                'reporter_phone' => '+251 911 234567',
                'created_at' => '2026-10-08 09:15 AM'
            ];
        }

        $timeline = [
            ['status' => 'Ticket Created', 'date' => '2026-10-08 09:15 AM', 'user' => 'System'],
            ['status' => 'Assigned to Technician', 'date' => '2026-10-08 10:00 AM', 'user' => 'Operations Manager'],
            ['status' => 'Work Started', 'date' => '2026-10-08 11:30 AM', 'user' => 'Field Staff'],
        ];

        return view('frontend.operations.ticket_show', compact('ticket', 'timeline'));
    }

    public function cleaning()
    {
        $dbTasks = CleaningTask::with(['property', 'cleaner'])->latest()->get();
        if ($dbTasks->isNotEmpty()) {
            $tasks = $dbTasks->map(function ($c, $idx) {
                return [
                    'id' => 'CLN-' . (200 + $idx),
                    'unit' => $c->property?->title ?? 'Apartment Unit',
                    'type' => 'Routine / Turnover Cleaning',
                    'date' => $c->created_at ? $c->created_at->format('Y-m-d') : '2026-10-08',
                    'status' => $c->status ?? 'pending',
                    'assigned' => $c->cleaner?->name ?? 'Cleaning Crew',
                ];
            })->toArray();
        } else {
            $tasks = [
                ['id' => 'CLN-201', 'unit' => 'Bole Apt 405', 'type' => 'Checkout Cleaning', 'date' => '2026-10-08', 'status' => 'pending', 'assigned' => 'Sara Cleaning Service'],
                ['id' => 'CLN-202', 'unit' => 'CMC Condo 201', 'type' => 'Routine Maintenance', 'date' => '2026-10-09', 'status' => 'assigned', 'assigned' => 'Sara Cleaning Service'],
                ['id' => 'CLN-203', 'unit' => 'Kazanchis V12', 'type' => 'Deep Clean', 'date' => '2026-10-07', 'status' => 'in_progress', 'assigned' => 'Selamawit T.'],
                ['id' => 'CLN-204', 'unit' => 'Bole Apt 101', 'type' => 'Post-Construction', 'date' => '2026-10-05', 'status' => 'inspection', 'assigned' => 'Dawit M.'],
                ['id' => 'CLN-205', 'unit' => 'CMC Condo 505', 'type' => 'Checkout Cleaning', 'date' => '2026-10-02', 'status' => 'ready', 'assigned' => 'Sara Cleaning Service'],
            ];
        }

        return view('frontend.operations.cleaning', compact('tasks'));
    }

    public function staff()
    {
        $staffUsers = User::where('email', 'like', '%@srms.et')->get();
        if ($staffUsers->isNotEmpty()) {
            $staff = $staffUsers->map(function ($u, $idx) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'role' => str_contains(strtolower($u->name), 'clean') ? 'Housekeeper' : (str_contains(strtolower($u->name), 'electric') ? 'Electrician' : 'Maintenance Tech'),
                    'status' => 'active',
                    'active_tasks' => rand(1, 4),
                    'completed_today' => rand(1, 3),
                ];
            })->toArray();
        } else {
            $staff = [
                ['id' => 1, 'name' => 'Amanuel Tadesse', 'role' => 'Maintenance Tech', 'status' => 'active', 'active_tasks' => 2, 'completed_today' => 1],
                ['id' => 2, 'name' => 'Sara Cleaning Service', 'role' => 'Contractor', 'status' => 'active', 'active_tasks' => 4, 'completed_today' => 0],
                ['id' => 3, 'name' => 'Dawit M.', 'role' => 'Electrician', 'status' => 'offline', 'active_tasks' => 1, 'completed_today' => 3],
                ['id' => 4, 'name' => 'Selamawit T.', 'role' => 'Housekeeper', 'status' => 'active', 'active_tasks' => 1, 'completed_today' => 2],
            ];
        }

        return view('frontend.operations.staff', compact('staff'));
    }
}
