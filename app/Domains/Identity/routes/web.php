<?php

use App\Domains\Identity\Http\Controllers\Web;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard/Identity')
    ->middleware(['auth', 'verified', 'org.scope'])
    ->group(function () {
        // Add your Web routes here
        // Route::resource('resource', Web\ResourceController::class);
    });
