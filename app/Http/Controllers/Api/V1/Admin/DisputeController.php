<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DisputeEvidenceResource;
use App\Http\Resources\DisputeResource;
use App\Http\Resources\ListingFlagResource;
use App\Http\Resources\UserFlagResource;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Models\ListingFlag;
use App\Models\UserFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DisputeController extends Controller
{
    /**
     * List all disputes with filters.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Dispute::class);

        $query = Dispute::with(['complainant', 'respondent', 'assignedModerator', 'disputable', 'evidence']);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('assigned_moderator_id')) {
            $query->where('assigned_moderator_id', $request->assigned_moderator_id);
        }
        if ($request->has('complainant_id')) {
            $query->where('complainant_id', $request->complainant_id);
        }
        if ($request->has('date_from')) {
            $query->where('opened_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('opened_at', '<=', $request->date_to);
        }
        if ($request->has('sla_breached')) {
            $query->where('sla_breached', $request->boolean('sla_breached'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'opened_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $disputes = $query->paginate($request->get('per_page', 15));

        return DisputeResource::collection($disputes);
    }

    /**
     * Show a specific dispute with all evidence.
     */
    public function show(Dispute $dispute)
    {
        Gate::authorize('view', $dispute);

        $dispute->load([
            'complainant',
            'respondent',
            'assignedModerator',
            'disputable',
            'evidence.submittedBy',
            'evidence.verifiedBy',
        ]);

        return response()->json(['data' => new DisputeResource($dispute)]);
    }

    /**
     * Assign dispute to a moderator.
     */
    public function assign(Request $request, Dispute $dispute)
    {
        Gate::authorize('update', $dispute);

        $validated = $request->validate([
            'moderator_id' => 'required|exists:users,id',
        ]);

        $dispute->update([
            'assigned_moderator_id' => $validated['moderator_id'],
            'assigned_at' => now(),
            'status' => 'under_review',
        ]);

        return response()->json(['data' => new DisputeResource($dispute->load('assignedModerator'))]);
    }

    /**
     * Update dispute status.
     */
    public function updateStatus(Request $request, Dispute $dispute)
    {
        Gate::authorize('update', $dispute);

        $validated = $request->validate([
            'status' => 'required|in:open,under_review,awaiting_evidence,awaiting_response,mediated,resolved,closed,escalated',
            'resolution' => 'nullable|in:refund,partial_refund,replacement,compensation,dismissed,mutual_agreement',
            'resolution_notes' => 'nullable|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'sla_hours' => 'nullable|integer|min:1',
        ]);

        $oldStatus = $dispute->status;

        if (! $dispute->canTransitionTo($validated['status'])) {
            return response()->json([
                'message' => "Cannot transition from {$oldStatus} to {$validated['status']}",
            ], 422);
        }

        $updateData = ['status' => $validated['status']];

        if (isset($validated['resolution'])) {
            $updateData['resolution'] = $validated['resolution'];
        }
        if (isset($validated['resolution_notes'])) {
            $updateData['resolution_notes'] = $validated['resolution_notes'];
        }
        if (isset($validated['priority'])) {
            $updateData['priority'] = $validated['priority'];
        }
        if (isset($validated['sla_hours'])) {
            $updateData['sla_hours'] = $validated['sla_hours'];
        }

        // Set timestamps based on status transitions
        if ($oldStatus !== 'under_review' && $validated['status'] === 'under_review') {
            $updateData['assigned_at'] = now();
        }
        if ($oldStatus !== 'resolved' && $validated['status'] === 'resolved') {
            $updateData['resolved_at'] = now();
            $updateData['closed_at'] = now();
        }
        if ($oldStatus !== 'closed' && $validated['status'] === 'closed') {
            $updateData['closed_at'] = now();
        }

        $dispute->update($updateData);

        return response()->json(['data' => new DisputeResource($dispute)]);
    }

    /**
     * Add evidence to a dispute.
     */
    public function addEvidence(Request $request, Dispute $dispute)
    {
        Gate::authorize('update', $dispute);

        $validated = $request->validate([
            'type' => 'required|in:document,screenshot,communication,transaction_record,contract,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path' => 'nullable|string',
            'file_url' => 'nullable|url',
            'mime_type' => 'nullable|string',
            'file_size' => 'nullable|integer',
            'metadata' => 'nullable|array',
        ]);

        $evidence = DisputeEvidence::create([
            'dispute_id' => $dispute->id,
            'submitted_by' => auth()->id(),
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'file_path' => $validated['file_path'] ?? null,
            'file_url' => $validated['file_url'] ?? null,
            'mime_type' => $validated['mime_type'] ?? null,
            'file_size' => $validated['file_size'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return response()->json(['data' => new DisputeEvidenceResource($evidence)], 201);
    }

    /**
     * Verify evidence.
     */
    public function verifyEvidence(DisputeEvidence $evidence)
    {
        Gate::authorize('update', $evidence->dispute);

        $evidence->update([
            'is_verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return response()->json(['data' => new DisputeEvidenceResource($evidence)]);
    }

    /**
     * List all listing flags.
     */
    public function listingFlags(Request $request)
    {
        Gate::authorize('viewAny', ListingFlag::class);

        $query = ListingFlag::with(['listing', 'reporter', 'assignedModerator']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('reason')) {
            $query->where('reason', $request->reason);
        }
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->has('listing_id')) {
            $query->where('listing_id', $request->listing_id);
        }

        $flags = $query->orderBy('reported_at', 'desc')->paginate($request->get('per_page', 15));

        return ListingFlagResource::collection($flags);
    }

    /**
     * Update listing flag status.
     */
    public function updateListingFlag(Request $request, ListingFlag $flag)
    {
        Gate::authorize('update', $flag);

        $validated = $request->validate([
            'status' => 'required|in:pending,under_review,dismissed,validated,escalated_to_dispute',
            'moderator_notes' => 'nullable|string',
            'assigned_moderator_id' => 'nullable|exists:users,id',
        ]);

        $flag->update($validated);

        if ($validated['status'] === 'under_review' && ! $flag->reviewed_at) {
            $flag->update(['reviewed_at' => now()]);
        }
        if (in_array($validated['status'], ['dismissed', 'validated', 'escalated_to_dispute'])) {
            $flag->update(['resolved_at' => now()]);
        }

        // If escalated to dispute, create a dispute
        if ($validated['status'] === 'escalated_to_dispute') {
            $dispute = Dispute::create([
                'complainant_id' => $flag->reporter_id,
                'respondent_id' => $flag->listing->owner_user_id,
                'disputable_type' => 'App\Models\Listing',
                'disputable_id' => $flag->listing_id,
                'type' => 'misrepresentation',
                'subject' => "Escalated from listing flag: {$flag->reason}",
                'description' => $flag->description ?? 'Listing flag escalated to dispute.',
                'status' => 'open',
                'priority' => 'high',
                'opened_at' => now(),
            ]);

            // Link evidence from flag (if any) - could be extended
        }

        return response()->json(['data' => new ListingFlagResource($flag)]);
    }

    /**
     * List all user flags.
     */
    public function userFlags(Request $request)
    {
        Gate::authorize('viewAny', UserFlag::class);

        $query = UserFlag::with(['user', 'reporter', 'assignedModerator', 'context']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('reason')) {
            $query->where('reason', $request->reason);
        }
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $flags = $query->orderBy('reported_at', 'desc')->paginate($request->get('per_page', 15));

        return UserFlagResource::collection($flags);
    }

    /**
     * Update user flag status.
     */
    public function updateUserFlag(Request $request, UserFlag $flag)
    {
        Gate::authorize('update', $flag);

        $validated = $request->validate([
            'status' => 'required|in:pending,under_review,dismissed,validated,escalated_to_dispute,user_suspended',
            'moderator_notes' => 'nullable|string',
            'assigned_moderator_id' => 'nullable|exists:users,id',
        ]);

        $flag->update($validated);

        if ($validated['status'] === 'under_review' && ! $flag->reviewed_at) {
            $flag->update(['reviewed_at' => now()]);
        }
        if (in_array($validated['status'], ['dismissed', 'validated', 'escalated_to_dispute', 'user_suspended'])) {
            $flag->update(['resolved_at' => now()]);
        }

        // If user_suspended, could trigger suspension logic
        if ($validated['status'] === 'user_suspended') {
            // Trigger user suspension logic here
        }

        return response()->json(['data' => new UserFlagResource($flag)]);
    }

    /**
     * Get dashboard statistics for fraud/disputes.
     */
    public function stats(Request $request)
    {
        Gate::authorize('viewAny', Dispute::class);

        return response()->json([
            'data' => [
                'disputes' => [
                    'total' => Dispute::count(),
                    'open' => Dispute::where('status', 'open')->count(),
                    'under_review' => Dispute::where('status', 'under_review')->count(),
                    'resolved' => Dispute::where('status', 'resolved')->count(),
                    'closed' => Dispute::where('status', 'closed')->count(),
                    'sla_breached' => Dispute::where('sla_breached', true)->count(),
                    'by_type' => Dispute::selectRaw('type, count(*) as count')
                        ->groupBy('type')
                        ->pluck('count', 'type')
                        ->toArray(),
                    'by_priority' => Dispute::selectRaw('priority, count(*) as count')
                        ->groupBy('priority')
                        ->pluck('count', 'priority')
                        ->toArray(),
                ],
                'listing_flags' => [
                    'total' => ListingFlag::count(),
                    'pending' => ListingFlag::where('status', 'pending')->count(),
                    'under_review' => ListingFlag::where('status', 'under_review')->count(),
                    'validated' => ListingFlag::where('status', 'validated')->count(),
                    'dismissed' => ListingFlag::where('status', 'dismissed')->count(),
                    'escalated' => ListingFlag::where('status', 'escalated_to_dispute')->count(),
                ],
                'user_flags' => [
                    'total' => UserFlag::count(),
                    'pending' => UserFlag::where('status', 'pending')->count(),
                    'under_review' => UserFlag::where('status', 'under_review')->count(),
                    'validated' => UserFlag::where('status', 'validated')->count(),
                    'dismissed' => UserFlag::where('status', 'dismissed')->count(),
                    'escalated' => UserFlag::where('status', 'escalated_to_dispute')->count(),
                ],
            ],
        ]);
    }
}
