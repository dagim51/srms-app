<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrustWebController extends Controller
{
    public function index()
    {
        $stats = [
            'active_cases' => 14,
            'new_reports' => 5,
            'open_disputes' => 8,
            'avg_resolution' => '24h'
        ];

        $recentActivity = [
            ['id' => 'CAS-801', 'type' => 'Fraud Report', 'subject' => 'Suspicious Listing #405', 'status' => 'investigating', 'date' => '2026-10-08'],
            ['id' => 'CAS-802', 'type' => 'Dispute', 'subject' => 'Deposit Deduction', 'status' => 'escalated', 'date' => '2026-10-07'],
            ['id' => 'CAS-803', 'type' => 'Moderation', 'subject' => 'Inappropriate Message', 'status' => 'resolved', 'date' => '2026-10-05'],
            ['id' => 'CAS-804', 'type' => 'Fraud Report', 'subject' => 'Fake Identity Upload', 'status' => 'action_taken', 'date' => '2026-10-02'],
        ];

        return view('frontend.trust.index', compact('stats', 'recentActivity'));
    }

    public function reports()
    {
        $reports = [
            ['id' => 'REP-501', 'reporter' => 'User ID 405', 'reason' => 'Scam/Fraud', 'target' => 'Listing: CMC Condo', 'status' => 'open', 'date' => '2026-10-08'],
            ['id' => 'REP-502', 'reporter' => 'Sara Tadesse', 'reason' => 'Harassment', 'target' => 'User ID 912', 'status' => 'reviewing', 'date' => '2026-10-07'],
            ['id' => 'REP-503', 'reporter' => 'System (Automated)', 'reason' => 'Suspicious IP', 'target' => 'Account: Dawit M.', 'status' => 'resolved', 'date' => '2026-10-06'],
        ];

        return view('frontend.trust.reports', compact('reports'));
    }

    public function disputes()
    {
        $disputes = [
            ['id' => 'DSP-301', 'plaintiff' => 'Abebe Kebede', 'defendant' => 'Property Manager', 'amount' => '5,000 ETB', 'reason' => 'Unfair Deposit Deduction', 'status' => 'escalated', 'date' => '2026-10-08'],
            ['id' => 'DSP-302', 'plaintiff' => 'Sara Cleaning', 'defendant' => 'System', 'amount' => '1,500 ETB', 'reason' => 'Payment Failed', 'status' => 'investigating', 'date' => '2026-10-06'],
        ];

        return view('frontend.trust.disputes', compact('disputes'));
    }

    public function caseShow($id)
    {
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
            'description' => 'Tenant claims that the 5,000 ETB deduction for "painting" was unjustified as the apartment was returned in the same condition it was received.'
        ];

        $evidence = [
            ['type' => 'Image', 'name' => 'move_out_condition_living_room.jpg', 'submitted_by' => 'Abebe Kebede', 'date' => '2026-10-08 10:20'],
            ['type' => 'Document', 'name' => 'move_in_inspection_report.pdf', 'submitted_by' => 'Manager', 'date' => '2026-10-08 11:00'],
        ];

        $timeline = [
            ['action' => 'Dispute Filed', 'user' => 'Abebe Kebede', 'date' => '2026-10-08 10:15 AM'],
            ['action' => 'Evidence Uploaded', 'user' => 'Abebe Kebede', 'date' => '2026-10-08 10:20 AM'],
            ['action' => 'Counter Evidence Provided', 'user' => 'Manager', 'date' => '2026-10-08 11:00 AM'],
            ['action' => 'Escalated to Mediator', 'user' => 'System', 'date' => '2026-10-08 14:00 PM'],
        ];

        return view('frontend.trust.case_show', compact('case', 'evidence', 'timeline'));
    }
}
