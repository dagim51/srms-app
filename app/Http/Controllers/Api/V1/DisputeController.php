<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DisputeEvidenceResource;
use App\Http\Resources\DisputeResource;
use App\Http\Resources\ListingFlagResource;
use App\Http\Resources\UserFlagResource;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Models\Listing;
use App\Models\ListingFlag;
use App\Models\User;
use App\Models\UserFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    /**
     * List current user's disputes.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Dispute::with(['complainant', 'respondent', 'assignedModerator', 'disputable', 'evidence'])
            ->where(function ($q) use ($user) {
                $q->where('complainant_id', $user->id)
                    ->orWhere('respondent_id', $user->id);
            });

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $disputes = $query->orderBy('opened_at', 'desc')->paginate($request->get('per_page', 15));

        return DisputeResource::collection($disputes);
    }

    /**
     * Create a new dispute.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'disputable_type' => 'required|string|in:App\Models\Listing,App\Models\User,App\Models\Offer,App\Models\Reservation,App\Models\Lease',
            'disputable_id' => 'required|uuid',
            'type' => 'required|string|in:deposit_dispute,misrepresentation,non_delivery,quality_issue,contract_breach,fraud_suspected,communication_abuse,other',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'amount_in_dispute' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'evidence' => 'nullable|array',
            'evidence.*.type' => 'required_with:evidence|in:document,screenshot,communication,transaction_record,contract,other',
            'evidence.*.title' => 'required_with:evidence|string|max:255',
            'evidence.*.description' => 'nullable|string',
            'evidence.*.file_path' => 'nullable|string',
            'evidence.*.file_url' => 'nullable|url',
        ]);

        $user = Auth::user();

        // Find the disputable and determine respondent
        $disputableClass = $validated['disputable_type'];
        $disputable = $disputableClass::findOrFail($validated['disputable_id']);

        // Determine respondent based on disputable type
        $respondentId = $this->getRespondentId($disputable, $validated['disputable_type']);

        if ($respondentId === $user->id) {
            return response()->json([
                'message' => 'You cannot file a dispute against yourself.',
            ], 422);
        }

        // Check if dispute already exists for this disputable between these parties
        $existing = Dispute::where('complainant_id', $user->id)
            ->where('disputable_type', $validated['disputable_type'])
            ->where('disputable_id', $validated['disputable_id'])
            ->whereIn('status', ['open', 'under_review', 'awaiting_evidence', 'awaiting_response', 'mediated'])
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'A dispute for this item is already in progress.',
            ], 409);
        }

        $dispute = Dispute::create([
            'complainant_id' => $user->id,
            'respondent_id' => $respondentId,
            'disputable_type' => $validated['disputable_type'],
            'disputable_id' => $validated['disputable_id'],
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'amount_in_dispute' => $validated['amount_in_dispute'] ?? null,
            'currency' => $validated['currency'] ?? 'ETB',
            'status' => 'open',
            'priority' => $validated['priority'] ?? 'normal',
            'opened_at' => now(),
            'sla_hours' => match ($validated['priority'] ?? 'normal') {
                'urgent' => 24,
                'high' => 48,
                'normal' => 72,
                'low' => 120,
                default => 72,
            },
        ]);

        // Create initial evidence if provided
        if (! empty($validated['evidence'])) {
            foreach ($validated['evidence'] as $evidenceData) {
                DisputeEvidence::create([
                    'dispute_id' => $dispute->id,
                    'submitted_by' => $user->id,
                    'type' => $evidenceData['type'],
                    'title' => $evidenceData['title'],
                    'description' => $evidenceData['description'] ?? null,
                    'file_path' => $evidenceData['file_path'] ?? null,
                    'file_url' => $evidenceData['file_url'] ?? null,
                    'mime_type' => $evidenceData['mime_type'] ?? null,
                    'file_size' => $evidenceData['file_size'] ?? null,
                ]);
            }
        }

        $dispute->load(['complainant', 'respondent', 'evidence']);

        return response()->json(['data' => new DisputeResource($dispute)], 201);
    }

    /**
     * Show a dispute (if user is complainant or respondent).
     */
    public function show(Dispute $dispute)
    {
        $user = Auth::user();

        if ($dispute->complainant_id !== $user->id && $dispute->respondent_id !== $user->id) {
            abort(403, 'You are not a party to this dispute.');
        }

        $dispute->load([
            'complainant',
            'respondent',
            'assignedModerator',
            'disputable',
            'evidence.submittedBy',
        ]);

        return response()->json(['data' => new DisputeResource($dispute)]);
    }

    /**
     * Add evidence to a dispute (complainant or respondent).
     */
    public function addEvidence(Request $request, Dispute $dispute)
    {
        $user = Auth::user();

        if ($dispute->complainant_id !== $user->id && $dispute->respondent_id !== $user->id) {
            abort(403, 'You are not a party to this dispute.');
        }

        if (! $dispute->isOpen()) {
            return response()->json([
                'message' => 'Cannot add evidence to a closed dispute.',
            ], 422);
        }

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
            'submitted_by' => $user->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $validated['file_path'] ?? null,
            'file_url' => $validated['file_url'] ?? null,
            'mime_type' => $validated['mime_type'] ?? null,
            'file_size' => $validated['file_size'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return response()->json(['data' => new DisputeEvidenceResource($evidence)], 201);
    }

    /**
     * Create a listing flag (report a listing).
     */
    public function flagListing(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|uuid|exists:listings,id',
            'reason' => 'required|in:scam_fraudulent_price,misleading_photos,duplicate_listing,prohibited_content,spam,personal_info,off_topic,other',
            'description' => 'required|string|min:10|max:2000',
            'severity' => 'nullable|in:low,medium,high,critical',
        ]);

        $user = Auth::user();
        $listing = Listing::findOrFail($validated['listing_id']);

        if ($listing->owner_actor_id === $user->id) {
            return response()->json([
                'message' => 'You cannot flag your own listing.',
            ], 422);
        }

        // Check for duplicate flag
        $existing = ListingFlag::where('listing_id', $listing->id)
            ->where('reporter_id', $user->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You have already reported this listing.',
            ], 409);
        }

        $flag = ListingFlag::create([
            'listing_id' => $listing->id,
            'reporter_id' => $user->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'severity' => $validated['severity'] ?? 'medium',
            'status' => 'pending',
            'reported_at' => now(),
        ]);

        return response()->json(['data' => new ListingFlagResource($flag->load('listing', 'reporter'))], 201);
    }

    /**
     * Create a user flag (report a user).
     */
    public function flagUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'reason' => 'required|in:abusive_communication,harassment,fake_identity,spam,scam_attempt,payment_fraud,contract_violation,other',
            'description' => 'required|string|min:10|max:2000',
            'severity' => 'nullable|in:low,medium,high,critical',
            'context_type' => 'nullable|string',
            'context_id' => 'nullable|uuid',
        ]);

        $user = Auth::user();
        $flaggedUser = User::findOrFail($validated['user_id']);

        if ($flaggedUser->id === $user->id) {
            return response()->json([
                'message' => 'You cannot report yourself.',
            ], 422);
        }

        // Check for duplicate flag
        $existing = UserFlag::where('user_id', $flaggedUser->id)
            ->where('reporter_id', $user->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You have already reported this user.',
            ], 409);
        }

        $flag = UserFlag::create([
            'user_id' => $flaggedUser->id,
            'reporter_id' => $user->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'severity' => $validated['severity'] ?? 'medium',
            'status' => 'pending',
            'context_type' => $validated['context_type'] ?? null,
            'context_id' => $validated['context_id'] ?? null,
            'reported_at' => now(),
        ]);

        return response()->json(['data' => new UserFlagResource($flag->load('user', 'reporter'))], 201);
    }

    /**
     * List current user's listing flags.
     */
    public function myListingFlags(Request $request)
    {
        $user = Auth::user();

        $flags = ListingFlag::with(['listing', 'assignedModerator'])
            ->where('reporter_id', $user->id)
            ->orderBy('reported_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return ListingFlagResource::collection($flags);
    }

    /**
     * List current user's user flags.
     */
    public function myUserFlags(Request $request)
    {
        $user = Auth::user();

        $flags = UserFlag::with(['user', 'assignedModerator', 'context'])
            ->where('reporter_id', $user->id)
            ->orderBy('reported_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return UserFlagResource::collection($flags);
    }

    /**
     * Determine respondent ID based on disputable type.
     */
    private function getRespondentId($disputable, string $type): string
    {
        return match ($type) {
            'App\Models\Listing' => $disputable->owner_actor_id,
            'App\Models\User' => $disputable->id,
            'App\Models\Offer' => $disputable->listing->owner_actor_id,
            'App\Models\Reservation' => $disputable->listing->owner_actor_id,
            'App\Models\Lease' => $disputable->property->owner_user_id,
            default => $disputable->owner_user_id ?? $disputable->owner_actor_id ?? $disputable->user_id,
        };
    }
}
