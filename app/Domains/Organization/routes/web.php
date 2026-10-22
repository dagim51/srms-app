<?php

use App\Domains\Organization\Http\Controllers\Web;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard/Organization')
    ->middleware(['auth', 'verified', 'org.scope'])
    ->group(function () {
        // Add your Web routes here
        // Route::resource('resource', Web\ResourceController::class);
    });
