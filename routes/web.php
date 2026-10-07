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

