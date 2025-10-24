<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware(['auth:sanctum'])->group(function () {
Route::prefix('article')->name('article.')->group(function () {
    Route::get('/', [ArticleController::class, 'CartByUserJson']);
});

Route::prefix('cart')->name('cart')->group(function () {
    Route::get('/', [CartController::class, 'CartByUserJson'])->name('cartGetAllJson');
});
// });
