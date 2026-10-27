<?php

namespace App\Domains\CRM\Http\Controllers\Web;

use App\Domains\CRM\Models\Commission;
use App\Domains\CRM\Models\CrmActivity;
use App\Domains\CRM\Models\Lead;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmWebController extends Controller
{
    public function index()
    {
        $totalLeads = Lead::count();
        $activePipeline = Lead::whereNotIn('status', ['won', 'lost'])->count();
        $closedThisMonth = Lead::where('status', 'won')
            ->where('updated_at', '>=', now()->startOfMonth())
            ->count();
        $tasksDue = CrmActivity::whereNull('completed_at')->count();

        $stats = [
            'total_leads' => $totalLeads,
            'active_pipeline' => $activePipeline,
            'closed_this_month' => $closedThisMonth,
            'tasks_due' => $tasksDue,
        ];

        $leads = Lead::with(['assignee', 'listing.property'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'name' => $l->name,
                    'status' => ucwords(str_replace('_', ' ', $l->status ?? 'New')),
                    'property' => $l->listing?->property?->title ?? 'General Inquiry',
                    'value' => '—',
                    'last_contact' => $l->updated_at ? $l->updated_at->format('Y-m-d') : now()->format('Y-m-d'),
                    'assigned_to' => $l->assignee?->name ?? 'Unassigned',
                ];
            })->toArray();

        return view('frontend.crm.index', compact('stats', 'leads'));
    }

    public function leads(Request $request)
    {
        $query = Lead::with(['assignee', 'listing.property'])->latest();

        if ($request->filled('status')) {
            $query->where('status', strtolower($request->status));
        }

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhereHas('assignee', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $leadsCollection = $query->paginate(20)->withQueryString();
        $emails = $leadsCollection->pluck('email')->filter()->unique();
        $clientsByEmail = User::whereIn('email', $emails)->pluck('id', 'email');

        $leads = $leadsCollection->through(function ($l) use ($clientsByEmail) {
            return [
                'id' => $l->id,
                'client_id' => $clientsByEmail[$l->email] ?? null,
                'name' => $l->name,
                'status' => ucwords(str_replace('_', ' ', $l->status ?? 'New')),
                'raw_status' => strtolower($l->status ?? 'new'),
                'property' => $l->listing?->property?->title ?? 'General Inquiry',
                'value' => '—',
                'last_contact' => $l->updated_at ? $l->updated_at->format('Y-m-d') : now()->format('Y-m-d'),
                'assigned_to' => $l->assignee?->name ?? 'Unassigned',
            ];
        });

        $listings = Listing::with('property')->take(30)->get();
        $agents = User::orderBy('name')->take(50)->get();

        return view('frontend.crm.leads', [
            'leads' => $leads,
            'listings' => $listings,
            'agents' => $agents,
        ]);
    }

    public function storeLead(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string|in:web,walk_in,referral,inquiry',
            'status' => 'nullable|string|in:new,contacted,qualified,viewing,negotiation,offer,won,lost',
            'listing_id' => 'nullable|uuid|exists:listings,id',
            'assigned_to' => 'nullable|uuid|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $orgId = OrganizationMember::where('user_id', Auth::id())->value('organization_id')
            ?? Organization::first()?->id;

        if (!$orgId) {
            $defaultOrg = Organization::create([
                'name' => 'Primary Real Estate Agency',
                'slug' => 'primary-real-estate-agency',
            ]);
            $orgId = $defaultOrg->id;
        }

        $lead = Lead::create([
            'organization_id' => $orgId,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'source' => $validated['source'] ?? 'inquiry',
            'status' => strtolower($validated['status'] ?? 'new'),
            'listing_id' => $validated['listing_id'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? Auth::id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        CrmActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id() ?? $lead->assigned_to ?? User::first()?->id,
            'type' => 'note',
            'description' => 'Lead captured into CRM pipeline via web dashboard.',
            'completed_at' => now(),
        ]);

        return redirect()->route('dashboard.crm.leads')->with('success', __('Lead created successfully.'));
    }

    public function leadShow($id)
    {
        $lead = Lead::with(['assignee', 'listing.property', 'activities.user'])->findOrFail($id);

        $client = null;
        if (!empty($lead->email)) {
            $client = User::where('email', $lead->email)->first();
        } elseif (!empty($lead->phone)) {
            $client = User::where('phone', $lead->phone)->first();
        }

        $formattedLead = [
            'id' => $lead->id,
            'client_id' => $client?->id,
            'name' => $lead->name,
            'status' => ucwords(str_replace('_', ' ', $lead->status ?? 'New')),
            'raw_status' => strtolower($lead->status ?? 'new'),
            'property' => $lead->listing?->property?->title ?? 'General Inquiry',
            'value' => '—',
            'email' => $lead->email ?? '—',
            'phone' => $lead->phone ?? '—',
            'source' => ucwords(str_replace('_', ' ', $lead->source ?? 'Inquiry')),
            'last_contact' => $lead->updated_at ? $lead->updated_at->format('Y-m-d') : now()->format('Y-m-d'),
            'created' => $lead->created_at ? $lead->created_at->format('M d, Y') : now()->format('M d, Y'),
            'assigned_to' => $lead->assignee?->name ?? 'Unassigned',
            'notes' => $lead->notes ?? 'No notes recorded.',
        ];

        $timeline = $lead->activities ? $lead->activities->sortByDesc('created_at')->map(function ($act) {
            return [
                'type' => $act->type ?? 'note',
                'title' => $act->description ?? ($act->type ? ucfirst($act->type).' Event' : 'CRM Activity'),
                'date' => $act->created_at ? $act->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'),
                'user' => $act->user?->name ?? 'Agent Staff',
            ];
        })->values()->toArray() : [];

        return view('frontend.crm.lead_show', ['lead' => $formattedLead, 'timeline' => $timeline]);
    }

    public function updateStage(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:new,contacted,qualified,viewing,negotiation,offer,won,lost,New,Contacted,Qualified,Viewing,Negotiation,Offer,Won,Lost',
        ]);

        $newStatus = strtolower($validated['status']);
        $oldStatus = $lead->status;
        $lead->status = $newStatus;
        $lead->save();

        CrmActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id() ?? User::first()?->id,
            'type' => 'status_change',
            'description' => 'Stage progressed from ' . ucfirst($oldStatus) . ' to ' . ucfirst($newStatus) . '.',
            'completed_at' => now(),
        ]);

        return back()->with('success', __('Lead pipeline stage updated to :stage.', ['stage' => ucfirst($newStatus)]));
    }

    public function logActivity(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|string|in:call,email,meeting,note,viewing',
            'description' => 'required|string|max:1000',
            'scheduled_at' => 'nullable|date',
        ]);

        CrmActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id() ?? User::first()?->id,
            'type' => $validated['type'],
            'description' => $validated['description'],
            'scheduled_at' => !empty($validated['scheduled_at']) ? $validated['scheduled_at'] : null,
            'completed_at' => empty($validated['scheduled_at']) ? now() : null,
        ]);

        return back()->with('success', __('Activity logged successfully.'));
    }

    public function convertToClient(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $email = $lead->email ?: 'client_' . substr($lead->id, 0, 8) . '@srms.et';

        $client = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $lead->name,
                'phone' => $lead->phone,
                'password' => bcrypt(str()->random(16)),
            ]
        );

        $lead->status = 'won';
        $lead->save();

        CrmActivity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id() ?? User::first()?->id,
            'type' => 'conversion',
            'description' => 'Converted prospect into registered client (' . $client->name . ').',
            'completed_at' => now(),
        ]);

        return back()->with('success', __('Lead successfully converted to registered client account.'));
    }

    public function pipeline()
    {
        $allLeads = Lead::with(['assignee', 'listing.property'])->latest()->get();

        $pipeline = [
            'New' => [],
            'Contacted' => [],
            'Qualified' => [],
            'Viewing' => [],
            'Negotiation' => [],
            'Offer' => [],
            'Won' => [],
            'Lost' => [],
        ];

        foreach ($allLeads as $l) {
            $formatted = [
                'id' => $l->id,
                'name' => $l->name,
                'status' => $l->status,
                'property' => $l->listing?->property?->title ?? 'Inquiry',
                'value' => '—',
                'last_contact' => $l->updated_at ? $l->updated_at->format('Y-m-d') : now()->format('Y-m-d'),
                'assigned_to' => $l->assignee?->name ?? 'Unassigned',
            ];

            $stage = match (strtolower($l->status ?? '')) {
                'contacted' => 'Contacted',
                'qualified' => 'Qualified',
                'viewing' => 'Viewing',
                'negotiation' => 'Negotiation',
                'offer' => 'Offer',
                'won' => 'Won',
                'lost' => 'Lost',
                default => 'New',
            };

            $pipeline[$stage][] = $formatted;
        }

        $listings = Listing::with('property')->take(30)->get();
        $agents = User::orderBy('name')->take(50)->get();

        return view('frontend.crm.pipeline', compact('pipeline', 'listings', 'agents'));
    }

    public function clients(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $clients = $query->latest()->paginate(20)->withQueryString()->through(function ($u) {
            $leadsCount = Lead::where('email', $u->email)
                ->when(!empty($u->phone), fn($q) => $q->orWhere('phone', $u->phone))
                ->count();

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone ?? '—',
                'type' => 'Client',
                'properties' => 0,
                'leads_count' => $leadsCount,
            ];
        });

        return view('frontend.crm.clients', [
            'clients' => $clients,
        ]);
    }

    public function clientShow($id)
    {
        $client = User::findOrFail($id);

        $leads = Lead::where('email', $client->email)
            ->when(!empty($client->phone), function ($q) use ($client) {
                $q->orWhere('phone', $client->phone);
            })
            ->with(['assignee', 'listing.property'])
            ->latest()
            ->get();

        $leadIds = $leads->pluck('id')->filter();

        $activities = CrmActivity::whereIn('lead_id', $leadIds)
            ->orWhere('user_id', $client->id)
            ->with(['lead', 'user'])
            ->latest()
            ->take(15)
            ->get();

        $stats = [
            'total_leads' => $leads->count(),
            'active_inquiries' => $leads->whereNotIn('status', ['won', 'lost'])->count(),
            'closed_deals' => $leads->where('status', 'won')->count(),
        ];

        return view('frontend.crm.client_show', compact('client', 'leads', 'activities', 'stats'));
    }

    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $client = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => bcrypt(str()->random(16)),
        ]);

        return redirect()->route('dashboard.crm.clients.show', $client->id)
            ->with('success', __('Client profile created successfully.'));
    }

    public function tasks(Request $request)
    {
        $query = CrmActivity::with(['lead', 'user'])->latest();

        if ($request->filled('status')) {
            if ($request->status === 'completed') {
                $query->whereNotNull('completed_at');
            } elseif ($request->status === 'pending') {
                $query->whereNull('completed_at');
            }
        }

        $activities = $query->paginate(20)->withQueryString();
        $tasks = $activities->through(function ($act) {
            return [
                'id' => $act->id,
                'title' => $act->description ?? ($act->type ? ucfirst($act->type).' Task' : 'Activity Task'),
                'due_date' => $act->scheduled_at ? $act->scheduled_at->format('Y-m-d') : ($act->created_at ? $act->created_at->format('Y-m-d') : now()->format('Y-m-d')),
                'status' => $act->completed_at ? 'completed' : 'pending',
                'priority' => 'medium',
                'lead_id' => $act->lead_id,
                'lead_name' => $act->lead?->name ?? 'General Inquiry',
                'type' => $act->type ?? 'note',
            ];
        });

        $leads = Lead::orderBy('name')->take(50)->get();

        return view('frontend.crm.tasks', compact('tasks', 'leads'));
    }

    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|uuid|exists:leads,id',
            'type' => 'required|string|in:call,email,meeting,note,viewing,task',
            'description' => 'required|string|max:1000',
            'scheduled_at' => 'nullable|date',
        ]);

        CrmActivity::create([
            'lead_id' => $validated['lead_id'],
            'user_id' => Auth::id() ?? User::first()?->id,
            'type' => $validated['type'],
            'description' => $validated['description'],
            'scheduled_at' => !empty($validated['scheduled_at']) ? $validated['scheduled_at'] : now()->addDay(),
            'completed_at' => null,
        ]);

        return redirect()->route('dashboard.crm.tasks')->with('success', __('Task created successfully.'));
    }

    public function completeTask(Request $request, $id)
    {
        $task = CrmActivity::findOrFail($id);
        $task->update(['completed_at' => now()]);

        return back()->with('success', __('Task marked as completed.'));
    }

    public function commissions(Request $request)
    {
        $query = Commission::with('agent')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('deal_id', 'like', "%{$q}%")
                    ->orWhere('deal_type', 'like', "%{$q}%")
                    ->orWhereHas('agent', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $commissionsList = $query->paginate(20)->withQueryString();
        $commissions = $commissionsList->through(function ($c) {
            return [
                'raw_id' => $c->id,
                'id' => 'COM-'.strtoupper(substr($c->id, 0, 8)),
                'property' => $c->deal_type ? ucwords($c->deal_type).' Deal' : 'Real Estate Deal',
                'client' => $c->agent?->name ?? 'Agent',
                'amount' => number_format($c->commission_amount).' '.$c->currency,
                'status' => $c->status,
                'date' => $c->created_at ? $c->created_at->format('Y-m-d') : now()->format('Y-m-d'),
            ];
        });

        return view('frontend.crm.commissions', compact('commissions'));
    }

    public function approveCommission(Request $request, $id)
    {
        $cleanId = str_replace('COM-', '', $id);
        $commission = Commission::where('id', $id)
            ->orWhere('id', 'like', $cleanId . '%')
            ->firstOrFail();

        $commission->update(['status' => 'approved']);

        return back()->with('success', __('Commission approved for payout settlement.'));
    }
}
