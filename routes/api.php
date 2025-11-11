<?php

use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\PlacesController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('v1')
    ->group(function () {

        Route::post('auth/login', [UserController::class, 'authenticate']);
        Route::post('auth/logout', [UserController::class, 'logout']);
        Route::post('auth/password_request', [UserController::class, 'passwordRequest']);
        Route::post('auth/change_password', [UserController::class, 'confirmPasswordRequest']);

        Route::middleware(['jwt.verify'])->group(function () {
            Route::get('users/me', [UserController::class, 'me']);
            Route::put('users/me', [UserController::class, 'updateUser']);
            Route::put('users/password', [UserController::class, 'changePassword']);
        });



        Route::get('places', [PlacesController::class, 'getAll']);
        Route::post('orders', [OrdersController::class, 'store']);
        Route::get('orders/{id}', [OrdersController::class, 'getById']);
    });
