<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeveloperProjectWebController extends Controller
{
    protected $projects = [
        [
            'id' => 1,
            'name' => 'Skyline Towers',
            'developer' => 'Addis Developers PLC',
            'status' => 'under_construction',
            'location' => 'Bole, Addis Ababa',
            'completion_date' => '2027-12-01',
            'total_units' => 120,
            'sold_units' => 45,
            'progress' => 35,
            'image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&q=80',
        ],
        [
            'id' => 2,
            'name' => 'Green Valley Estate',
            'developer' => 'Addis Developers PLC',
            'status' => 'planning',
            'location' => 'CMC, Addis Ababa',
            'completion_date' => '2028-06-01',
            'total_units' => 250,
            'sold_units' => 10,
            'progress' => 5,
            'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80',
        ]
    ];

    public function publicIndex(Request $request)
    {
        $status = $request->query('status');
        $projects = collect($this->projects);
        
        if ($status && $status !== 'all') {
            $projects = $projects->where('status', $status);
        }

        return view('frontend.developer.projects.public_index', [
            'projects' => $projects->values(),
            'currentStatus' => $status ?? 'all',
        ]);
    }

    public function index()
    {
        $projects = $this->projects;
        return view('frontend.developer.projects.index', compact('projects'));
    }

    public function show($id)
    {
        $project = collect($this->projects)->firstWhere('id', (int) $id);
        if (!$project) abort(404);

        return view('frontend.developer.projects.show', compact('project'));
    }

    public function buildings($id)
    {
        $project = collect($this->projects)->firstWhere('id', (int) $id);
        if (!$project) abort(404);

        $buildings = [
            ['id' => 1, 'name' => 'Tower A', 'floors' => 15, 'units' => 60, 'status' => 'under_construction', 'progress' => 40],
            ['id' => 2, 'name' => 'Tower B', 'floors' => 15, 'units' => 60, 'status' => 'foundation', 'progress' => 15],
        ];

        return view('frontend.developer.projects.buildings', compact('project', 'buildings'));
    }

    public function units($id)
    {
        $project = collect($this->projects)->firstWhere('id', (int) $id);
        if (!$project) abort(404);

        $units = [
            ['id' => 101, 'number' => 'A-101', 'type' => '2 Bedroom', 'floor' => 1, 'price' => '8,500,000 ETB', 'status' => 'available'],
            ['id' => 102, 'number' => 'A-102', 'type' => '3 Bedroom', 'floor' => 1, 'price' => '12,000,000 ETB', 'status' => 'sold'],
            ['id' => 201, 'number' => 'A-201', 'type' => '2 Bedroom', 'floor' => 2, 'price' => '8,600,000 ETB', 'status' => 'reserved'],
            ['id' => 202, 'number' => 'A-202', 'type' => '3 Bedroom', 'floor' => 2, 'price' => '12,200,000 ETB', 'status' => 'available'],
        ];

        return view('frontend.developer.projects.units', compact('project', 'units'));
    }

    public function progress($id)
    {
        $project = collect($this->projects)->firstWhere('id', (int) $id);
        if (!$project) abort(404);

        $milestones = [
            ['date' => '2026-09-15', 'title' => 'Foundation Completed for Tower A', 'status' => 'completed', 'percentage' => 15],
            ['date' => '2026-10-05', 'title' => 'First Floor Slab Poured (Tower A)', 'status' => 'completed', 'percentage' => 25],
            ['date' => '2026-11-20', 'title' => 'Structural Framing Reaches 5th Floor', 'status' => 'in_progress', 'percentage' => 35],
            ['date' => '2027-04-10', 'title' => 'Exterior Enclosure Completed', 'status' => 'planned', 'percentage' => 70],
        ];

        return view('frontend.developer.projects.progress', compact('project', 'milestones'));
    }

    public function documents($id)
    {
        $project = collect($this->projects)->firstWhere('id', (int) $id);
        if (!$project) abort(404);

        $documents = [
            ['name' => 'Project Brochure', 'type' => 'PDF', 'size' => '4.2 MB', 'uploaded_by' => 'Marketing Team', 'date' => '2026-08-01'],
            ['name' => 'Floor Plans - Tower A', 'type' => 'PDF', 'size' => '12.5 MB', 'uploaded_by' => 'Engineering', 'date' => '2026-08-10'],
            ['name' => 'Master Plan', 'type' => 'DWG', 'size' => '28 MB', 'uploaded_by' => 'Architecture', 'date' => '2026-07-15'],
        ];

        return view('frontend.developer.projects.documents', compact('project', 'documents'));
    }

    // Public view
    public function publicShow($id)
    {
        $project = collect($this->projects)->firstWhere('id', (int) $id);
        if (!$project) abort(404);

        return view('frontend.developer.projects.public_show', compact('project'));
    }
}
