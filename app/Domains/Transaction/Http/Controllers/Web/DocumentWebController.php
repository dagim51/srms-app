<?php

namespace App\Domains\Transaction\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentWebController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_documents' => 1450,
            'pending_verifications' => 24,
            'recently_updated' => 18,
            'storage_used' => '45.2 GB',
        ];

        $documents = collect([
            ['id' => 'DOC-001', 'name' => 'Abebe Kebede ID', 'type' => 'Identity Document', 'owner' => 'Abebe Kebede', 'status' => 'verified', 'date' => '2026-10-01', 'size' => '2.4 MB'],
            ['id' => 'DOC-002', 'name' => 'Lease Agreement Bole 405', 'type' => 'Contract', 'owner' => 'Abebe Kebede', 'status' => 'active', 'date' => '2026-10-02', 'size' => '1.1 MB'],
            ['id' => 'DOC-003', 'name' => 'Sara Tadesse Passport', 'type' => 'Identity Document', 'owner' => 'Sara Tadesse', 'status' => 'pending', 'date' => '2026-10-08', 'size' => '4.2 MB'],
            ['id' => 'DOC-004', 'name' => 'Trade License 2026', 'type' => 'Business Document', 'owner' => 'Company', 'status' => 'verified', 'date' => '2026-01-15', 'size' => '800 KB'],
            ['id' => 'DOC-005', 'name' => 'Proof of Income - Dawit', 'type' => 'Financial Document', 'owner' => 'Dawit Girma', 'status' => 'rejected', 'date' => '2026-10-05', 'size' => '1.5 MB'],
        ]);

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $documents = $documents->filter(function ($d) use ($q) {
                return str_contains(strtolower($d['id']), $q)
                    || str_contains(strtolower($d['name']), $q)
                    || str_contains(strtolower($d['type']), $q)
                    || str_contains(strtolower($d['owner']), $q)
                    || str_contains(strtolower($d['status']), $q);
            });
        }

        return view('frontend.documents.index', [
            'stats' => $stats,
            'documents' => $documents->values(),
        ]);
    }

    public function documentShow($id)
    {
        $document = [
            'id' => $id,
            'name' => 'Lease Agreement Bole 405',
            'type' => 'Contract',
            'owner' => 'Abebe Kebede',
            'status' => 'active',
            'created_at' => '2026-10-02 10:00',
            'updated_at' => '2026-10-02 14:30',
            'size' => '1.1 MB',
            'mime' => 'application/pdf',
            'description' => 'Signed lease agreement for Bole Apartment unit 405, valid for 12 months starting Oct 2026.',
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

        return view('frontend.documents.document_show', compact('document', 'versions', 'accessLogs'));
    }

    public function verifications(Request $request)
    {
        $verifications = collect([
            ['id' => 'VRF-101', 'subject' => 'Sara Tadesse', 'type' => 'Identity Verification', 'submitted' => '2026-10-08 09:15', 'status' => 'pending_review', 'risk_score' => 'low'],
            ['id' => 'VRF-102', 'subject' => 'Dawit Girma', 'type' => 'Income Verification', 'submitted' => '2026-10-05 14:20', 'status' => 'rejected', 'risk_score' => 'high'],
            ['id' => 'VRF-103', 'subject' => 'Abebe Kebede', 'type' => 'Identity Verification', 'submitted' => '2026-10-01 10:00', 'status' => 'approved', 'risk_score' => 'low'],
            ['id' => 'VRF-104', 'subject' => 'Meron Alemu', 'type' => 'Business KYC', 'submitted' => '2026-10-07 16:45', 'status' => 'pending_review', 'risk_score' => 'medium'],
        ]);

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $verifications = $verifications->filter(function ($v) use ($q) {
                return str_contains(strtolower($v['id']), $q)
                    || str_contains(strtolower($v['subject']), $q)
                    || str_contains(strtolower($v['type']), $q)
                    || str_contains(strtolower($v['status']), $q);
            });
        }

        return view('frontend.documents.verifications', [
            'verifications' => $verifications->values(),
        ]);
    }

    public function verificationShow($id)
    {
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

        return view('frontend.documents.verification_show', compact('verification', 'evidence'));
    }
}
