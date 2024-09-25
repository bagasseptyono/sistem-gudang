<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\MutationController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::apiResource('items', ItemController::class);
        Route::get('items/{id}/mutations', [ItemController::class, 'mutationHistory']);
        Route::apiResource('users', UserController::class);
        Route::get('users/{id}/mutations', [UserController::class, 'mutationHistory']);
        Route::apiResource('mutations', MutationController::class);
    });

});

// Route::middleware('auth:api')->group(function () {

// });
