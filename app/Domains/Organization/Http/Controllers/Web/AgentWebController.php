<?php

namespace App\Domains\Organization\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class AgentWebController extends Controller
{
    public function show($id)
    {
        // Mock data for public agent profile
        $agent = [
            'id' => $id,
            'name' => 'Meron Alemu',
            'title' => 'Senior Real Estate Agent',
            'organization' => 'Addis Estates Ltd.',
            'phone' => '+251 91 112 3344',
            'email' => 'meron@addisestates.com',
            'rating' => 4.8,
            'reviews_count' => 24,
            'bio' => 'With over 5 years of experience in the Addis Ababa real estate market, I specialize in luxury residential properties and commercial leasing.',
            'service_areas' => ['Bole', 'Kazanchis', 'Sarbet', 'Old Airport'],
        ];

        $listings = [
            ['id' => 101, 'title' => 'Modern Apartment in Bole', 'price' => '45,000 ETB/mo', 'type' => 'Rent', 'status' => 'Available'],
            ['id' => 102, 'title' => 'Luxury Villa in Old Airport', 'price' => '15,000,000 ETB', 'type' => 'Sale', 'status' => 'Available'],
            ['id' => 103, 'title' => 'Commercial Space Kazanchis', 'price' => '120,000 ETB/mo', 'type' => 'Rent', 'status' => 'Under Offer'],
        ];

        return view('frontend.agents.show', compact('agent', 'listings'));
    }
}
