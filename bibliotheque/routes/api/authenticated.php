<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReferenceController;
use App\Http\Controllers\Api\DepositRequestController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PublisherController;
use App\Http\Controllers\Api\DocumentTypeController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\ViewController;

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Notifications (personnelles)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Téléchargement de fichier (tout utilisateur connecté)
    Route::get('/references/{reference}/download', [ReferenceController::class, 'download']);

    // Références (tout utilisateur connecté peut proposer)
    Route::post('/references', [ReferenceController::class, 'store']);

    // Dépôt (tout utilisateur connecté)
    Route::get('/deposit-requests', [DepositRequestController::class, 'index']);
    Route::get('/deposit-requests/{depositRequest}', [DepositRequestController::class, 'show']);
    Route::get('/deposit-requests/{depositRequest}/download', [DepositRequestController::class, 'downloadFile']);
    Route::apiResource('deposit-requests', DepositRequestController::class)->except(['index', 'show']);

    // Profil utilisateur (tout utilisateur connecté)
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    // Création rapide de catégories, éditeurs et types de document (tout utilisateur connecté)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/publishers', [PublisherController::class, 'store']);
    Route::post('/document-types', [DocumentTypeController::class, 'store']);

    // Statistiques (admin et RH)
    Route::get('/downloads/stats', [DownloadController::class, 'stats']);
    Route::get('/views/stats', [ViewController::class, 'stats']);
});
