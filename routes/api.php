<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is running cleanly.',
        'environment' => config('app.env'),
        'timestamp' => now()->toIso8601String(),
    ]);
});
