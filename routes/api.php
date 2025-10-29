<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware(['auth:sanctum'])->group(function () {
Route::prefix('article')->name('article.')->group(function () {
    Route::get('/', [ArticleController::class, 'CartByUserJson']);
});

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'cartByUserJson'])->name('cartGetAllJson');
    Route::delete('/deleteById', [CartController::class, 'deleteCartById'])->name('cartDeleteById');
    Route::put('/update-qty', [CartController::class, 'updateQtyCartByIdJson'])->name('cartQtyUpdateById');
});


Route::prefix('user')->name('user.')->group(function () {
    Route::post('/create-user', [AuthController::class, 'createUser'])->name('createUser');
});

// });
