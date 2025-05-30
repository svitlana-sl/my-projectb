<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceTypeController;
use App\Http\Controllers\Api\SitterServiceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\SitterProfileController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes (no middleware needed)
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

// Service Types routes - public read, authenticated write
Route::get('service-types', [ServiceTypeController::class, 'index']);
Route::get('service-types/{id}', [ServiceTypeController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('service-types', [ServiceTypeController::class, 'store']);
    Route::put('service-types/{id}', [ServiceTypeController::class, 'update']);
    Route::delete('service-types/{id}', [ServiceTypeController::class, 'destroy']);
});

// Sitter Services routes - public read, authenticated write
Route::get('sitter-services', [SitterServiceController::class, 'index']);
Route::get('sitter-services/{id}', [SitterServiceController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('sitter-services', [SitterServiceController::class, 'store']);
    Route::put('sitter-services/{id}', [SitterServiceController::class, 'update']);
    Route::delete('sitter-services/{id}', [SitterServiceController::class, 'destroy']);
});

// Users routes - public sitters list, authenticated full CRUD
Route::get('sitters', [UserController::class, 'getSitters']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
});

// Sitter Profiles routes - public read and search, authenticated write
Route::get('sitter-profiles', [SitterProfileController::class, 'index']);
Route::get('sitter-profiles/{id}', [SitterProfileController::class, 'show']);
Route::get('sitter-profiles/search', [SitterProfileController::class, 'searchByLocation']);
Route::get('users/{user_id}/sitter-profile', [SitterProfileController::class, 'getByUserId']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('sitter-profiles', [SitterProfileController::class, 'store']);
    Route::put('sitter-profiles/{id}', [SitterProfileController::class, 'update']);
    Route::delete('sitter-profiles/{id}', [SitterProfileController::class, 'destroy']);
});

// Ratings routes - public read, authenticated write
Route::get('ratings', [RatingController::class, 'index']);
Route::get('ratings/{id}', [RatingController::class, 'show']);
Route::get('sitters/{sitter_id}/average-rating', [RatingController::class, 'getSitterAverageRating']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('ratings', [RatingController::class, 'store']);
    Route::put('ratings/{id}', [RatingController::class, 'update']);
    Route::delete('ratings/{id}', [RatingController::class, 'destroy']);
});

// Authenticated-only routes
Route::middleware('auth:sanctum')->group(function () {
    // Pets
    Route::apiResource('pets', PetController::class);
    
    // Service Requests
    Route::apiResource('service-requests', ServiceRequestController::class);
    Route::put('service-requests/{id}/accept', [ServiceRequestController::class, 'accept']);
    Route::put('service-requests/{id}/reject', [ServiceRequestController::class, 'reject']);
    
    // Favorites
    Route::apiResource('favorites', FavoriteController::class);
    Route::delete('favorites/remove', [FavoriteController::class, 'removeByIds']);
    Route::get('owners/{owner_id}/favorites', [FavoriteController::class, 'getOwnerFavorites']);
    Route::get('favorites/check', [FavoriteController::class, 'checkFavoriteStatus']);
});
