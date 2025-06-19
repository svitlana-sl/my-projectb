<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\SitterServiceController;

Route::get('/', function () {
    return view('welcome');
});

// CORS-enabled image serving route for development
Route::get('/images/{filename}', function ($filename) {
    $path = public_path('images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->middleware(config('app.env') !== 'production' ? ['cors'] : []);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::resource('service-types', ServiceTypeController::class);
Route::resource('sitter-services', SitterServiceController::class);
