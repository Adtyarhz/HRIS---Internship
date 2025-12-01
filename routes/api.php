<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;

Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::get('/auth/user/{id}', [AuthApiController::class, 'getUser']);
Route::post('/auth/logout', [AuthApiController::class, 'logout']);
