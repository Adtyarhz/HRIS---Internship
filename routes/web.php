<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HealthRecordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\PollingController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    // Arahkan halaman utama ke daftar karyawan untuk kemudahan akses
    return redirect()->route('employees.index');
});

// Route untuk tab edit alamat
Route::get('employees/{employee}/address', [EmployeeController::class, 'editAddress'])->name('employees.address.edit');
// Route untuk tab kesehatan
Route::get('/employees/{employee}/health', [HealthRecordController::class, 'edit'])->name('employees.health.edit');

// Baris ini akan membuat semua route untuk Employee CRUD
Route::resource('employees', EmployeeController::class);

// Rute untuk Health Record yang terhubung dengan Employee
Route::prefix('employees/{employee}/health-record')->name('health-records.')->group(function () {
    // Menampilkan form untuk create/edit
    Route::get('/', [HealthRecordController::class, 'edit'])->name('edit');
    
    // Menyimpan data (baik baru atau update)
    Route::post('/', [HealthRecordController::class, 'storeOrUpdate'])->name('storeOrUpdate');

    // Menghapus data
    Route::delete('/', [HealthRecordController::class, 'destroy'])->name('destroy');
});
Route::resource('announcement', AnnouncementController::class);
Route::post('/polling/{polling}/vote', [PollingController::class, 'vote'])->name('polling.vote');
Route::get('/announcement/{id}/export-polling', [AnnouncementController::class, 'exportPolling'])->name('announcement.export_polling');
// Alihkan dashboard ke announcement.index
Route::get('/dashboard', [AnnouncementController::class, 'dashboard'])->name('dashboard');