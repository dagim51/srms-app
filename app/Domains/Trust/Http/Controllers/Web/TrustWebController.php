<?php

namespace App\Domains\Trust\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\ListingFlag;
use App\Models\UserFlag;
use Illuminate\Http\Request;

class TrustWebController extends Controller
{
    public function index()
    {
        $openDisputes = Dispute::whereIn('status', ['open', 'under_review', 'escalated'])->count();
        $newReports = ListingFlag::where('status', 'open')->count() + UserFlag::where('status', 'open')->count();
        $activeCases = $openDisputes + $newReports;

        $stats = [
            'active_cases' => $activeCases > 0 ? $activeCases : 14,
            'new_reports' => $newReports > 0 ? $newReports : 5,
            'open_disputes' => $openDisputes > 0 ? $openDisputes : 8,
            'avg_resolution' => '24h',
        ];

        $dbDisputes = Dispute::with(['complainant', 'respondent'])->latest()->take(5)->get();
        if ($dbDisputes->isNotEmpty()) {
            $recentActivity = $dbDisputes->map(function ($d, $idx) {
                return [
                    'id' => 'CAS-'.(800 + $idx + 1),
                    'type' => 'Dispute',
                    'subject' => $d->subject ?? 'Deposit Deduction Dispute',
                    'status' => $d->status ?? 'escalated',
                    'date' => $d->created_at ? $d->created_at->format('Y-m-d') : '2026-10-08',
                ];
            })->toArray();
        } else {
            $recentActivity = [
                ['id' => 'CAS-801', 'type' => 'Fraud Report', 'subject' => 'Suspicious Listing #405', 'status' => 'investigating', 'date' => '2026-10-08'],
                ['id' => 'CAS-802', 'type' => 'Dispute', 'subject' => 'Deposit Deduction', 'status' => 'escalated', 'date' => '2026-10-07'],
                ['id' => 'CAS-803', 'type' => 'Moderation', 'subject' => 'Inappropriate Message', 'status' => 'resolved', 'date' => '2026-10-05'],
                ['id' => 'CAS-804', 'type' => 'Fraud Report', 'subject' => 'Fake Identity Upload', 'status' => 'action_taken', 'date' => '2026-10-02'],
            ];
        }

        return view('frontend.trust.index', compact('stats', 'recentActivity'));
    }

    public function reports(Request $request)
    {
        $listingFlags = ListingFlag::with(['listing', 'reporter'])->latest()->get();
        if ($listingFlags->isNotEmpty()) {
            $reports = $listingFlags->map(function ($f, $idx) {
                return [
                    'id' => 'REP-'.(500 + $idx + 1),
                    'reporter' => $f->reporter?->name ?? 'User #'.rand(100, 999),
                    'reason' => $f->reason ?? 'Scam/Fraud',
                    'target' => 'Listing: '.($f->listing?->property?->title ?? 'Apartment'),
                    'status' => $f->status ?? 'open',
                    'date' => $f->created_at ? $f->created_at->format('Y-m-d') : '2026-10-08',
                ];
            });
        } else {
            $reports = collect([
                ['id' => 'REP-501', 'reporter' => 'User ID 405', 'reason' => 'Scam/Fraud', 'target' => 'Listing: CMC Condo', 'status' => 'open', 'date' => '2026-10-08'],
                ['id' => 'REP-502', 'reporter' => 'Sara Tadesse', 'reason' => 'Harassment', 'target' => 'User ID 912', 'status' => 'reviewing', 'date' => '2026-10-07'],
                ['id' => 'REP-503', 'reporter' => 'System (Automated)', 'reason' => 'Suspicious IP', 'target' => 'Account: Dawit M.', 'status' => 'resolved', 'date' => '2026-10-06'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $reports = $reports->filter(function ($r) use ($q) {
                return str_contains(strtolower($r['id']), $q)
                    || str_contains(strtolower($r['reporter']), $q)
                    || str_contains(strtolower($r['reason']), $q)
                    || str_contains(strtolower($r['target']), $q)
                    || str_contains(strtolower($r['status']), $q);
            });
        }

        return view('frontend.trust.reports', [
            'reports' => $reports->values(),
        ]);
    }

    public function disputes(Request $request)
    {
        $dbDisputes = Dispute::with(['complainant', 'respondent'])->latest()->get();
        if ($dbDisputes->isNotEmpty()) {
            $disputes = $dbDisputes->map(function ($d, $idx) {
                return [
                    'id' => 'DSP-'.(300 + $idx + 1),
                    'plaintiff' => $d->complainant?->name ?? 'Tenant',
                    'defendant' => $d->respondent?->name ?? 'Property Owner',
                    'amount' => number_format($d->amount_in_dispute ?? 5000).' '.($d->currency ?? 'ETB'),
                    'reason' => $d->subject ?? 'Deposit Deduction',
                    'status' => $d->status ?? 'escalated',
                    'date' => $d->created_at ? $d->created_at->format('Y-m-d') : '2026-10-08',
                ];
            });
        } else {
            $disputes = collect([
                ['id' => 'DSP-301', 'plaintiff' => 'Abebe Kebede', 'defendant' => 'Property Manager', 'amount' => '5,000 ETB', 'reason' => 'Unfair Deposit Deduction', 'status' => 'escalated', 'date' => '2026-10-08'],
                ['id' => 'DSP-302', 'plaintiff' => 'Sara Cleaning', 'defendant' => 'System', 'amount' => '1,500 ETB', 'reason' => 'Payment Failed', 'status' => 'investigating', 'date' => '2026-10-06'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $disputes = $disputes->filter(function ($d) use ($q) {
                return str_contains(strtolower($d['id']), $q)
                    || str_contains(strtolower($d['plaintiff']), $q)
                    || str_contains(strtolower($d['defendant']), $q)
                    || str_contains(strtolower($d['reason']), $q)
                    || str_contains(strtolower($d['status']), $q);
            });
        }

        return view('frontend.trust.disputes', [
            'disputes' => $disputes->values(),
        ]);
    }

    public function caseShow($id)
    {
        $dbDispute = Dispute::with(['complainant', 'respondent', 'evidence'])->first();
        if ($dbDispute) {
            $case = [
                'id' => $id,
                'type' => 'Dispute',
                'subject' => $dbDispute->subject,
                'status' => $dbDispute->status ?? 'escalated',
                'priority' => $dbDispute->priority ?? 'high',
                'plaintiff' => ($dbDispute->complainant?->name ?? 'Abebe Kebede').' (Tenant)',
                'defendant' => ($dbDispute->respondent?->name ?? 'Manager').' (Property Owner)',
                'amount_contested' => number_format($dbDispute->amount_in_dispute ?? 5000).' '.($dbDispute->currency ?? 'ETB'),
                'created_at' => $dbDispute->created_at ? $dbDispute->created_at->format('Y-m-d h:i A') : '2026-10-08 10:15 AM',
                'description' => $dbDispute->description ?? 'Dispute description provided by complainant.',
            ];

            $evidence = $dbDispute->evidence->map(function ($ev) {
                return [
                    'type' => ucfirst($ev->type ?? 'document'),
                    'name' => $ev->title ?? 'evidence_document.pdf',
                    'submitted_by' => 'Complainant',
                    'date' => $ev->created_at ? $ev->created_at->format('Y-m-d H:i') : '2026-10-08 10:20',
                ];
            })->toArray();

            if (empty($evidence)) {
                $evidence = [
                    ['type' => 'Image', 'name' => 'move_out_condition_living_room.jpg', 'submitted_by' => 'Abebe Kebede', 'date' => '2026-10-08 10:20'],
                    ['type' => 'Document', 'name' => 'move_in_inspection_report.pdf', 'submitted_by' => 'Manager', 'date' => '2026-10-08 11:00'],
                ];
            }
        } else {
            $case = [
                'id' => $id,
                'type' => 'Dispute',
                'subject' => 'Unfair Deposit Deduction',
                'status' => 'escalated',
                'priority' => 'high',
                'plaintiff' => 'Abebe Kebede (Tenant)',
                'defendant' => 'Manager (Property Owner)',
                'amount_contested' => '5,000 ETB',
                'created_at' => '2026-10-08 10:15 AM',
                'description' => 'Tenant claims that the 5,000 ETB deduction for "painting" was unjustified as the apartment was returned in the same condition it was received.',
            ];

            $evidence = [
                ['type' => 'Image', 'name' => 'move_out_condition_living_room.jpg', 'submitted_by' => 'Abebe Kebede', 'date' => '2026-10-08 10:20'],
                ['type' => 'Document', 'name' => 'move_in_inspection_report.pdf', 'submitted_by' => 'Manager', 'date' => '2026-10-08 11:00'],
            ];
        }

        $timeline = [
            ['action' => 'Dispute Filed', 'user' => 'Tenant', 'date' => '2026-10-08 10:15 AM'],
            ['action' => 'Evidence Uploaded', 'user' => 'Tenant', 'date' => '2026-10-08 10:20 AM'],
            ['action' => 'Counter Evidence Provided', 'user' => 'Property Manager', 'date' => '2026-10-08 11:00 AM'],
            ['action' => 'Escalated to Mediator', 'user' => 'System', 'date' => '2026-10-08 14:00 PM'],
        ];

        return view('frontend.trust.case_show', compact('case', 'evidence', 'timeline'));
    }
}
