<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepositRequestController;

Route::middleware(['auth:sanctum', 'responsable.demande'])->group(function () {
    Route::post('/deposit-requests/{depositRequest}/approve-manager', [DepositRequestController::class, 'approveByManager']);
    Route::post('/deposit-requests/{depositRequest}/reject-manager', [DepositRequestController::class, 'rejectByManager']);
});
