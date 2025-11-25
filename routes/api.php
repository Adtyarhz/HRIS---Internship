<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;

Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::get('/auth/user/{id}', [AuthApiController::class, 'getUser']);
Route::post('/auth/logout', [AuthApiController::class, 'logout']);
Route::options('/{any}', function () {
    return response('', 204)
        ->header('Access-Control-Allow-Origin', 'http://localhost:8080')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->header('Access-Control-Allow-Credentials', 'true');
})->where('any', '.*');
