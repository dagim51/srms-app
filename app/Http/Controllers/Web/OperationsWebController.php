<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OperationsWebController extends Controller
{
    public function index()
    {
        $stats = [
            'open_tickets' => 12,
            'overdue_tasks' => 3,
            'cleaning_pending' => 8,
            'staff_active' => 15
        ];

        $recentTickets = [
            ['id' => 'TKT-1050', 'unit' => 'Bole 405', 'issue' => 'Water Leak in Bathroom', 'priority' => 'high', 'status' => 'open', 'assigned' => null],
            ['id' => 'TKT-1049', 'unit' => 'CMC 201', 'issue' => 'AC Not Cooling', 'priority' => 'medium', 'status' => 'in_progress', 'assigned' => 'Amanuel T.'],
            ['id' => 'TKT-1048', 'unit' => 'Kazanchis V12', 'issue' => 'Broken Door Handle', 'priority' => 'low', 'status' => 'waiting', 'assigned' => 'John Doe'],
        ];

        return view('frontend.operations.index', compact('stats', 'recentTickets'));
    }

    public function tickets()
    {
        $tickets = [
            ['id' => 'TKT-1050', 'unit' => 'Bole Apt 405', 'issue' => 'Water Leak in Bathroom', 'priority' => 'high', 'status' => 'open', 'assigned' => null, 'date' => '2026-10-08'],
            ['id' => 'TKT-1049', 'unit' => 'CMC Condo 201', 'issue' => 'AC Not Cooling', 'priority' => 'medium', 'status' => 'in_progress', 'assigned' => 'Amanuel T.', 'date' => '2026-10-07'],
            ['id' => 'TKT-1048', 'unit' => 'Kazanchis V12', 'issue' => 'Broken Door Handle', 'priority' => 'low', 'status' => 'waiting', 'assigned' => 'John Doe', 'date' => '2026-10-05'],
            ['id' => 'TKT-1047', 'unit' => 'Bole Apt 201', 'issue' => 'Power Outlet Sparking', 'priority' => 'critical', 'status' => 'assigned', 'assigned' => 'Dawit M.', 'date' => '2026-10-08'],
            ['id' => 'TKT-1045', 'unit' => 'CMC Condo 505', 'issue' => 'No Hot Water', 'priority' => 'high', 'status' => 'completed', 'assigned' => 'Amanuel T.', 'date' => '2026-10-02'],
        ];

        return view('frontend.operations.tickets', compact('tickets'));
    }

    public function ticketShow($id)
    {
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

        $timeline = [
            ['status' => 'Ticket Created', 'date' => '2026-10-08 09:15 AM', 'user' => 'System'],
            ['status' => 'Assigned to Amanuel T.', 'date' => '2026-10-08 10:00 AM', 'user' => 'Manager'],
            ['status' => 'Work Started', 'date' => '2026-10-08 11:30 AM', 'user' => 'Amanuel T.'],
        ];

        return view('frontend.operations.ticket_show', compact('ticket', 'timeline'));
    }

    public function cleaning()
    {
        $tasks = [
            ['id' => 'CLN-201', 'unit' => 'Bole Apt 405', 'type' => 'Checkout Cleaning', 'date' => '2026-10-08', 'status' => 'pending', 'assigned' => 'Sara Cleaning Service'],
            ['id' => 'CLN-202', 'unit' => 'CMC Condo 201', 'type' => 'Routine Maintenance', 'date' => '2026-10-09', 'status' => 'assigned', 'assigned' => 'Sara Cleaning Service'],
            ['id' => 'CLN-203', 'unit' => 'Kazanchis V12', 'type' => 'Deep Clean', 'date' => '2026-10-07', 'status' => 'in_progress', 'assigned' => 'Selamawit T.'],
            ['id' => 'CLN-204', 'unit' => 'Bole Apt 101', 'type' => 'Post-Construction', 'date' => '2026-10-05', 'status' => 'inspection', 'assigned' => 'Dawit M.'],
            ['id' => 'CLN-205', 'unit' => 'CMC Condo 505', 'type' => 'Checkout Cleaning', 'date' => '2026-10-02', 'status' => 'ready', 'assigned' => 'Sara Cleaning Service'],
        ];

        return view('frontend.operations.cleaning', compact('tasks'));
    }

    public function staff()
    {
        $staff = [
            ['id' => 1, 'name' => 'Amanuel Tadesse', 'role' => 'Maintenance Tech', 'status' => 'active', 'active_tasks' => 2, 'completed_today' => 1],
            ['id' => 2, 'name' => 'Sara Cleaning Service', 'role' => 'Contractor', 'status' => 'active', 'active_tasks' => 4, 'completed_today' => 0],
            ['id' => 3, 'name' => 'Dawit M.', 'role' => 'Electrician', 'status' => 'offline', 'active_tasks' => 1, 'completed_today' => 3],
            ['id' => 4, 'name' => 'Selamawit T.', 'role' => 'Housekeeper', 'status' => 'active', 'active_tasks' => 1, 'completed_today' => 2],
        ];

        return view('frontend.operations.staff', compact('staff'));
    }
}
