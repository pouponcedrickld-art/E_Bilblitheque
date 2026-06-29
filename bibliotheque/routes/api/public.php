<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\PublisherController;
use App\Http\Controllers\Api\ReferenceController;
use App\Http\Controllers\Api\KeywordController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\DocumentTypeController;

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/{author}', [AuthorController::class, 'show']);
Route::get('/publishers', [PublisherController::class, 'index']);
Route::get('/publishers/{publisher}', [PublisherController::class, 'show']);
Route::get('/references', [ReferenceController::class, 'index']);
Route::get('/references/featured', [ReferenceController::class, 'featured']);
Route::get('/references/{reference}', [ReferenceController::class, 'show'])->whereNumber('reference');
Route::get('/references/{reference}/read', [ReferenceController::class, 'read']);
Route::get('/keywords', [KeywordController::class, 'index']);
Route::get('/stats', [StatsController::class, 'index']);
Route::get('/document-types', [DocumentTypeController::class, 'index']);
Route::get('/document-types/{documentType}', [DocumentTypeController::class, 'show']);
