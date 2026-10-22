<?php

use App\Domains\Listing\Http\Controllers\Web;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard/Listing')
    ->middleware(['auth', 'verified', 'org.scope'])
    ->group(function () {
        // Add your Web routes here
        // Route::resource('resource', Web\ResourceController::class);
    });
