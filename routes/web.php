<?php

use Illuminate\Support\Facades\Route;

// Public Landing
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Auth pages (stubs - will connect to Laravel auth in Phase 04 frontend)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Dashboard stub
Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');


Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::get('/reset-password/{token}', function (\Illuminate\Http\Request $request, $token) {
    return view('auth.reset-password', ['request' => $request]);
})->name('password.reset');


Route::get('/403', function () {
    return view('errors.403');
});


Route::get('/dashboard/properties', function () {
    return view('dashboard.properties.index');
});
Route::get('/dashboard/properties/create', function () {
    return view('dashboard.properties.create');
});


Route::get('/dashboard/listings', function () {
    return view('dashboard.listings.index');
});
Route::get('/dashboard/listings/create', function () {
    return view('dashboard.listings.create');
});

// Phase 08: Property Features & Amenities Catalog Management
Route::get('/dashboard/amenities', [\App\Http\Controllers\Web\AmenityCatalogController::class, 'index'])->name('dashboard.amenities.index');
Route::post('/dashboard/amenities', [\App\Http\Controllers\Web\AmenityCatalogController::class, 'store'])->name('dashboard.amenities.store');
Route::put('/dashboard/amenities/{amenity}', [\App\Http\Controllers\Web\AmenityCatalogController::class, 'update'])->name('dashboard.amenities.update');
Route::patch('/dashboard/amenities/{amenity}/toggle-status', [\App\Http\Controllers\Web\AmenityCatalogController::class, 'toggleStatus'])->name('dashboard.amenities.toggle');
Route::delete('/dashboard/amenities/{amenity}', [\App\Http\Controllers\Web\AmenityCatalogController::class, 'destroy'])->name('dashboard.amenities.destroy');

Route::get('/dashboard/features', [\App\Http\Controllers\Web\FeatureCatalogController::class, 'index'])->name('dashboard.features.index');
Route::post('/dashboard/features', [\App\Http\Controllers\Web\FeatureCatalogController::class, 'store'])->name('dashboard.features.store');
Route::put('/dashboard/features/{feature}', [\App\Http\Controllers\Web\FeatureCatalogController::class, 'update'])->name('dashboard.features.update');
Route::patch('/dashboard/features/{feature}/toggle-status', [\App\Http\Controllers\Web\FeatureCatalogController::class, 'toggleStatus'])->name('dashboard.features.toggle');
Route::delete('/dashboard/features/{feature}', [\App\Http\Controllers\Web\FeatureCatalogController::class, 'destroy'])->name('dashboard.features.destroy');


