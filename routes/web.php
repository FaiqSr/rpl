<?php

use Illuminate\Support\Facades\Route;

// Public Routes - Semua bisa diakses tanpa login
Route::get('/', function () {
    return view('store.home');
})->name('home');

// Authentication Pages (hanya tampilan, tidak ada proses backend)
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Dashboard (bisa langsung diakses tanpa login)
Route::get('/dashboard', function () {
    return view('dashboard.seller');
})->name('dashboard');

Route::get('/dashboard/products', function () {
    return view('dashboard.products');
})->name('dashboard.products');

Route::get('/dashboard/tools', function () {
    return view('dashboard.tools');
})->name('dashboard.tools');

Route::get('/dashboard/tools/monitoring', function () {
    return view('dashboard.tools-monitoring');
})->name('dashboard.tools.monitoring');

Route::get('/dashboard/sales', function () {
    return view('dashboard.sales');
})->name('dashboard.sales');

Route::get('/dashboard/chat', function () {
    return view('dashboard.chat');
})->name('dashboard.chat');

// Other Pages
Route::get('/articles', function () {
    return view('articles');
})->name('articles');

Route::get('/marketplace', function () {
    return view('marketplace');
})->name('marketplace');

Route::get('/profile', function () {
    return view('profile');
})->name('profile');
