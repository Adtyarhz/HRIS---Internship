<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CareerHistoryController;
use App\Http\Controllers\CareerProjectionController;
use App\Http\Controllers\FamilyDependentController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\PollingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkExperienceController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\EducationHistoryController;
use App\Http\Controllers\TrainingHistoryController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\RecruitmentProgressController;
use App\Http\Controllers\InterviewScheduleController;
use App\Http\Controllers\OrganizationalStructureController;

// === LOGIN & LOGOUT ROUTES ===
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/employee/{id}/edit-login', [LoginController::class, 'editLogin'])->name('employees.data.edit_login');
Route::post('/employee/{id}/update-login', [LoginController::class, 'updateLogin'])->name('employees.data.update_login');


// === PROTECTED ROUTES ===
Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Employee tab: address
    Route::get('employees/{employee}/address', [EmployeeController::class, 'editAddress'])->name('employees.address.edit');

    // Employee tab: health
    Route::get('/employees/{employee}/health', [HealthRecordController::class, 'edit'])->name('employees.health.edit');

    // Employee CRUD
    Route::resource('employees', EmployeeController::class);
    Route::post('/employees/{employee}', [EmployeeController::class, 'deactivate'])->name('employees.deactivate');

    // Health Record
    Route::prefix('employees/{employee}/health-record')->name('health-records.')->group(function () {
        Route::get('/', [HealthRecordController::class, 'edit'])->name('edit');
        Route::post('/', [HealthRecordController::class, 'storeOrUpdate'])->name('storeOrUpdate');
        Route::delete('/', [HealthRecordController::class, 'destroy'])->name('destroy');
    });

    // Work Experience
    Route::prefix('employees/{employee}/work-experience')->name('employees.work-experience.')->group(function () {
        Route::get('/', [WorkExperienceController::class, 'index'])->name('index');
        Route::get('/create', [WorkExperienceController::class, 'create'])->name('create');
        Route::post('/', [WorkExperienceController::class, 'store'])->name('store');
        Route::get('/{workExperience}/edit', [WorkExperienceController::class, 'edit'])->name('edit');
        Route::put('/{workExperience}', [WorkExperienceController::class, 'update'])->name('update');
        Route::delete('/{workExperience}', [WorkExperienceController::class, 'destroy'])->name('destroy');
    });

    // Certifications
    Route::prefix('employees/{employee}/certifications')->name('employees.certifications.')->group(function () {
        Route::get('/', [CertificationController::class, 'index'])->name('index');
        Route::get('/create', [CertificationController::class, 'create'])->name('create');
        Route::post('/', [CertificationController::class, 'store'])->name('store');
        Route::get('/{certification}/edit', [CertificationController::class, 'edit'])->name('edit');
        Route::put('/{certification}', [CertificationController::class, 'update'])->name('update');
        Route::delete('/{certification}', [CertificationController::class, 'destroy'])->name('destroy');
        Route::delete('/{certification}/materials/{material}', [CertificationController::class, 'destroyMaterial'])->name('materials.destroy');
    });

    // Insurance
    Route::prefix('employees/{employee}/insurance')->name('employees.insurance.')->group(function () {
        Route::get('/', [InsuranceController::class, 'index'])->name('index');
        Route::get('/create', [InsuranceController::class, 'create'])->name('create');
        Route::post('/', [InsuranceController::class, 'store'])->name('store');
        Route::get('/{insurance}/edit', [InsuranceController::class, 'edit'])->name('edit');
        Route::put('/{insurance}', [InsuranceController::class, 'update'])->name('update');
        Route::delete('/{insurance}', [InsuranceController::class, 'destroy'])->name('destroy');
    });

    // Education History
    Route::prefix('employees/{employee}/educationhistory')->name('employees.educationhistory.')->group(function () {
        Route::get('/', [EducationHistoryController::class, 'index'])->name('index');
        Route::get('/create', [EducationHistoryController::class, 'create'])->name('create');
        Route::post('/', [EducationHistoryController::class, 'store'])->name('store');
        Route::get('/{educationHistory}/edit', [EducationHistoryController::class, 'edit'])->name('edit');
        Route::put('/{educationHistory}', [EducationHistoryController::class, 'update'])->name('update');
        Route::delete('/{educationHistory}', [EducationHistoryController::class, 'destroy'])->name('destroy');
    });

    // Training History
    Route::prefix('employees/{employee}/training-histories')->name('employees.training-histories.')->group(function () {
        Route::get('/', [TrainingHistoryController::class, 'index'])->name('index');
        Route::get('/create', [TrainingHistoryController::class, 'create'])->name('create');
        Route::post('/', [TrainingHistoryController::class, 'store'])->name('store');
        Route::get('/{trainingHistory}/edit', [TrainingHistoryController::class, 'edit'])->name('edit');
        Route::put('/{trainingHistory}', [TrainingHistoryController::class, 'update'])->name('update');
        Route::delete('/{trainingHistory}', [TrainingHistoryController::class, 'destroy'])->name('destroy');
        Route::delete('/{trainingHistory}/materials/{material}', [TrainingHistoryController::class, 'destroyMaterial'])->name('materials.destroy');
    });

    // Family Dependents
    Route::resource('employees.family-dependents', FamilyDependentController::class)->scoped();

    // Career Path
    Route::get('/career-path', [EmployeeController::class, 'indexCareer'])->name('career.index');
    Route::get('employees/{employee}/career', [EmployeeController::class, 'showCareer'])->name('employees.showCareer');

    // Career History
    Route::resource('employees.career_histories', CareerHistoryController::class)
        ->parameters(['career_histories' => 'careerHistory'])
        ->except(['show']);

    // Career Projection
    Route::prefix('employees/{employee}/career-projection')->name('employees.career_projection.')->group(function () {
        Route::get('/', [CareerProjectionController::class, 'form'])->name('form');
        Route::post('/', [CareerProjectionController::class, 'storeOrUpdate'])->name('storeOrUpdate');
        Route::delete('/', [CareerProjectionController::class, 'destroy'])->name('destroy');
    });

    // Announcement
    Route::resource('announcement', AnnouncementController::class);
    Route::post('/polling/{polling}/vote', [PollingController::class, 'vote'])->name('polling.vote');
    Route::get('/announcement/{id}/export-polling', [AnnouncementController::class, 'exportPolling'])->name('announcement.export_polling');
    Route::post('/announcements/{id}/vote', [AnnouncementController::class, 'vote'])->name('announcement.vote');

    // Dashboard
    Route::get('/dashboard', [AnnouncementController::class, 'dashboard'])->name('dashboard');

    // Applicants
    Route::resource('applicants', ApplicantController::class);

    // Recruitment Progress
    Route::prefix('applicants/{applicant}/recruitment-progress')->group(function () {
        Route::get('/', [RecruitmentProgressController::class, 'show'])->name('recruitment-progress.show');
        Route::get('/stage/{stage}', [RecruitmentProgressController::class, 'stageShow'])->name('recruitment.stage.show');
        Route::get('/stage/{stage}/edit', [RecruitmentProgressController::class, 'stageEdit'])->name('recruitment.stage.edit');
        Route::put('/stage/update', [RecruitmentProgressController::class, 'stageUpdate'])->name('recruitment.stage.update');
    });

    // Interview Schedule
    Route::prefix('applicants/{applicant}/interview-schedule')->group(function () {
        Route::get('/', [InterviewScheduleController::class, 'index'])->name('interview-schedule.index');
        Route::get('/create', [InterviewScheduleController::class, 'create'])->name('interview-schedule.create');
        Route::post('/', [InterviewScheduleController::class, 'store'])->name('interview-schedule.store');
        Route::get('/{schedule}', [InterviewScheduleController::class, 'show'])->name('interview-schedule.show');
        Route::get('/{schedule}/edit', [InterviewScheduleController::class, 'edit'])->name('interview-schedule.edit');
        Route::put('/{schedule}', [InterviewScheduleController::class, 'update'])->name('interview-schedule.update');
        Route::delete('/{schedule}', [InterviewScheduleController::class, 'destroy'])->name('interview-schedule.destroy');
    });

    Route::resource('organization/structure', OrganizationalStructureController::class)
        ->parameters(['structure' => 'position'])
        ->names('organization.structure');
});
