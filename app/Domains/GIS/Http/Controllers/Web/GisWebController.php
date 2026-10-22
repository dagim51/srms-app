<?php

namespace App\Domains\GIS\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class GisWebController extends Controller
{
    public function index()
    {
        return view('dashboard.properties.map');
    }
}
