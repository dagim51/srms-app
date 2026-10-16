<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Viewing;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViewingWebController extends Controller
{
    protected function getActiveUser()
    {
        return Auth::user();
    }

    public function index()
    {
        $user = $this->getActiveUser();
        
        $viewings = Viewing::with(['listing.property', 'agent', 'requester'])
            ->where(function($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhere('agent_id', $user->id);
            })
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return view('dashboard.viewings.index', compact('viewings'));
    }

    public function create(Request $request)
    {
        $listingId = $request->query('listing_id');
        $listing = $listingId ? Listing::with('property')->findOrFail($listingId) : null;

        return view('dashboard.viewings.create', compact('listing'));
    }

    public function store(Request $request)
    {
        $user = $this->getActiveUser();

        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required',
            'notes' => 'nullable|string',
        ]);

        $listing = Listing::findOrFail($request->listing_id);

        // check schedule conflict for the user
        $scheduledAt = \Carbon\Carbon::parse($request->scheduled_date . ' ' . $request->scheduled_time);

        $conflict = Viewing::where('agent_id', $listing->owner_actor_id)
            ->where('scheduled_at', $scheduledAt)
            ->whereIn('status', ['requested', 'confirmed', 'rescheduled'])
            ->exists();

        if ($conflict) {
            return back()->withInput()->withErrors(['scheduled_time' => 'This time slot is already booked. Please choose another time.']);
        }

        Viewing::create([
            'listing_id' => $listing->id,
            'requester_id' => $user->id,
            'agent_id' => $listing->owner_actor_id,
            'status' => 'requested',
            'scheduled_at' => $scheduledAt,
            'notes' => $request->notes,
        ]);

        return redirect()->route('dashboard.viewings.index')->with('success', 'Viewing requested successfully!');
    }

    public function show(Viewing $viewing)
    {
        $user = $this->getActiveUser();

        if ($viewing->requester_id !== $user->id && $viewing->agent_id !== $user->id) {
            abort(403);
        }

        $viewing->load(['listing.property', 'agent', 'requester']);

        return view('dashboard.viewings.show', compact('viewing'));
    }

    public function update(Request $request, Viewing $viewing)
    {
        $user = $this->getActiveUser();

        if ($viewing->requester_id !== $user->id && $viewing->agent_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'sometimes|in:confirmed,completed,cancelled,rescheduled',
            'scheduled_date' => 'sometimes|required_if:status,rescheduled|date|after_or_equal:today',
            'scheduled_time' => 'sometimes|required_if:status,rescheduled',
        ]);

        if ($request->has('status')) {
            $viewing->status = $request->status;
        }

        if ($request->status === 'rescheduled') {
            $viewing->scheduled_at = \Carbon\Carbon::parse($request->scheduled_date . ' ' . $request->scheduled_time);
        }

        $viewing->save();

        return back()->with('success', 'Viewing updated successfully!');
    }
}
