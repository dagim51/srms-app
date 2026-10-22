<?php

use App\Domains\Location\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

Route::prefix('Location')
    ->middleware(['auth:sanctum', 'org.scope'])
    ->group(function () {
        // Add your API routes here
        // Route::apiResource('resource', V1\ResourceController::class);
    });
