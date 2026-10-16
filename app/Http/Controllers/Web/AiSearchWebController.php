<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AiSearchWebController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $parsed = null;
        $results = [];

        if ($query) {
            // Mock AI parsing logic
            $parsed = [
                'type' => 'Villa',
                'bedrooms' => 4,
                'location' => 'Harar',
                'max_price' => '15,000,000 ETB',
                'features' => ['Parking']
            ];

            // Mock results based on the parsed query
            $results = [
                ['id' => 'V1', 'title' => 'Harar Hills 4-Bed Villa', 'price' => '12,500,000 ETB', 'location' => 'Harar, Old Town', 'features' => '4 Bed • 3 Bath • Parking'],
                ['id' => 'V2', 'title' => 'Modern Villa with Garden', 'price' => '14,800,000 ETB', 'location' => 'Harar, New District', 'features' => '4 Bed • 4 Bath • Parking'],
            ];
        }

        return view('frontend.ai.search', compact('query', 'parsed', 'results'));
    }

    public function assistant(Request $request)
    {
        $draft = null;

        if ($request->isMethod('post')) {
            $input = $request->input('features');
            // Mock AI generation
            $draft = "Welcome to this exceptional property featuring " . htmlspecialchars($input) . ". \n\nThis stunning home offers the perfect blend of modern comfort and elegant design. The spacious living areas are flooded with natural light, complementing the premium finishes throughout. Situated in a highly desirable neighborhood, it provides both convenience and tranquility.\n\nDon't miss the opportunity to make this your dream home. Contact us today for a viewing!";
        }

        return view('frontend.ai.assistant', compact('draft'));
    }
}
