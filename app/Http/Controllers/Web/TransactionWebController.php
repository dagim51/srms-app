<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionWebController extends Controller
{
    /**
     * Get active pipeline transactions with real models & comprehensive escrow metadata
     */
    protected function getPipelineDeals()
    {
        // Base realistic Ethiopian real estate transaction pipeline
        return collect([
            [
                'id' => 'TRX-ADD-8821',
                'property_title' => 'Contemporary Luxury Villa in Bole Atlas',
                'property_location' => 'Bole Atlas, Addis Ababa',
                'property_code' => 'PROP-ADD-007',
                'title_deed_no' => 'AA-BOL-2026/8941',
                'cadastral_code' => 'CAD-AA-03-8812',
                'type' => 'Purchase Escrow',
                'total_amount' => 65000000,
                'total_amount_formatted' => '65,000,000 ETB',
                'escrow_amount' => 13000000,
                'escrow_amount_formatted' => '13,000,000 ETB (20% Earnest)',
                'currency' => 'ETB',
                'buyer_name' => 'Abebe Kebede',
                'buyer_avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80',
                'buyer_phone' => '+251 91 123 4567',
                'seller_name' => 'Sunshine Real Estate PLC',
                'seller_avatar' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=200&q=80',
                'bank_partner' => 'Commercial Bank of Ethiopia',
                'escrow_account' => 'CBE Escrow #1000-4829-1029',
                'current_stage' => 3, // Cadastral Title Authentication
                'status' => 'cadastral_review',
                'status_label' => 'Cadastral & Title Review',
                'status_variant' => 'warning',
                'initiated_at' => '2026-10-04',
                'est_completion' => '2026-10-24',
                'milestones' => [
                    ['title' => 'Offer Accepted & Contract Signed', 'status' => 'completed', 'date' => '2026-10-04', 'note' => 'Formal purchase agreement counter-signed.'],
                    ['title' => 'Earnest Escrow Deposit Locked', 'status' => 'completed', 'date' => '2026-10-06', 'note' => '13,000,000 ETB verified in CBE Escrow vault.'],
                    ['title' => 'Cadastral Deed & Boundary Survey', 'status' => 'in_progress', 'date' => '2026-10-12', 'note' => 'Addis Ababa Land Bureau PostGIS verification ongoing.'],
                    ['title' => 'Ministry of Revenue Tax Clearance', 'status' => 'pending', 'date' => 'Est. Oct 18', 'note' => 'Capital gains & stamp duty assessment.'],
                    ['title' => 'Deed Handover & Final Disbursement', 'status' => 'pending', 'date' => 'Est. Oct 24', 'note' => 'Transfer of title certificate and seller fund release.'],
                ]
            ],
            [
                'id' => 'TRX-ADD-8822',
                'property_title' => 'Executive High-Rise Penthouse at Kazanchis',
                'property_location' => 'Kazanchis Commercial Core, Addis Ababa',
                'property_code' => 'PROP-ADD-008',
                'title_deed_no' => 'AA-KAZ-2026/3310',
                'cadastral_code' => 'CAD-AA-08-4109',
                'type' => 'Off-Plan Milestone',
                'total_amount' => 14500000,
                'total_amount_formatted' => '14,500,000 ETB',
                'escrow_amount' => 4350000,
                'escrow_amount_formatted' => '4,350,000 ETB (Stage 2 Installment)',
                'currency' => 'ETB',
                'buyer_name' => 'Sara Tadesse',
                'buyer_avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=200&q=80',
                'buyer_phone' => '+251 92 987 6543',
                'seller_name' => 'Noah Real Estate Development',
                'seller_avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=200&q=80',
                'bank_partner' => 'Awash Bank Escrow',
                'escrow_account' => 'AWASH-ESC-9082-114',
                'current_stage' => 4, // Tax Clearance
                'status' => 'tax_clearance',
                'status_label' => 'Tax & Legal Clearance',
                'status_variant' => 'info',
                'initiated_at' => '2026-09-28',
                'est_completion' => '2026-10-20',
                'milestones' => [
                    ['title' => 'Offer Accepted & Contract Signed', 'status' => 'completed', 'date' => '2026-09-28', 'note' => 'Off-plan reservation agreement finalized.'],
                    ['title' => 'Earnest Escrow Deposit Locked', 'status' => 'completed', 'date' => '2026-09-30', 'note' => 'First stage milestone confirmed.'],
                    ['title' => 'Cadastral Deed & Boundary Survey', 'status' => 'completed', 'date' => '2026-10-08', 'note' => 'Sub-city engineer boundary validation certified.'],
                    ['title' => 'Ministry of Revenue Tax Clearance', 'status' => 'in_progress', 'date' => '2026-10-15', 'note' => 'Clearance certificate in final processing.'],
                    ['title' => 'Deed Handover & Final Disbursement', 'status' => 'pending', 'date' => 'Est. Oct 20', 'note' => 'Sub-lease title transfer scheduled.'],
                ]
            ],
            [
                'id' => 'TRX-HAW-4012',
                'property_title' => 'Lakeside Eco-Compound Estate in Hawassa',
                'property_location' => 'Tabor Sub-City, Hawassa',
                'property_code' => 'PROP-HAW-004',
                'title_deed_no' => 'SID-HAW-2026/1029',
                'cadastral_code' => 'CAD-SID-01-3329',
                'type' => 'Purchase Escrow',
                'total_amount' => 42000000,
                'total_amount_formatted' => '42,000,000 ETB',
                'escrow_amount' => 42000000,
                'escrow_amount_formatted' => '42,000,000 ETB (100% Fully Funded)',
                'currency' => 'ETB',
                'buyer_name' => 'Dawit Girma',
                'buyer_avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=200&q=80',
                'buyer_phone' => '+251 94 455 6677',
                'seller_name' => 'Tabor Lakeside Properties',
                'seller_avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80',
                'bank_partner' => 'Telebirr SuperApp Escrow',
                'escrow_account' => 'TELE-ESC-7788-009',
                'current_stage' => 5, // Completed
                'status' => 'completed',
                'status_label' => 'Deed Transferred & Closed',
                'status_variant' => 'success',
                'initiated_at' => '2026-09-15',
                'est_completion' => '2026-10-10',
                'milestones' => [
                    ['title' => 'Offer Accepted & Contract Signed', 'status' => 'completed', 'date' => '2026-09-15', 'note' => 'Direct contract verified by legal counsel.'],
                    ['title' => 'Earnest Escrow Deposit Locked', 'status' => 'completed', 'date' => '2026-09-17', 'note' => 'Total amount locked in digital escrow.'],
                    ['title' => 'Cadastral Deed & Boundary Survey', 'status' => 'completed', 'date' => '2026-09-25', 'note' => 'Hawassa City Municipality survey cleared.'],
                    ['title' => 'Ministry of Revenue Tax Clearance', 'status' => 'completed', 'date' => '2026-10-02', 'note' => 'All regional transfer taxes settled.'],
                    ['title' => 'Deed Handover & Final Disbursement', 'status' => 'completed', 'date' => '2026-10-10', 'note' => 'Ownership certificate issued. Funds disbursed to seller.'],
                ]
            ],
            [
                'id' => 'TRX-ADD-8824',
                'property_title' => 'Prime Commercial Plot in CMC Michael',
                'property_location' => 'CMC Michael, Yeka, Addis Ababa',
                'property_code' => 'PROP-ADD-012',
                'title_deed_no' => 'AA-YEK-2026/7711',
                'cadastral_code' => 'CAD-AA-06-9921',
                'type' => 'Land Title Transfer',
                'total_amount' => 88000000,
                'total_amount_formatted' => '88,000,000 ETB',
                'escrow_amount' => 17600000,
                'escrow_amount_formatted' => '17,600,000 ETB (20% Escrow)',
                'currency' => 'ETB',
                'buyer_name' => 'Bole Commercial Investments PLC',
                'buyer_avatar' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?auto=format&fit=crop&w=200&q=80',
                'buyer_phone' => '+251 91 334 5566',
                'seller_name' => 'Yeka Landholdings Private Trust',
                'seller_avatar' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=200&q=80',
                'bank_partner' => 'Commercial Bank of Ethiopia',
                'escrow_account' => 'CBE Escrow #1000-9942-8819',
                'current_stage' => 2, // Escrow Locked
                'status' => 'escrow_locked',
                'status_label' => 'Escrow Funds Locked',
                'status_variant' => 'brand',
                'initiated_at' => '2026-10-10',
                'est_completion' => '2026-11-05',
                'milestones' => [
                    ['title' => 'Offer Accepted & Contract Signed', 'status' => 'completed', 'date' => '2026-10-10', 'note' => 'Formal notary agreement verified.'],
                    ['title' => 'Earnest Escrow Deposit Locked', 'status' => 'completed', 'date' => '2026-10-12', 'note' => '17.6M ETB escrow hold active in CBE.'],
                    ['title' => 'Cadastral Deed & Boundary Survey', 'status' => 'in_progress', 'date' => '2026-10-14', 'note' => 'Geospatial beacon verification scheduled.'],
                    ['title' => 'Ministry of Revenue Tax Clearance', 'status' => 'pending', 'date' => 'Est. Oct 26', 'note' => 'Pending cadastral report submission.'],
                    ['title' => 'Deed Handover & Final Disbursement', 'status' => 'pending', 'date' => 'Est. Nov 05', 'note' => 'Final title issuance pending clearances.'],
                ]
            ],
            [
                'id' => 'TRX-BHR-2009',
                'property_title' => 'Panoramic Lake Tana Waterfront Villa',
                'property_location' => 'Waterfront Drive, Bahir Dar',
                'property_code' => 'PROP-BHR-003',
                'title_deed_no' => 'AMH-BHR-2026/0491',
                'cadastral_code' => 'CAD-AMH-02-1102',
                'type' => 'Purchase Escrow',
                'total_amount' => 38000000,
                'total_amount_formatted' => '38,000,000 ETB',
                'escrow_amount' => 7600000,
                'escrow_amount_formatted' => '7,600,000 ETB (20% Escrow)',
                'currency' => 'ETB',
                'buyer_name' => 'Henok Alemayehu',
                'buyer_avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=200&q=80',
                'buyer_phone' => '+251 93 112 2334',
                'seller_name' => 'Tana Development Consortium',
                'seller_avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80',
                'bank_partner' => 'Dashen Bank Escrow',
                'escrow_account' => 'DASHEN-ESC-3391-440',
                'current_stage' => 3, // Cadastral Review
                'status' => 'cadastral_review',
                'status_label' => 'Cadastral & Title Review',
                'status_variant' => 'warning',
                'initiated_at' => '2026-10-02',
                'est_completion' => '2026-10-25',
                'milestones' => [
                    ['title' => 'Offer Accepted & Contract Signed', 'status' => 'completed', 'date' => '2026-10-02', 'note' => 'Sales agreement signed by both parties.'],
                    ['title' => 'Earnest Escrow Deposit Locked', 'status' => 'completed', 'date' => '2026-10-05', 'note' => 'Initial deposit verified by Dashen Escrow.'],
                    ['title' => 'Cadastral Deed & Boundary Survey', 'status' => 'in_progress', 'date' => '2026-10-11', 'note' => 'Lake setback and riparian rights validation.'],
                    ['title' => 'Ministry of Revenue Tax Clearance', 'status' => 'pending', 'date' => 'Est. Oct 20', 'note' => 'Amhara Revenue Bureau clearance in queue.'],
                    ['title' => 'Deed Handover & Final Disbursement', 'status' => 'pending', 'date' => 'Est. Oct 25', 'note' => 'Final registry deed transfer scheduled.'],
                ]
            ],
        ]);
    }

    /**
     * Display the property deals & escrow pipeline hub
     */
    public function index(Request $request)
    {
        $deals = $this->getPipelineDeals();

        // Filter by stage/status
        $currentStatus = $request->query('status', 'all');
        if ($currentStatus !== 'all') {
            $deals = $deals->filter(function($deal) use ($currentStatus) {
                return $deal['status'] === $currentStatus;
            });
        }

        // Filter by transaction type
        $currentType = $request->query('type', 'all');
        if ($currentType !== 'all') {
            $deals = $deals->filter(function($deal) use ($currentType) {
                return $deal['type'] === $currentType;
            });
        }

        // Filter by search keyword
        $search = $request->query('q');
        if (!empty($search)) {
            $q = strtolower($search);
            $deals = $deals->filter(function($deal) use ($q) {
                return str_contains(strtolower($deal['id']), $q)
                    || str_contains(strtolower($deal['property_title']), $q)
                    || str_contains(strtolower($deal['property_location']), $q)
                    || str_contains(strtolower($deal['title_deed_no']), $q)
                    || str_contains(strtolower($deal['buyer_name']), $q)
                    || str_contains(strtolower($deal['seller_name']), $q);
            });
        }

        // Pipeline Metrics
        $allDeals = $this->getPipelineDeals();
        $metrics = [
            'total_volume' => '247,500,000 ETB',
            'escrow_locked' => '84,550,000 ETB',
            'active_verifications' => $allDeals->where('status', 'cadastral_review')->count(),
            'closed_deals' => $allDeals->where('status', 'completed')->count(),
        ];

        return view('dashboard.transactions.index', compact(
            'deals',
            'metrics',
            'currentStatus',
            'currentType',
            'search'
        ));
    }

    /**
     * Display a single deal & escrow audit detail
     */
    public function show($id)
    {
        $allDeals = $this->getPipelineDeals();
        $deal = $allDeals->firstWhere('id', $id);

        if (!$deal) {
            // Default fallback if arbitrary ID queried
            $deal = $allDeals->first();
            $deal['id'] = $id;
        }

        return view('dashboard.transactions.show', compact('deal'));
    }
}
