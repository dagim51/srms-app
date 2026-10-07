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

