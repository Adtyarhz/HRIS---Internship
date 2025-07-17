<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CareerHistoryController;
use App\Http\Controllers\CareerProjectionController;
use App\Http\Controllers\FamilyDependentController;
use App\Http\Controllers\HealthRecordController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\PollingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkExperienceController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\EducationHistoryController;
use App\Http\Controllers\TrainingHistoryController;
use App\Http\Controllers\ApplicantController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    // Arahkan halaman utama ke daftar karyawan untuk kemudahan akses
    return redirect()->route('dashboard');
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

// Group nested work experiences under employee
Route::prefix('employees/{employee}/work-experience')->name('employees.work-experience.')->group(function () {
    Route::get('/', [WorkExperienceController::class, 'index'])->name('index'); // Show list
    Route::get('/create', [WorkExperienceController::class, 'create'])->name('create'); // Show add form
    Route::post('/', [WorkExperienceController::class, 'store'])->name('store'); // Save new
    Route::get('/{workExperience}/edit', [WorkExperienceController::class, 'edit'])->name('edit'); // Edit form
    Route::put('/{workExperience}', [WorkExperienceController::class, 'update'])->name('update'); // Update existing
    Route::delete('/{workExperience}', [WorkExperienceController::class, 'destroy'])->name('destroy'); // Delete
});

Route::prefix('employees/{employee}/certifications')->name('employees.certifications.')->group(function () {
    Route::get('/', [CertificationController::class, 'index'])->name('index');
    Route::get('/create', [CertificationController::class, 'create'])->name('create');
    Route::post('/', [CertificationController::class, 'store'])->name('store');
    Route::get('/{certification}/edit', [CertificationController::class, 'edit'])->name('edit');
    Route::put('/{certification}', [CertificationController::class, 'update'])->name('update');
    Route::delete('/{certification}', [CertificationController::class, 'destroy'])->name('destroy');
    Route::delete('/{certification}/materials/{material}', [CertificationController::class, 'destroyMaterial'])->name('materials.destroy');
});

Route::prefix('employees/{employee}/insurance')->name('employees.insurance.')->group(function () {
    Route::get('/', [InsuranceController::class, 'index'])->name('index'); // Show list
    Route::get('/create', [InsuranceController::class, 'create'])->name('create'); // Show add form
    Route::post('/', [InsuranceController::class, 'store'])->name('store'); // Save new
    Route::get('/{insurance}/edit', [InsuranceController::class, 'edit'])->name('edit'); // Edit form
    Route::put('/{insurance}', [InsuranceController::class, 'update'])->name('update'); // Update existing
    Route::delete('/{insurance}', [InsuranceController::class, 'destroy'])->name('destroy'); // Delete
});

Route::prefix('employees/{employee}/educationhistory')->name('employees.educationhistory.')->group(function () {
    Route::get('/', [EducationHistoryController::class, 'index'])->name('index');
    Route::get('/create', [EducationHistoryController::class, 'create'])->name('create');
    Route::post('/', [EducationHistoryController::class, 'store'])->name('store');
    Route::get('/{educationHistory}/edit', [EducationHistoryController::class, 'edit'])->name('edit');
    Route::put('/{educationHistory}', [EducationHistoryController::class, 'update'])->name('update');
    Route::delete('/{educationHistory}', [EducationHistoryController::class, 'destroy'])->name('destroy');
});

Route::prefix('employees/{employee}/training-histories')->name('employees.training-histories.')->group(function () {
    Route::get('/', [TrainingHistoryController::class, 'index'])->name('index');
    Route::get('/create', [TrainingHistoryController::class, 'create'])->name('create');
    Route::post('/', [TrainingHistoryController::class, 'store'])->name('store');
    Route::get('/{trainingHistory}/edit', [TrainingHistoryController::class, 'edit'])->name('edit');
    Route::put('/{trainingHistory}', [TrainingHistoryController::class, 'update'])->name('update');
    Route::delete('/{trainingHistory}', [TrainingHistoryController::class, 'destroy'])->name('destroy');
    Route::delete('/{trainingHistory}/materials/{material}', [TrainingHistoryController::class, 'destroyMaterial'])->name('materials.destroy');
});

Route::resource('employees.family-dependents', FamilyDependentController::class)->scoped();

Route::get('/career-path', [EmployeeController::class, 'indexCareer'])->name('career.index');
Route::get('employees/{employee}/career', [EmployeeController::class, 'showCareer'])->name('employees.showCareer');

// Nested resource routes for CareerHistory under Employee
Route::resource('employees.career_histories', CareerHistoryController::class)
    ->parameters(['career_histories' => 'careerHistory'])
    ->except(['show']);

// Nested resource routes for CareerProjection under Employee
// Route::middleware('auth')->group(function () { --> jika login sudah terdefinisi
Route::prefix('employees/{employee}/career-projection')->name('employees.career_projection.')->group(function () {
    Route::get('/', [CareerProjectionController::class, 'form'])->name('form');
    Route::post('/', [CareerProjectionController::class, 'storeOrUpdate'])->name('storeOrUpdate');
    Route::delete('/', [CareerProjectionController::class, 'destroy'])->name('destroy');
});
// });

Route::resource('announcement', AnnouncementController::class);
Route::post('/polling/{polling}/vote', [PollingController::class, 'vote'])->name('polling.vote');
Route::get('/announcement/{id}/export-polling', [AnnouncementController::class, 'exportPolling'])->name('announcement.export_polling');
// Alihkan dashboard ke announcement.index
Route::get('/dashboard', [AnnouncementController::class, 'dashboard'])->name('dashboard');

Route::resource('applicants', ApplicantController::class);
