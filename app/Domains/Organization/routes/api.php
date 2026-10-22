<?php

use App\Domains\Organization\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

Route::prefix('Organization')
    ->middleware(['auth:sanctum', 'org.scope'])
    ->group(function () {
        // Add your API routes here
        // Route::apiResource('resource', V1\ResourceController::class);
    });
