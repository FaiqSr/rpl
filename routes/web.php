<?php

use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function () {
        // TODO: Implement login logic
        return redirect()->route('dashboard');
    })->name('login.post');

    Route::post('/logout', function () {
        // TODO: Implement logout logic
        return redirect()->route('home');
    })->name('logout');
});

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard Routes
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // User Profile Routes
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
});

// Content Routes
Route::prefix('content')->group(function () {
    Route::get('/articles', function () {
        return view('articles');
    })->name('articles');

    Route::get('/marketplace', function () {
        return view('marketplace');
    })->name('marketplace');
});
