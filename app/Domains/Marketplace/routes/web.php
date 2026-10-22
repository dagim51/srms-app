<?php

use App\Domains\Marketplace\Http\Controllers\Web;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard/Marketplace')
    ->middleware(['auth', 'verified', 'org.scope'])
    ->group(function () {
        // Add your Web routes here
        // Route::resource('resource', Web\ResourceController::class);
    });
