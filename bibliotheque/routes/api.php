<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\PublisherController;
use App\Http\Controllers\Api\ReferenceController;
use App\Http\Controllers\Api\DepositRequestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\ViewController;

// Routes publiques (accessibles sans authentification)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/{author}', [AuthorController::class, 'show']);
Route::get('/publishers', [PublisherController::class, 'index']);
Route::get('/publishers/{publisher}', [PublisherController::class, 'show']);
Route::get('/references', [ReferenceController::class, 'index']);
Route::get('/references/{reference}', [ReferenceController::class, 'show']);
Route::get('/references/{reference}/read', [ReferenceController::class, 'read']);

// Routes protégées par session Sanctum
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
    Route::put('/references/{reference}', [ReferenceController::class, 'update']);
    Route::delete('/references/{reference}', [ReferenceController::class, 'destroy']);

    // Dépôt (tout utilisateur connecté)
    Route::apiResource('deposit-requests', DepositRequestController::class)->except(['index', 'show']);

    // --- Admin + Responsable Demande ---
    Route::middleware('responsable.demande')->group(function () {
        Route::get('/deposit-requests', [DepositRequestController::class, 'index']);
        Route::get('/deposit-requests/{depositRequest}', [DepositRequestController::class, 'show']);
        Route::post('/deposit-requests/{depositRequest}/approve-manager', [DepositRequestController::class, 'approveByManager']);
        Route::post('/deposit-requests/{depositRequest}/reject-manager', [DepositRequestController::class, 'rejectByManager']);
    });

    // --- Admin + Responsable RH ---
    Route::middleware('responsable.rh')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::post('/users/{user}/activate', [UserController::class, 'activate']);
        Route::post('/users/{user}/suspend', [UserController::class, 'suspend']);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    });
});

// --- Admin uniquement ---
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Gestion du catalogue (CRUD catégories, auteurs, éditeurs)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::post('/authors', [AuthorController::class, 'store']);
    Route::put('/authors/{author}', [AuthorController::class, 'update']);
    Route::delete('/authors/{author}', [AuthorController::class, 'destroy']);

    Route::post('/publishers', [PublisherController::class, 'store']);
    Route::put('/publishers/{publisher}', [PublisherController::class, 'update']);
    Route::delete('/publishers/{publisher}', [PublisherController::class, 'destroy']);

    // Workflow admin
    Route::post('/deposit-requests/{depositRequest}/publish', [DepositRequestController::class, 'publish']);
    Route::post('/deposit-requests/{depositRequest}/reject-admin', [DepositRequestController::class, 'rejectByAdmin']);
    Route::post('/deposit-requests/{depositRequest}/override', [DepositRequestController::class, 'overrideReject']);
    Route::post('/deposit-requests/{depositRequest}/second-review', [DepositRequestController::class, 'requestSecondReview']);

    // Logs d'activité
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/stats', [ActivityLogController::class, 'stats']);
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show']);

    // Statistiques téléchargements / consultations
    Route::get('/downloads', [DownloadController::class, 'index']);
    Route::get('/downloads/stats', [DownloadController::class, 'stats']);
    Route::get('/downloads/{download}', [DownloadController::class, 'show']);

    Route::get('/views', [ViewController::class, 'index']);
    Route::get('/views/stats', [ViewController::class, 'stats']);
    Route::get('/views/{view}', [ViewController::class, 'show']);
});
