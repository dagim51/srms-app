<?php

use Illuminate\Support\Facades\Route;

Route::prefix('GIS')
    ->middleware(['auth:sanctum', 'org.scope'])
    ->group(function () {
        // Add your API routes here
    });
