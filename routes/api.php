<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:login');

    // Di luar auth:api, karena token yang sudah kedaluwarsa
    // masih boleh diperbarui selama belum lewat JWT_REFRESH_TTL
    Route::post('refresh', [AuthController::class, 'refresh'])
        ->middleware('throttle:10,1');

    Route::middleware(['auth:api', 'active'])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});