<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\PollingController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('announcement', AnnouncementController::class);
Route::post('/polling/{polling}/vote', [PollingController::class, 'vote'])->name('polling.vote');
Route::get('/announcement/{id}/export-polling', [AnnouncementController::class, 'exportPolling'])->name('announcement.export_polling');
// Alihkan dashboard ke announcement.index
Route::get('/dashboard', [AnnouncementController::class, 'dashboard'])->name('dashboard');