<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SuspensionRequestController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\PublisherController;
use App\Http\Controllers\Api\DocumentTypeController;
use App\Http\Controllers\Api\DepositRequestController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\ViewController;
use App\Http\Controllers\Api\KeywordController;
use App\Http\Controllers\Api\ReferenceController;

Route::middleware(['auth:sanctum', 'admin', 'log.activity'])->group(function () {
    // Suspension directe et gestion des demandes de suspension
    Route::post('/users/{user}/suspend', [UserController::class, 'suspend']);
    Route::post('/suspension-requests/{suspensionRequest}/approve', [SuspensionRequestController::class, 'approve']);
    Route::post('/suspension-requests/{suspensionRequest}/reject', [SuspensionRequestController::class, 'reject']);

    // Gestion du catalogue (CRUD catégories, auteurs, éditeurs)
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::post('/authors', [AuthorController::class, 'store']);
    Route::put('/authors/{author}', [AuthorController::class, 'update']);
    Route::delete('/authors/{author}', [AuthorController::class, 'destroy']);

    Route::put('/publishers/{publisher}', [PublisherController::class, 'update']);
    Route::delete('/publishers/{publisher}', [PublisherController::class, 'destroy']);

    Route::put('/document-types/{documentType}', [DocumentTypeController::class, 'update']);
    Route::delete('/document-types/{documentType}', [DocumentTypeController::class, 'destroy']);

    // Workflow admin
    Route::post('/deposit-requests/{depositRequest}/publish', [DepositRequestController::class, 'publish']);
    Route::post('/deposit-requests/{depositRequest}/reject-admin', [DepositRequestController::class, 'rejectByAdmin']);
    Route::post('/deposit-requests/{depositRequest}/override', [DepositRequestController::class, 'overrideReject']);
    Route::post('/deposit-requests/{depositRequest}/second-review', [DepositRequestController::class, 'requestSecondReview']);
    Route::post('/deposit-requests/{depositRequest}/reassign', [DepositRequestController::class, 'reassignManager']);

    // Logs d'activité
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/filters', [ActivityLogController::class, 'filters']);
    Route::get('/activity-logs/stats', [ActivityLogController::class, 'stats']);
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show']);

    // Statistiques téléchargements / consultations
    Route::get('/downloads', [DownloadController::class, 'index']);
    Route::get('/downloads/{download}', [DownloadController::class, 'show']);

    Route::get('/views', [ViewController::class, 'index']);
    Route::get('/views/{view}', [ViewController::class, 'show']);

    // Gestion des mots-clés
    Route::post('/keywords', [KeywordController::class, 'store']);
    Route::delete('/keywords/{keyword}', [KeywordController::class, 'destroy']);

    // Modification/suppression des références (admin uniquement)
    Route::put('/references/{reference}', [ReferenceController::class, 'update']);
    Route::delete('/references/{reference}', [ReferenceController::class, 'destroy']);

    // Forcer le téléchargement (admin uniquement)
    Route::post('/references/{reference}/force-download', [ReferenceController::class, 'forceDownload']);
});
