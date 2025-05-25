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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    // Service Types
    Route::apiResource('service-types', ServiceTypeController::class);
    
    // Sitter Services
    Route::apiResource('sitter-services', SitterServiceController::class);
    
    // Users
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
