<?php

use App\Domains\Property\Http\Controllers\Web;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard/Property')
    ->middleware(['auth', 'verified', 'org.scope'])
    ->group(function () {
        // Add your Web routes here
        // Route::resource('resource', Web\ResourceController::class);
    });
