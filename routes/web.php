<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HealthRecordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    // Arahkan halaman utama ke daftar karyawan untuk kemudahan akses
    return redirect()->route('employees.index');
});

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