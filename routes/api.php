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

// Public routes (no authentication required)
Route::group(['prefix' => 'public'], function () {
    // Public Service Types - anyone can view
    Route::get('service-types', [ServiceTypeController::class, 'index']);
    Route::get('service-types/{id}', [ServiceTypeController::class, 'show']);
    
    // Public Sitter Services - anyone can view and search
    Route::get('sitter-services', [SitterServiceController::class, 'index']);
    Route::get('sitter-services/{id}', [SitterServiceController::class, 'show']);
    
    // Public Users (sitters only) - anyone can view sitters
    Route::get('sitters', [UserController::class, 'getSitters']);
    Route::get('sitters/{id}', [UserController::class, 'show']);
    
    // Public Sitter Profiles - anyone can view and search
    Route::get('sitter-profiles', [SitterProfileController::class, 'index']);
    Route::get('sitter-profiles/{id}', [SitterProfileController::class, 'show']);
    Route::get('sitter-profiles/search', [SitterProfileController::class, 'searchByLocation']);
    Route::get('users/{user_id}/sitter-profile', [SitterProfileController::class, 'getByUserId']);
    
    // Public Ratings - anyone can view ratings
    Route::get('ratings', [RatingController::class, 'index']);
    Route::get('sitters/{sitter_id}/average-rating', [RatingController::class, 'getSitterAverageRating']);
});

// Authenticated routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    // Service Types - full CRUD (only authenticated users can create/update/delete)
    Route::post('service-types', [ServiceTypeController::class, 'store']);
    Route::put('service-types/{id}', [ServiceTypeController::class, 'update']);
    Route::delete('service-types/{id}', [ServiceTypeController::class, 'destroy']);
    
    // Sitter Services - full CRUD (only authenticated users can create/update/delete)
    Route::post('sitter-services', [SitterServiceController::class, 'store']);
    Route::put('sitter-services/{id}', [SitterServiceController::class, 'update']);
    Route::delete('sitter-services/{id}', [SitterServiceController::class, 'destroy']);
    
    // Users - full CRUD (only authenticated users)
    Route::apiResource('users', UserController::class);
    
    // Pets
    Route::apiResource('pets', PetController::class);
    
    // Service Requests
    Route::apiResource('service-requests', ServiceRequestController::class);
    Route::put('service-requests/{id}/accept', [ServiceRequestController::class, 'accept']);
    Route::put('service-requests/{id}/reject', [ServiceRequestController::class, 'reject']);
    
    // Ratings
    Route::apiResource('ratings', RatingController::class);
    Route::get('sitters/{sitter_id}/average-rating', [RatingController::class, 'getSitterAverageRating']);
    
    // Favorites
    Route::apiResource('favorites', FavoriteController::class);
    Route::delete('favorites/remove', [FavoriteController::class, 'removeByIds']);
    Route::get('owners/{owner_id}/favorites', [FavoriteController::class, 'getOwnerFavorites']);
    Route::get('favorites/check', [FavoriteController::class, 'checkFavoriteStatus']);
    
    // Sitter Profiles
    Route::apiResource('sitter-profiles', SitterProfileController::class);
    Route::get('users/{user_id}/sitter-profile', [SitterProfileController::class, 'getByUserId']);
    Route::get('sitter-profiles/search', [SitterProfileController::class, 'searchByLocation']);
});
