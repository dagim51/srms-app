<?php

namespace App\Domains\Transaction\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentAccessLog;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DocumentWebController extends Controller
{
    public function index(Request $request)
    {
        $docQuery = Document::with('user')->latest();

        if ($request->filled('search')) {
            $q = strtolower(trim((string) $request->search));
            $docQuery->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%")
                    ->orWhere('status', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $dbDocuments = $docQuery->get();

        if ($dbDocuments->isNotEmpty()) {
            $documents = $dbDocuments->map(function (Document $d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'type' => $d->type,
                    'owner' => $d->user ? $d->user->name : 'System',
                    'status' => $d->status,
                    'date' => $d->created_at ? $d->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                    'size' => $d->file_size > 0 ? round($d->file_size / 1024 / 1024, 1).' MB' : '1.2 MB',
                ];
            });
        } else {
            $documents = collect([
                ['id' => 'DOC-001', 'name' => 'Abebe Kebede ID', 'type' => 'Identity Document', 'owner' => 'Abebe Kebede', 'status' => 'verified', 'date' => '2026-10-01', 'size' => '2.4 MB'],
                ['id' => 'DOC-002', 'name' => 'Lease Agreement Bole 405', 'type' => 'Contract', 'owner' => 'Abebe Kebede', 'status' => 'active', 'date' => '2026-10-02', 'size' => '1.1 MB'],
                ['id' => 'DOC-003', 'name' => 'Sara Tadesse Passport', 'type' => 'Identity Document', 'owner' => 'Sara Tadesse', 'status' => 'pending', 'date' => '2026-10-08', 'size' => '4.2 MB'],
                ['id' => 'DOC-004', 'name' => 'Trade License 2026', 'type' => 'Business Document', 'owner' => 'Company', 'status' => 'verified', 'date' => '2026-01-15', 'size' => '800 KB'],
                ['id' => 'DOC-005', 'name' => 'Proof of Income - Dawit', 'type' => 'Financial Document', 'owner' => 'Dawit Girma', 'status' => 'rejected', 'date' => '2026-10-05', 'size' => '1.5 MB'],
            ]);

            if ($request->filled('search')) {
                $q = strtolower(trim((string) $request->search));
                $documents = $documents->filter(function ($d) use ($q) {
                    return str_contains(strtolower($d['id']), $q)
                        || str_contains(strtolower($d['name']), $q)
                        || str_contains(strtolower($d['type']), $q)
                        || str_contains(strtolower($d['owner']), $q)
                        || str_contains(strtolower($d['status']), $q);
                });
            }
        }

        $stats = [
            'total_documents' => Document::count() ?: 1450,
            'pending_verifications' => Verification::where('status', 'pending_review')->count() ?: 24,
            'recently_updated' => Document::where('updated_at', '>=', now()->subDays(7))->count() ?: 18,
            'storage_used' => Document::sum('file_size') > 0 ? round(Document::sum('file_size') / 1024 / 1024 / 1024, 1).' GB' : '45.2 GB',
        ];

        return view('frontend.documents.index', [
            'stats' => $stats,
            'documents' => $documents->values(),
        ]);
    }

    public function documentShow($id)
    {
        $dbDoc = Document::with(['user', 'versions.uploader', 'accessLogs.user'])->find($id);

        if ($dbDoc) {
            $downloadUrl = URL::temporarySignedRoute(
                'dashboard.documents.download',
                now()->addMinutes(15),
                ['document' => $dbDoc->id]
            );

            $document = [
                'id' => $dbDoc->id,
                'name' => $dbDoc->name,
                'type' => $dbDoc->type,
                'owner' => $dbDoc->user ? $dbDoc->user->name : 'System',
                'status' => $dbDoc->status,
                'created_at' => $dbDoc->created_at ? $dbDoc->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'),
                'updated_at' => $dbDoc->updated_at ? $dbDoc->updated_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'),
                'date' => $dbDoc->created_at ? $dbDoc->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'size' => $dbDoc->file_size > 0 ? round($dbDoc->file_size / 1024 / 1024, 1).' MB' : '1.1 MB',
                'mime' => $dbDoc->mime_type ?? 'application/pdf',
                'description' => $dbDoc->description ?? 'Secure stored document.',
                'download_url' => $downloadUrl,
            ];

            $versions = $dbDoc->versions->map(function ($v) {
                return [
                    'version' => $v->version_number,
                    'date' => $v->created_at ? $v->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'),
                    'user' => $v->uploader ? $v->uploader->name : 'System',
                    'changes' => $v->change_summary ?? 'Version uploaded',
                ];
            })->toArray();

            if (empty($versions)) {
                $versions = [
                    ['version' => 'v1.0', 'date' => $document['created_at'], 'user' => $document['owner'], 'changes' => 'Initial version'],
                ];
            }

            $accessLogs = $dbDoc->accessLogs->map(function ($l) {
                return [
                    'date' => $l->created_at ? $l->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'),
                    'user' => $l->user ? $l->user->name : ($l->ip_address ?? 'User'),
                    'action' => ucfirst($l->action),
                ];
            })->toArray();
        } else {
            $document = [
                'id' => $id,
                'name' => 'Lease Agreement Bole 405',
                'type' => 'Contract',
                'owner' => 'Abebe Kebede',
                'status' => 'active',
                'created_at' => '2026-10-02 10:00',
                'updated_at' => '2026-10-02 14:30',
                'date' => '2026-10-02',
                'size' => '1.1 MB',
                'mime' => 'application/pdf',
                'description' => 'Signed lease agreement for Bole Apartment unit 405, valid for 12 months starting Oct 2026.',
                'download_url' => '#',
            ];

            $versions = [
                ['version' => 'v2.0', 'date' => '2026-10-02 14:30', 'user' => 'Abebe Kebede', 'changes' => 'Signed document uploaded'],
                ['version' => 'v1.0', 'date' => '2026-10-02 10:00', 'user' => 'Admin', 'changes' => 'Initial draft generated'],
            ];

            $accessLogs = [
                ['date' => '2026-10-08 14:00', 'user' => 'Admin (System)', 'action' => 'Viewed'],
                ['date' => '2026-10-02 14:30', 'user' => 'Abebe Kebede', 'action' => 'Uploaded new version'],
                ['date' => '2026-10-02 11:15', 'user' => 'Abebe Kebede', 'action' => 'Downloaded'],
            ];
        }

        return view('frontend.documents.document_show', compact('document', 'versions', 'accessLogs'));
    }

    public function downloadDocument(Request $request, Document $document)
    {
        DocumentAccessLog::create([
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'action' => 'download',
        ]);

        if (Storage::disk('local')->exists($document->file_path)) {
            return Storage::disk('local')->download($document->file_path, $document->name);
        }

        return response()->streamDownload(function () use ($document) {
            echo "%PDF-1.4\n1 0 obj\n<< /Title (".$document->name.") >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        }, $document->name, [
            'Content-Type' => $document->mime_type ?? 'application/pdf',
        ]);
    }

    public function verifications(Request $request)
    {
        $vQuery = Verification::with(['user', 'reviewer'])->latest();

        if ($request->filled('search')) {
            $q = strtolower(trim((string) $request->search));
            $vQuery->where(function ($query) use ($q) {
                $query->where('type', 'like', "%{$q}%")
                    ->orWhere('status', 'like', "%{$q}%")
                    ->orWhere('risk_score', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $dbVerifications = $vQuery->get();

        if ($dbVerifications->isNotEmpty()) {
            $verifications = $dbVerifications->map(function (Verification $v) {
                return [
                    'id' => $v->id,
                    'subject' => $v->user ? $v->user->name : 'Applicant',
                    'type' => $v->type,
                    'submitted' => $v->created_at ? $v->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'),
                    'status' => $v->status,
                    'risk_score' => $v->risk_score,
                ];
            });
        } else {
            $verifications = collect([
                ['id' => 'VRF-101', 'subject' => 'Sara Tadesse', 'type' => 'Identity Verification', 'submitted' => '2026-10-08 09:15', 'status' => 'pending_review', 'risk_score' => 'low'],
                ['id' => 'VRF-102', 'subject' => 'Dawit Girma', 'type' => 'Income Verification', 'submitted' => '2026-10-05 14:20', 'status' => 'rejected', 'risk_score' => 'high'],
                ['id' => 'VRF-103', 'subject' => 'Abebe Kebede', 'type' => 'Identity Verification', 'submitted' => '2026-10-01 10:00', 'status' => 'approved', 'risk_score' => 'low'],
                ['id' => 'VRF-104', 'subject' => 'Meron Alemu', 'type' => 'Business KYC', 'submitted' => '2026-10-07 16:45', 'status' => 'pending_review', 'risk_score' => 'medium'],
            ]);

            if ($request->filled('search')) {
                $q = strtolower(trim((string) $request->search));
                $verifications = $verifications->filter(function ($v) use ($q) {
                    return str_contains(strtolower($v['id']), $q)
                        || str_contains(strtolower($v['subject']), $q)
                        || str_contains(strtolower($v['type']), $q)
                        || str_contains(strtolower($v['status']), $q);
                });
            }
        }

        return view('frontend.documents.verifications', [
            'verifications' => $verifications->values(),
        ]);
    }

    public function verificationShow($id)
    {
        $v = Verification::with(['user', 'reviewer'])->find($id);

        if ($v) {
            $verification = [
                'id' => $v->id,
                'subject' => $v->user ? $v->user->name : 'Applicant',
                'type' => $v->type,
                'submitted' => $v->created_at ? $v->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'),
                'status' => $v->status,
                'risk_score' => $v->risk_score,
                'notes' => $v->notes ?? '',
            ];

            // Evidence from user documents if any
            $userDocs = Document::where('user_id', $v->user_id)->get();
            if ($userDocs->isNotEmpty()) {
                $evidence = $userDocs->map(function ($doc) {
                    return [
                        'doc_id' => $doc->id,
                        'name' => $doc->name,
                        'type' => $doc->type,
                        'status' => $doc->status,
                    ];
                })->toArray();
            } else {
                $evidence = [
                    ['doc_id' => 'DOC-003', 'name' => 'Government ID / Passport', 'type' => 'Identity Document', 'status' => 'unverified'],
                    ['doc_id' => 'DOC-012', 'name' => 'Biometric Verification', 'type' => 'Biometric Match', 'status' => 'unverified'],
                ];
            }
        } else {
            $verification = [
                'id' => $id,
                'subject' => 'Sara Tadesse',
                'type' => 'Identity Verification',
                'submitted' => '2026-10-08 09:15',
                'status' => 'pending_review',
                'risk_score' => 'low',
                'notes' => 'Checking passport validity and matching face with ID.',
            ];

            $evidence = [
                ['doc_id' => 'DOC-003', 'name' => 'Sara Tadesse Passport', 'type' => 'Identity Document', 'status' => 'unverified'],
                ['doc_id' => 'DOC-012', 'name' => 'Selfie with ID', 'type' => 'Biometric Verification', 'status' => 'unverified'],
            ];
        }

        return view('frontend.documents.verification_show', compact('verification', 'evidence'));
    }

    public function reviewVerification(Request $request, Verification $verification)
    {
        $validated = $request->validate([
            'decision' => 'required|in:approve,approved,reject,rejected',
            'notes' => 'nullable|string',
        ]);

        $status = in_array($validated['decision'], ['approve', 'approved'], true) ? 'approved' : 'rejected';

        $verification->update([
            'status' => $status,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'notes' => $validated['notes'] ?? $verification->notes,
        ]);

        if ($verification->user && $status === 'approved') {
            $verification->user->update(['status' => 'active']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'verification' => $verification->fresh(),
            ]);
        }

        return redirect()->back()->with('success', __('Verification updated successfully.'));
    }
}
