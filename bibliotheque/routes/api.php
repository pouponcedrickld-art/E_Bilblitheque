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

// Routes protégées par session Sanctum
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // CRUD protégé
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::post('/authors', [AuthorController::class, 'store']);
    Route::put('/authors/{author}', [AuthorController::class, 'update']);
    Route::delete('/authors/{author}', [AuthorController::class, 'destroy']);

    Route::post('/publishers', [PublisherController::class, 'store']);
    Route::put('/publishers/{publisher}', [PublisherController::class, 'update']);
    Route::delete('/publishers/{publisher}', [PublisherController::class, 'destroy']);

    Route::post('/references', [ReferenceController::class, 'store']);
    Route::put('/references/{reference}', [ReferenceController::class, 'update']);
    Route::delete('/references/{reference}', [ReferenceController::class, 'destroy']);

    // Téléchargement
    Route::get('/references/{reference}/download', [ReferenceController::class, 'download']);

    // Dépôt
    Route::apiResource('deposit-requests', DepositRequestController::class);

    // Workflow de validation
    Route::post('/deposit-requests/{depositRequest}/approve-manager', [DepositRequestController::class, 'approveByManager']);
    Route::post('/deposit-requests/{depositRequest}/reject-manager', [DepositRequestController::class, 'rejectByManager']);
    Route::post('/deposit-requests/{depositRequest}/publish', [DepositRequestController::class, 'publish']);
    Route::post('/deposit-requests/{depositRequest}/reject-admin', [DepositRequestController::class, 'rejectByAdmin']);
    Route::post('/deposit-requests/{depositRequest}/override', [DepositRequestController::class, 'overrideReject']);
    Route::post('/deposit-requests/{depositRequest}/second-review', [DepositRequestController::class, 'requestSecondReview']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Utilisateurs (admin + RH)
    Route::apiResource('users', UserController::class);
    Route::post('/users/{user}/activate', [UserController::class, 'activate']);
    Route::post('/users/{user}/suspend', [UserController::class, 'suspend']);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);

    // Logs d'activité (admin uniquement)
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/stats', [ActivityLogController::class, 'stats']);
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show']);

    // Téléchargements
    Route::get('/downloads', [DownloadController::class, 'index']);
    Route::get('/downloads/stats', [DownloadController::class, 'stats']);
    Route::get('/downloads/{download}', [DownloadController::class, 'show']);

    // Consultations
    Route::get('/views', [ViewController::class, 'index']);
    Route::get('/views/stats', [ViewController::class, 'stats']);
    Route::get('/views/{view}', [ViewController::class, 'show']);
});
