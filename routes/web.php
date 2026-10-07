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
    $properties = \App\Models\Property::with(['media'])->latest()->paginate(10);
    return view('dashboard.properties.index', compact('properties'));
});
Route::get('/dashboard/properties/create', function () {
    return view('dashboard.properties.create');
});


// Phase 11: Listing Core UI
Route::get('/dashboard/listings', [\App\Http\Controllers\Web\ListingController::class, 'index'])->name('dashboard.listings.index');
Route::get('/dashboard/listings/create', [\App\Http\Controllers\Web\ListingController::class, 'create'])->name('dashboard.listings.create');
Route::post('/dashboard/listings', [\App\Http\Controllers\Web\ListingController::class, 'store'])->name('dashboard.listings.store');
Route::get('/dashboard/listings/{listing}/edit', [\App\Http\Controllers\Web\ListingController::class, 'edit'])->name('dashboard.listings.edit');
Route::put('/dashboard/listings/{listing}', [\App\Http\Controllers\Web\ListingController::class, 'update'])->name('dashboard.listings.update');
Route::patch('/dashboard/listings/{listing}/toggle-status', [\App\Http\Controllers\Web\ListingController::class, 'toggleStatus'])->name('dashboard.listings.toggle-status');
Route::delete('/dashboard/listings/{listing}', [\App\Http\Controllers\Web\ListingController::class, 'destroy'])->name('dashboard.listings.destroy');
Route::get('/dashboard/listings/{listing}/preview', [\App\Http\Controllers\Web\ListingController::class, 'preview'])->name('dashboard.listings.preview');

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

// Phase 09: Property Media UI
Route::get('/dashboard/properties/media', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'index'])->name('dashboard.properties.media.demo');
Route::get('/dashboard/properties/{property}/media', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'index'])->name('dashboard.properties.media');
Route::post('/dashboard/properties/{property}/media', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'store'])->name('dashboard.properties.media.store');
Route::put('/dashboard/properties/{property}/media/order', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'updateOrder'])->name('dashboard.properties.media.order');
Route::patch('/dashboard/properties/{property}/media/{media}/set-primary', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'setPrimary'])->name('dashboard.properties.media.primary');
Route::delete('/dashboard/properties/{property}/media/{media}', [\App\Http\Controllers\Web\PropertyMediaWebController::class, 'destroy'])->name('dashboard.properties.media.destroy');

// Phase 10: GIS Foundation UI
Route::get('/dashboard/properties/map', function () {
    return view('dashboard.properties.map');
})->name('dashboard.properties.map');

