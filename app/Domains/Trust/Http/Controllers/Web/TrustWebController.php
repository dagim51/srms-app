<?php

namespace App\Domains\Trust\Http\Controllers\Web;

use App\Domains\Listing\Models\ListingFlag;
use App\Domains\Property\Models\Property;
use App\Domains\Trust\Models\Dispute;
use App\Domains\Trust\Models\DisputeEvidence;
use App\Domains\Trust\Models\UserFlag;
use App\Domains\Trust\Models\Verification;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrustWebController extends Controller
{
    public function index()
    {
        $openDisputes = Dispute::whereIn('status', ['open', 'under_review', 'escalated'])->count();
        $newReports = ListingFlag::where('status', 'open')->count() + UserFlag::where('status', 'open')->count();
        $pendingVerifications = Verification::whereIn('status', ['pending', 'pending_review'])->count();
        $activeCases = $openDisputes + $newReports;

        $stats = [
            'active_cases' => $activeCases,
            'new_reports' => $newReports,
            'open_disputes' => $openDisputes,
            'pending_verifications' => $pendingVerifications,
            'avg_resolution' => '24h',
        ];

        $recentActivity = Dispute::with(['complainant', 'respondent'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($d) {
                return [
                    'id' => 'CAS-'.strtoupper(substr($d->id, 0, 8)),
                    'raw_id' => $d->id,
                    'type' => ucwords(str_replace('_', ' ', $d->type ?? 'Dispute')),
                    'subject' => $d->subject ?? 'Dispute Case',
                    'status' => $d->status ?? 'open',
                    'date' => $d->created_at ? $d->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                ];
            })->toArray();

        $recentVerifications = Verification::with('user')
            ->latest()
            ->take(5)
            ->get();

        $users = User::select('id', 'name', 'email')->orderBy('name')->take(50)->get();
        $properties = Property::select('id', 'title', 'property_code')->latest()->take(30)->get();

        return view('frontend.trust.index', compact('stats', 'recentActivity', 'recentVerifications', 'users', 'properties'));
    }

    public function reports(Request $request)
    {
        $query = ListingFlag::with(['listing.property', 'reporter'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('reason', 'like', "%{$q}%")
                    ->orWhereHas('reporter', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('listing.property', function ($p) use ($q) {
                        $p->where('title', 'like', "%{$q}%");
                    });
            });
        }

        $reports = $query->paginate(20)->withQueryString()->through(function ($f) {
            return [
                'raw_id' => $f->id,
                'id' => 'REP-'.strtoupper(substr($f->id, 0, 8)),
                'reporter' => $f->reporter?->name ?? 'Anonymous Reporter',
                'reason' => $f->reason ?? 'Flagged Content',
                'description' => $f->description ?? $f->reason,
                'target' => 'Listing: '.($f->listing?->property?->title ?? 'Real Estate Property'),
                'severity' => $f->severity ?? 'medium',
                'status' => $f->status ?? 'open',
                'moderator_notes' => $f->moderator_notes,
                'date' => $f->created_at ? $f->created_at->format('Y-m-d') : now()->format('Y-m-d'),
            ];
        });

        return view('frontend.trust.reports', compact('reports'));
    }

    public function disputes(Request $request)
    {
        $query = Dispute::with(['complainant', 'respondent'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('subject', 'like', "%{$q}%")
                    ->orWhere('id', 'like', "%{$q}%")
                    ->orWhereHas('complainant', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('respondent', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $disputes = $query->paginate(20)->withQueryString()->through(function ($d) {
            return [
                'raw_id' => $d->id,
                'id' => 'DSP-'.strtoupper(substr($d->id, 0, 8)),
                'plaintiff' => $d->complainant?->name ?? 'Complainant',
                'defendant' => $d->respondent?->name ?? 'Respondent',
                'amount' => number_format((float) ($d->amount_in_dispute ?? 0)).' '.($d->currency ?? 'ETB'),
                'raw_amount' => $d->amount_in_dispute ?? 0,
                'reason' => $d->subject ?? 'Dispute Case',
                'type' => ucwords(str_replace('_', ' ', $d->type ?? 'Contract Dispute')),
                'status' => $d->status ?? 'open',
                'priority' => $d->priority ?? 'medium',
                'date' => $d->created_at ? $d->created_at->format('Y-m-d') : now()->format('Y-m-d'),
            ];
        });

        $users = User::select('id', 'name', 'email')->orderBy('name')->take(50)->get();
        $properties = Property::select('id', 'title', 'property_code')->latest()->take(30)->get();

        return view('frontend.trust.disputes', compact('disputes', 'users', 'properties'));
    }

    public function storeCase(Request $request)
    {
        $validated = $request->validate([
            'complainant_id' => 'required|exists:users,id',
            'respondent_id' => 'required|exists:users,id|different:complainant_id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'type' => 'required|string|in:contract_dispute,payment_issue,damage_claim,fraud_suspect,escrow_violation',
            'amount_in_dispute' => 'nullable|numeric|min:0',
            'priority' => 'required|in:low,medium,high,urgent',
            'disputable_id' => 'nullable|string',
        ]);

        $dispute = Dispute::create([
            'complainant_id' => $validated['complainant_id'],
            'respondent_id' => $validated['respondent_id'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'amount_in_dispute' => $validated['amount_in_dispute'] ?? 0,
            'currency' => 'ETB',
            'status' => 'open',
            'priority' => $validated['priority'],
            'disputable_type' => Property::class,
            'disputable_id' => $validated['disputable_id'] ?? Property::first()?->id ?? Str::uuid(),
            'opened_at' => now(),
        ]);

        return redirect()->route('dashboard.trust.cases.show', $dispute->id)->with('success', 'Dispute case successfully logged and registered for mediation.');
    }

    public function caseShow($id)
    {
        $dbDispute = Dispute::with(['complainant', 'respondent', 'evidence'])->find($id);

        if (! $dbDispute && (str_starts_with((string) $id, 'DSP-') || str_starts_with((string) $id, 'CAS-'))) {
            $short = strtolower(substr((string) $id, 4));
            $dbDispute = Dispute::with(['complainant', 'respondent', 'evidence'])
                ->where('id', 'like', $short.'%')
                ->first();
        }

        // If not found as dispute, check if this is a ListingFlag report
        if (! $dbDispute) {
            $flag = ListingFlag::with(['listing.property', 'reporter'])->find($id);
            if (! $flag && str_starts_with((string) $id, 'REP-')) {
                $short = strtolower(substr((string) $id, 4));
                $flag = ListingFlag::with(['listing.property', 'reporter'])->where('id', 'like', $short.'%')->first();
            }

            if ($flag) {
                $case = [
                    'id' => $flag->id,
                    'code' => 'REP-'.strtoupper(substr($flag->id, 0, 8)),
                    'type' => 'User Marketplace Report',
                    'subject' => 'Listing Flag: '.($flag->reason ?? 'Reported Content'),
                    'status' => $flag->status ?? 'open',
                    'priority' => $flag->severity ?? 'medium',
                    'plaintiff' => ($flag->reporter?->name ?? 'Marketplace Reporter').' (Reporter)',
                    'defendant' => ($flag->listing?->property?->title ?? 'Reported Property').' (Target Listing)',
                    'amount_contested' => '0.00 ETB',
                    'created_at' => $flag->created_at ? $flag->created_at->format('Y-m-d h:i A') : now()->format('Y-m-d h:i A'),
                    'description' => $flag->description ?? ($flag->reason ?? 'Reported for review.'),
                    'is_report' => true,
                    'raw_id' => $flag->id,
                    'resolution_notes' => $flag->moderator_notes,
                ];

                $evidence = [];
                $timeline = [
                    ['action' => 'Report Submitted', 'user' => $flag->reporter?->name ?? 'Reporter', 'date' => $case['created_at']],
                    ['action' => 'Report Queue Registered', 'user' => 'Safety System', 'date' => $case['created_at']],
                ];

                return view('frontend.trust.case_show', compact('case', 'evidence', 'timeline'));
            }

            $dbDispute = Dispute::with(['complainant', 'respondent', 'evidence'])->findOrFail($id);
        }

        $case = [
            'id' => $dbDispute->id,
            'code' => 'DSP-'.strtoupper(substr($dbDispute->id, 0, 8)),
            'type' => ucwords(str_replace('_', ' ', $dbDispute->type ?? 'Dispute')),
            'subject' => $dbDispute->subject,
            'status' => $dbDispute->status ?? 'open',
            'priority' => $dbDispute->priority ?? 'medium',
            'plaintiff' => ($dbDispute->complainant?->name ?? 'Complainant').' (Party A)',
            'defendant' => ($dbDispute->respondent?->name ?? 'Respondent').' (Party B)',
            'amount_contested' => number_format((float) ($dbDispute->amount_in_dispute ?? 0)).' '.($dbDispute->currency ?? 'ETB'),
            'raw_amount' => $dbDispute->amount_in_dispute ?? 0,
            'created_at' => $dbDispute->created_at ? $dbDispute->created_at->format('Y-m-d h:i A') : now()->format('Y-m-d h:i A'),
            'description' => $dbDispute->description ?? 'No description provided.',
            'resolution_notes' => $dbDispute->resolution_notes,
            'resolved_at' => $dbDispute->resolved_at ? $dbDispute->resolved_at->format('Y-m-d h:i A') : null,
            'is_report' => false,
            'raw_id' => $dbDispute->id,
        ];

        $evidence = $dbDispute->evidence ? $dbDispute->evidence->map(function ($ev) {
            return [
                'id' => $ev->id,
                'type' => ucfirst($ev->type ?? 'document'),
                'name' => $ev->title ?? 'evidence_document.pdf',
                'submitted_by' => 'Party Member',
                'date' => $ev->created_at ? $ev->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'),
                'description' => $ev->description ?? 'Certified case submission.',
            ];
        })->toArray() : [];

        $timeline = [
            ['action' => 'Dispute Formally Filed', 'user' => $dbDispute->complainant?->name ?? 'Complainant', 'date' => $case['created_at']],
        ];

        if ($dbDispute->status === 'under_review') {
            $timeline[] = ['action' => 'Case Under Mediation Review', 'user' => 'Trust Officer', 'date' => now()->format('Y-m-d H:i')];
        }

        if ($dbDispute->resolved_at) {
            $timeline[] = ['action' => 'Case Resolved / Mediated', 'user' => 'Mediation Authority', 'date' => $case['resolved_at']];
        }

        return view('frontend.trust.case_show', compact('case', 'evidence', 'timeline'));
    }

    public function resolveCase(Request $request, $id)
    {
        $dispute = Dispute::where('id', $id)->first();
        if (! $dispute && (str_starts_with((string) $id, 'DSP-') || str_starts_with((string) $id, 'CAS-'))) {
            $short = strtolower(substr((string) $id, 4));
            $dispute = Dispute::where('id', 'like', $short.'%')->first();
        }

        if (! $dispute) {
            $dispute = Dispute::findOrFail($id);
        }

        $validated = $request->validate([
            'resolution_notes' => 'nullable|string|max:2000',
            'status' => 'required|string|in:resolved,closed,dismissed,under_review,escalated',
        ]);

        $dispute->update([
            'status' => $validated['status'],
            'resolution_notes' => $validated['resolution_notes'] ?? 'Dispute case reviewed and status finalized by mediator.',
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed', 'dismissed']) ? now() : null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'dispute' => $dispute->fresh(),
            ]);
        }

        return redirect()->back()->with('success', __('Dispute case status updated: :status.', ['status' => ucfirst($validated['status'])]));
    }

    public function uploadEvidence(Request $request, $id)
    {
        $dispute = Dispute::where('id', $id)->first();
        if (! $dispute && (str_starts_with((string) $id, 'DSP-') || str_starts_with((string) $id, 'CAS-'))) {
            $short = strtolower(substr((string) $id, 4));
            $dispute = Dispute::where('id', 'like', $short.'%')->first();
        }

        if (! $dispute) {
            $dispute = Dispute::findOrFail($id);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:document,image,contract,payment_slip,audio',
            'description' => 'nullable|string|max:1000',
            'file' => 'nullable|file|max:10240',
        ]);

        $filePath = 'evidence/manual_upload.pdf';
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('evidence', 'public');
        }

        DisputeEvidence::create([
            'dispute_id' => $dispute->id,
            'submitted_by' => auth()->id() ?? $dispute->complainant_id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? 'Submitted verification evidence',
            'file_path' => $filePath,
            'is_verified' => true,
        ]);

        return redirect()->back()->with('success', __('Evidence record attached to the dispute dossier.'));
    }

    public function updateReportStatus(Request $request, ListingFlag $flag)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,under_review,resolved,dismissed',
            'moderator_notes' => 'nullable|string',
        ]);

        $flag->update([
            'status' => $validated['status'],
            'moderator_notes' => $validated['moderator_notes'] ?? $flag->moderator_notes,
            'assigned_moderator_id' => auth()->id(),
            'reviewed_at' => now(),
            'resolved_at' => in_array($validated['status'], ['resolved', 'dismissed'], true) ? now() : null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'flag' => $flag->fresh(),
            ]);
        }

        return redirect()->back()->with('success', __('User report status updated successfully.'));
    }

    public function verifications(Request $request)
    {
        $query = Verification::with(['user', 'reviewer'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim((string) $request->search));
            $query->where(function ($sub) use ($q) {
                $sub->where('type', 'like', "%{$q}%")
                    ->orWhere('status', 'like', "%{$q}%")
                    ->orWhere('risk_score', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        $verifications = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Verification::count(),
            'pending' => Verification::whereIn('status', ['pending', 'pending_review'])->count(),
            'approved' => Verification::where('status', 'approved')->count(),
            'rejected' => Verification::where('status', 'rejected')->count(),
        ];

        return view('frontend.trust.verifications', compact('verifications', 'stats'));
    }

    public function reviewVerification(Request $request, $id)
    {
        $verification = Verification::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,additional_info_required,pending_review',
            'notes' => 'nullable|string|max:1000',
            'risk_score' => 'nullable|in:low,medium,high',
        ]);

        $verification->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $verification->notes,
            'risk_score' => $validated['risk_score'] ?? $verification->risk_score,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', __('Verification status updated to :status.', ['status' => ucfirst(str_replace('_', ' ', $validated['status']))]));
    }
}
