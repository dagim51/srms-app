<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServicesWebController extends Controller
{
    public function publicIndex(Request $request)
    {
        $categories = ['Legal & Notary', 'Surveyors', 'Architects', 'Valuers', 'Contractors', 'Inspectors'];
        
        $professionals = collect([
            [
                'id' => 'PRO-001',
                'name' => 'Addis Legal Associates',
                'category' => 'Legal & Notary',
                'rating' => 4.9,
                'reviews' => 128,
                'rate' => '2,500 ETB/hr',
                'location' => 'Bole, Addis Ababa',
                'avatar' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=256&q=80',
                'specialty' => 'Title deed verification, court representation & escrow contracts',
                'verified' => true,
            ],
            [
                'id' => 'PRO-002',
                'name' => 'Ethio Surveying Experts',
                'category' => 'Surveyors',
                'rating' => 4.7,
                'reviews' => 45,
                'rate' => '15,000 ETB/project',
                'location' => 'Kazanchis, Addis Ababa',
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=256&q=80',
                'specialty' => 'Cadastral boundary surveying, topographic mapping & GPS demarcations',
                'verified' => true,
            ],
            [
                'id' => 'PRO-003',
                'name' => 'Bole Architecture Group',
                'category' => 'Architects',
                'rating' => 4.8,
                'reviews' => 82,
                'rate' => 'Custom Quote',
                'location' => 'Bole Atlas, Addis Ababa',
                'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=256&q=80',
                'specialty' => 'Luxury residential architecture, interior design & municipal permits',
                'verified' => true,
            ],
            [
                'id' => 'PRO-004',
                'name' => 'Abyssinia Property Valuers',
                'category' => 'Valuers',
                'rating' => 4.9,
                'reviews' => 96,
                'rate' => '8,000 ETB/report',
                'location' => 'Old Airport, Addis Ababa',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=256&q=80',
                'specialty' => 'Bank loan collateral appraisal, probate valuations & fair market audits',
                'verified' => true,
            ],
            [
                'id' => 'PRO-005',
                'name' => 'Sheger Structural Inspectors',
                'category' => 'Inspectors',
                'rating' => 4.8,
                'reviews' => 64,
                'rate' => '12,000 ETB/inspection',
                'location' => 'CMC, Addis Ababa',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=256&q=80',
                'specialty' => 'Pre-purchase structural audits, foundation checks & plumbing/electrical inspection',
                'verified' => true,
            ],
        ]);

        if ($request->filled('category') && $request->category !== 'all') {
            $professionals = $professionals->where('category', $request->input('category'));
        }

        if ($request->filled('q')) {
            $q = strtolower(trim($request->input('q')));
            $professionals = $professionals->filter(function($p) use ($q) {
                return str_contains(strtolower($p['name']), $q) 
                    || str_contains(strtolower($p['category']), $q)
                    || str_contains(strtolower($p['specialty']), $q);
            });
        }

        return view('frontend.services.public_index', [
            'categories' => $categories,
            'professionals' => $professionals->values(),
            'currentCategory' => $request->input('category', 'all'),
        ]);
    }

    public function publicShow($id)
    {
        $providers = [
            'PRO-001' => [
                'id' => 'PRO-001',
                'name' => 'Addis Legal Associates',
                'category' => 'Legal & Notary',
                'rating' => 4.9,
                'reviews' => 128,
                'rate' => '2,500 ETB/hr',
                'location' => 'Bole, Addis Ababa',
                'avatar' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=256&q=80',
                'about' => 'Certified real estate lawyers specializing in property transfers, contract drafting, title authenticity verification, and escrow settlement resolution across Ethiopia.',
                'services' => [
                    ['name' => 'Title Deed Verification', 'price' => '5,000 ETB', 'duration' => '3 Days', 'desc' => 'Comprehensive land bureau registry record search to guarantee clean ownership without injunctions or liens.'],
                    ['name' => 'Contract Review & Drafting', 'price' => '3,500 ETB', 'duration' => '1 Day', 'desc' => 'Bilingual Amharic/English sales or lease agreements protecting buyer/seller obligations.'],
                    ['name' => 'Full Escrow Transfer Service', 'price' => '1% of Value', 'duration' => 'Variable', 'desc' => 'Bank-backed escrow management until final title document registration and keys handover.']
                ]
            ],
            'PRO-002' => [
                'id' => 'PRO-002',
                'name' => 'Ethio Surveying Experts',
                'category' => 'Surveyors',
                'rating' => 4.7,
                'reviews' => 45,
                'rate' => '15,000 ETB/project',
                'location' => 'Kazanchis, Addis Ababa',
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=256&q=80',
                'about' => 'Licensed geodetic surveyors with state-of-the-art GNSS/RTK instruments, delivering certified boundary demarcations and topographic cadastral maps.',
                'services' => [
                    ['name' => 'Cadastral Boundary Survey', 'price' => '15,000 ETB', 'duration' => '2 Days', 'desc' => 'Precise GPS boundary alignment preventing encroachment disputes with neighbors.'],
                    ['name' => 'Topographic Site Plan', 'price' => '22,000 ETB', 'duration' => '4 Days', 'desc' => 'Contour lines, elevation levels, and municipal submission formats.'],
                ]
            ],
            'PRO-003' => [
                'id' => 'PRO-003',
                'name' => 'Bole Architecture Group',
                'category' => 'Architects',
                'rating' => 4.8,
                'reviews' => 82,
                'rate' => 'Custom Quote',
                'location' => 'Bole Atlas, Addis Ababa',
                'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=256&q=80',
                'about' => 'Award-winning architecture studio specializing in modern residences, commercial complexes, and municipal building permit processing.',
                'services' => [
                    ['name' => 'Residential Architectural Design', 'price' => 'Custom Quote', 'duration' => '3-6 Weeks', 'desc' => 'Full 3D visualization, BIM schematics, and structural engineering documentation.'],
                    ['name' => 'Building Permit Expediting', 'price' => '25,000 ETB', 'duration' => '2 Weeks', 'desc' => 'Submission coordination with Addis Ababa Construction Bureau.'],
                ]
            ]
        ];

        $provider = $providers[$id] ?? [
            'id' => $id,
            'name' => 'Addis Real Estate Professional',
            'category' => 'Professional Services',
            'rating' => 4.8,
            'reviews' => 50,
            'rate' => 'Negotiable',
            'location' => 'Addis Ababa, Ethiopia',
            'avatar' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=256&q=80',
            'about' => 'Verified professional service provider registered on the SRMS Ethiopian Property Platform.',
            'services' => [
                ['name' => 'General Advisory Consultation', 'price' => '2,000 ETB', 'duration' => '1 Hour', 'desc' => 'Direct expert consultation regarding property acquisition, laws, or valuation.']
            ]
        ];

        return view('frontend.services.public_show', compact('provider'));
    }

    public function index(Request $request)
    {
        $categories = ['Legal & Notary', 'Surveyors', 'Architects', 'Valuers', 'Contractors', 'Inspectors'];
        
        $professionals = collect([
            ['id' => 'PRO-001', 'name' => 'Addis Legal Associates', 'category' => 'Legal & Notary', 'rating' => 4.9, 'reviews' => 128, 'rate' => '2,500 ETB/hr'],
            ['id' => 'PRO-002', 'name' => 'Ethio Surveying Experts', 'category' => 'Surveyors', 'rating' => 4.7, 'reviews' => 45, 'rate' => '15,000 ETB/project'],
            ['id' => 'PRO-003', 'name' => 'Bole Architecture Group', 'category' => 'Architects', 'rating' => 4.8, 'reviews' => 82, 'rate' => 'Custom Quote'],
        ]);

        if ($request->has('category')) {
            $professionals = $professionals->where('category', $request->input('category'));
        }

        return view('frontend.services.index', ['categories' => $categories, 'professionals' => $professionals->values()]);
    }

    public function show($id)
    {
        $provider = [
            'id' => $id,
            'name' => 'Addis Legal Associates',
            'category' => 'Legal & Notary',
            'rating' => 4.9,
            'reviews' => 128,
            'rate' => '2,500 ETB/hr',
            'about' => 'Certified real estate lawyers specializing in property transfers, contract drafting, and dispute resolution.',
            'services' => [
                ['name' => 'Title Deed Verification', 'price' => '5,000 ETB', 'duration' => '3 Days'],
                ['name' => 'Contract Review', 'price' => '3,000 ETB', 'duration' => '1 Day'],
                ['name' => 'Full Transfer Escrow', 'price' => '1% of Property Value', 'duration' => 'Variable']
            ]
        ];

        return view('frontend.services.show', compact('provider'));
    }

    public function dashboard()
    {
        $requests = [
            ['id' => 'REQ-101', 'client' => 'Abebe Kebede', 'service' => 'Title Deed Verification', 'date' => 'Oct 12, 2026', 'status' => 'pending'],
            ['id' => 'REQ-102', 'client' => 'Chaltu Tadesse', 'service' => 'Contract Review', 'date' => 'Oct 10, 2026', 'status' => 'in-progress'],
            ['id' => 'REQ-103', 'client' => 'Dawit Ashenafi', 'service' => 'Full Transfer Escrow', 'date' => 'Oct 05, 2026', 'status' => 'completed'],
        ];

        $stats = [
            'active_requests' => 5,
            'completed_jobs' => 128,
            'rating' => 4.9,
            'monthly_earnings' => '145,000 ETB'
        ];

        return view('frontend.services.dashboard', compact('requests', 'stats'));
    }
}
