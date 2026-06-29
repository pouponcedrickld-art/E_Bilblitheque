<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SuspensionRequestController;

Route::middleware(['auth:sanctum', 'responsable.rh'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::post('/users/{user}/activate', [UserController::class, 'activate']);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    Route::get('/suspension-requests', [SuspensionRequestController::class, 'index']);
    Route::post('/suspension-requests', [SuspensionRequestController::class, 'store']);
});
