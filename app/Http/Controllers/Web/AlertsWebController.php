<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AlertsWebController extends Controller
{
    public function index()
    {
        $alerts = [
            [
                'id' => 'ALT-101', 
                'name' => 'Bole 2-Bed Apartments', 
                'type' => 'Saved Search',
                'criteria' => 'Bole • 2+ Beds • Under 40,000 ETB', 
                'status' => 'active', 
                'frequency' => 'Instant',
                'results' => 12
            ],
            [
                'id' => 'ALT-102', 
                'name' => 'Commercial Spaces Kazanchis', 
                'type' => 'Price Alert',
                'criteria' => 'Kazanchis • Office • Price Drops', 
                'status' => 'paused', 
                'frequency' => 'Daily',
                'results' => 4
            ],
        ];

        return view('frontend.alerts.index', compact('alerts'));
    }

    public function show($id)
    {
        $alert = [
            'id' => $id,
            'name' => 'Bole 2-Bed Apartments',
            'type' => 'Saved Search',
            'location' => 'Bole, Addis Ababa',
            'property_type' => 'Apartment',
            'min_beds' => 2,
            'max_price' => 40000,
            'status' => 'active',
            'frequency' => 'Instant',
            'email_notify' => true,
            'push_notify' => true,
        ];

        return view('frontend.alerts.show', compact('alert'));
    }

    public function recommendations()
    {
        $recommendations = [
            [
                'id' => 'REC-001',
                'title' => 'Luxury 2BHK in Bole Medhanialem',
                'price' => '35,000 ETB/mo',
                'match_score' => 98,
                'reason' => 'Matches your saved search "Bole 2-Bed Apartments"',
                'image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&q=80&w=400&h=300'
            ],
            [
                'id' => 'REC-002',
                'title' => 'Modern Office Space Kazanchis',
                'price' => '85,000 ETB/mo',
                'match_score' => 85,
                'reason' => 'Price recently dropped by 10%',
                'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=400&h=300'
            ],
            [
                'id' => 'REC-003',
                'title' => 'Cozy Studio near CMC',
                'price' => '15,000 ETB/mo',
                'match_score' => 75,
                'reason' => 'Popular among users looking in your price range',
                'image' => 'https://images.unsplash.com/photo-1502672260266-1c1e52d1590c?auto=format&fit=crop&q=80&w=400&h=300'
            ]
        ];

        return view('frontend.alerts.recommendations', compact('recommendations'));
    }
}
