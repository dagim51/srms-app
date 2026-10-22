<?php

use App\Domains\GIS\Http\Controllers\Web;
use Illuminate\Support\Facades\Route;

Route::prefix('GIS')
    ->middleware(['web', 'auth'])
    ->group(function () {
        // Add your web routes here
    });
