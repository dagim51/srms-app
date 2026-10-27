<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CleaningTask;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class OperationsWebController extends Controller
{
    public function index()
    {
        $openTickets     = MaintenanceTicket::whereIn('status', ['open', 'assigned', 'in_progress'])->count();
        $overdueTasks    = CleaningTask::where('status', 'pending')->count();
        $cleaningPending = CleaningTask::whereIn('status', ['pending', 'assigned'])->count();
        $staffCount      = User::count();

        $stats = [
            'open_tickets'     => $openTickets,
            'overdue_tasks'    => $overdueTasks,
            'cleaning_pending' => $cleaningPending,
            'staff_active'     => $staffCount,
        ];

        $recentTickets = MaintenanceTicket::with(['property', 'assignee'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($t) {
                return [
                    'id'       => $t->id,
                    'label'    => 'TKT-' . strtoupper(substr($t->id, 0, 8)),
                    'unit'     => $t->property?->title ?? 'Apartment Unit',
                    'issue'    => $t->title ?? 'Maintenance Issue',
                    'priority' => $t->priority ?? 'medium',
                    'status'   => $t->status ?? 'open',
                    'assigned' => $t->assignee?->name ?? 'Unassigned',
                ];
            })->toArray();

        $properties = Property::orderBy('title')->get(['id', 'title']);

        return view('frontend.operations.index', compact('stats', 'recentTickets', 'properties'));
    }

    public function tickets(Request $request)
    {
        $query = MaintenanceTicket::with(['property', 'assignee'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('property', fn ($p) => $p->where('title', 'like', "%{$q}%"))
                    ->orWhereHas('assignee',  fn ($u) => $u->where('name',  'like', "%{$q}%"));
            });
        }

        $tickets = $query->paginate(20)->withQueryString()->through(function ($t) {
            return [
                'id'       => $t->id,
                'label'    => 'TKT-' . strtoupper(substr($t->id, 0, 8)),
                'unit'     => $t->property?->title ?? 'Unit',
                'issue'    => $t->title ?? 'Issue',
                'priority' => $t->priority ?? 'medium',
                'status'   => $t->status ?? 'open',
                'assigned' => $t->assignee?->name ?? 'Unassigned',
                'date'     => $t->created_at ? $t->created_at->format('Y-m-d') : now()->format('Y-m-d'),
            ];
        });

        $properties = Property::orderBy('title')->get(['id', 'title']);

        return view('frontend.operations.tickets', compact('tickets', 'properties'));
    }

    public function ticketShow($id)
    {
        $dbTicket = MaintenanceTicket::with(['property', 'reporter', 'assignee'])->findOrFail($id);

        $ticket = [
            'id'             => $dbTicket->id,
            'label'          => 'TKT-' . strtoupper(substr($dbTicket->id, 0, 8)),
            'unit'           => $dbTicket->property?->title ?? 'Apartment Unit',
            'issue'          => $dbTicket->title,
            'description'    => $dbTicket->description ?? 'No description provided.',
            'priority'       => $dbTicket->priority ?? 'medium',
            'status'         => $dbTicket->status ?? 'open',
            'assigned'       => $dbTicket->assignee?->name ?? 'Unassigned',
            'assigned_to'    => $dbTicket->assigned_to,
            'reporter'       => $dbTicket->reporter?->name ?? 'Property Occupant',
            'reporter_phone' => $dbTicket->reporter?->phone ?? 'N/A',
            'created_at'     => $dbTicket->created_at
                ? $dbTicket->created_at->format('Y-m-d h:i A')
                : now()->format('Y-m-d h:i A'),
        ];

        $timeline = [
            ['status' => 'Ticket Created', 'date' => $ticket['created_at'], 'user' => $ticket['reporter']],
        ];

        $staffUsers = User::orderBy('name')->get(['id', 'name']);

        return view('frontend.operations.ticket_show', compact('ticket', 'timeline', 'staffUsers'));
    }

    public function cleaning()
    {
        $tasks = CleaningTask::with(['property', 'cleaner'])->latest()->paginate(20)->through(function ($c) {
            return [
                'id'       => $c->id,
                'label'    => 'CLN-' . strtoupper(substr($c->id, 0, 8)),
                'unit'     => $c->property?->title ?? 'Apartment Unit',
                'type'     => 'Turnover Cleaning',
                'date'     => $c->created_at ? $c->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'status'   => $c->status ?? 'pending',
                'assigned' => $c->cleaner?->name ?? 'Cleaning Crew',
            ];
        });

        $cleaningStats = [
            'pending_checkouts'   => CleaningTask::where('status', 'pending')->count(),
            'active_cleaning'     => CleaningTask::where('status', 'in_progress')->count(),
            'awaiting_inspection' => CleaningTask::where('status', 'inspection')->count(),
            'units_ready'         => CleaningTask::where('status', 'ready')->count(),
        ];

        $properties = Property::orderBy('title')->get(['id', 'title']);
        $staffUsers = User::orderBy('name')->get(['id', 'name']);

        return view('frontend.operations.cleaning', compact('tasks', 'cleaningStats', 'properties', 'staffUsers'));
    }

    public function staff()
    {
        $staffUsers = User::latest()->paginate(20);
        $staff = $staffUsers->through(function ($u) {
            $activeTasks = MaintenanceTicket::where('assigned_to', $u->id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->count();
            $completedToday = MaintenanceTicket::where('assigned_to', $u->id)
                ->where('status', 'completed')
                ->whereDate('updated_at', today())
                ->count();
            return [
                'id'              => $u->id,
                'name'            => $u->name,
                'role'            => 'Operations Staff',
                'status'          => $u->status ?? 'active',
                'active_tasks'    => $activeTasks,
                'completed_today' => $completedToday,
            ];
        });

        return view('frontend.operations.staff', compact('staff'));
    }

    // POST handlers

    public function storeTicket(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'priority'    => 'required|in:low,medium,high,critical',
            'property_id' => 'nullable|exists:properties,id',
        ]);

        $data['reported_by'] = auth()->id();
        $data['status']      = 'open';

        MaintenanceTicket::create($data);

        return redirect()->route('dashboard.operations.tickets')
            ->with('success', 'Maintenance ticket created.');
    }

    public function updateTicketStatus(Request $request, $id)
    {
        $ticket = MaintenanceTicket::findOrFail($id);

        $data = $request->validate([
            'status'      => 'required|in:open,assigned,in_progress,waiting,completed,closed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket->update($data);

        return back()->with('success', 'Ticket status updated.');
    }

    public function postNote(Request $request, $id)
    {
        $ticket = MaintenanceTicket::findOrFail($id);

        $request->validate([
            'note'           => 'required|string|max:2000',
            'visible_tenant' => 'nullable|boolean',
        ]);

        $existing = $ticket->description ?? '';
        $stamp    = now()->format('Y-m-d H:i') . ' by ' . auth()->user()->name;
        $ticket->update([
            'description' => $existing . "\n\n[{$stamp}]\n" . $request->note,
        ]);

        return back()->with('success', 'Note posted.');
    }

    public function storeCleaning(Request $request)
    {
        $data = $request->validate([
            'property_id'         => 'required|exists:properties,id',
            'assigned_cleaner_id' => 'nullable|exists:users,id',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $data['status'] = 'pending';

        CleaningTask::create($data);

        return redirect()->route('dashboard.operations.cleaning')
            ->with('success', 'Cleaning task scheduled.');
    }
}
