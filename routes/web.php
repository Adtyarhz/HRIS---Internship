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
use App\Http\Controllers\EmployeeEditRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizationalStructureController;
use App\Models\User;
use App\Notifications\EmployeeEditRequestNotification;
use App\Http\Controllers\KpiPeriodController;
use App\Http\Controllers\KpiIndicatorController;
use App\Http\Controllers\KpiTemplateController;
use App\Http\Controllers\KpiAssessmentController;
use App\Http\Controllers\OvertimeApplicationController;
use App\Http\Controllers\KpiReportController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\ApprovalController;

// === LOGIN & LOGOUT ROUTES ===
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/employee/{id}/edit-login', [LoginController::class, 'editLogin'])->name('employees.data.edit_login');
Route::post('/employee/{id}/update-login', [LoginController::class, 'updateLogin'])->name('employees.data.update_login');

// === PROTECTED ROUTES ===
Route::middleware('auth')->group(function () {
    Route::post('/employees/{id}/reset-password', [LoginController::class, 'resetPassword'])->name('employees.reset_password');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard - Semua role bisa akses
    Route::get('/dashboard', [AnnouncementController::class, 'dashboard'])->name('dashboard');

    // === SUPERADMIN ONLY ROUTES ===
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':superadmin,hc')->group(function () {
        // Employee CRUD - Hanya superadmin
        Route::resource('employees', EmployeeController::class);
        Route::put('/employees/{employee}/deactivate', [EmployeeController::class, 'deactivate'])->name('employees.deactivate');
        Route::get('/employees/{employee}/deactivate-form', [EmployeeController::class, 'showDeactivateForm'])->name('employees.deactivate.form');

        // Struktur Organisasi: CRUD hanya superadmin & hc
        Route::get('/organization/structure/create', [OrganizationalStructureController::class, 'create'])->name('organization.structure.create');
        Route::post('/organization/structure', [OrganizationalStructureController::class, 'store'])->name('organization.structure.store');
        Route::get('/organization/structure/{position}', [OrganizationalStructureController::class, 'show'])->name('organization.structure.show');
        Route::get('/organization/structure/{position}/edit', [OrganizationalStructureController::class, 'edit'])->name('organization.structure.edit');
        Route::put('/organization/structure/{position}', [OrganizationalStructureController::class, 'update'])->name('organization.structure.update');
        Route::delete('/organization/structure/{position}', [OrganizationalStructureController::class, 'destroy'])->name('organization.structure.destroy');

        Route::prefix('organization/division')->name('organization.division.')->group(function () {
            Route::get('/create', [DivisionController::class, 'create'])->name('create');
            Route::post('/', [DivisionController::class, 'store'])->name('store');
            Route::get('/{division}/edit', [DivisionController::class, 'edit'])->name('edit');
            Route::put('/{division}', [DivisionController::class, 'update'])->name('update');
            Route::delete('/{division}', [DivisionController::class, 'destroy'])->name('destroy');
        });

        // ========================================================================
        // KPI MANAGEMENT (SETUP)
        // ========================================================================
        // --- Rute untuk Manajemen Indikator KPI ---
        Route::resource('kpi-indicators', KpiIndicatorController::class)->except('show');
        // --- Rute untuk Manajemen Periode KPI ---
        Route::resource('kpi-periods', KpiPeriodController::class)->except('show');
        // --- Rute untuk Manajemen Templat KPI ---
        Route::resource('kpi-templates', KpiTemplateController::class)->except(['edit', 'update']);
        // Rute untuk mengelola item di dalam sebuah templat
        Route::post('kpi-templates/{kpiTemplate}/items', [KpiTemplateController::class, 'storeItem'])->name('kpi-templates.items.store');
        Route::delete('kpi-template-items/{kpiTemplateItem}', [KpiTemplateController::class, 'destroyItem'])->name('kpi-template-items.destroy');
        // Rute untuk mengelola aturan skoring di dalam sebuah item
        Route::post('kpi-template-items/{kpiTemplateItem}/rules', [KpiTemplateController::class, 'storeScoringRule'])->name('kpi-template-items.rules.store');
        Route::delete('kpi-scoring-rules/{kpiScoringRule}', [KpiTemplateController::class, 'destroyScoringRule'])->name('kpi-scoring-rules.destroy');
    });

    // === Route khusus untuk user biasa mengedit data mereka sendiri ===
    Route::middleware('auth')->group(function () {
        Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    });

    // === SUPERADMIN, DIREKSI, MANAGER, SECTION_HEAD ROUTES ===
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':superadmin,hc,direksi,manager,section_head')->group(function () {
        // Applicants - Superadmin, direksi, manager, section_head
        Route::resource('applicants', ApplicantController::class);
        Route::get('/applicants/export/csv', [ApplicantController::class, 'exportCsv'])->name('applicants.export.csv')->middleware('role:hc,superadmin');

        // Recruitment Progress - Superadmin, direksi, manager, section_head
        Route::prefix('applicants/{applicant}/recruitment-progress')->group(function () {
            Route::get('/', [RecruitmentProgressController::class, 'show'])->name('recruitment-progress.show');
            Route::get('/stage/{stage}', [RecruitmentProgressController::class, 'stageShow'])->name('recruitment.stage.show');
            Route::get('/stage/{stage}/edit', [RecruitmentProgressController::class, 'stageEdit'])->name('recruitment.stage.edit');
            Route::put('/stage/update', [RecruitmentProgressController::class, 'stageUpdate'])->name('recruitment.stage.update');
        });

        Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':superadmin,hc,direksi,manager,section_head')
            ->prefix('interview-schedule')
            ->group(function () {

                // Semua role terkait bisa lihat daftar jadwal interview (view only)
                Route::get('/', [InterviewScheduleController::class, 'index'])
                    ->name('interview-schedule.index');

                // Hanya superadmin & hc yang bisa create, edit, delete
                Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':superadmin,hc')->group(function () {
                    Route::get('/create', [InterviewScheduleController::class, 'create'])
                        ->name('interview-schedule.create');

                    Route::post('/', [InterviewScheduleController::class, 'store'])
                        ->name('interview-schedule.store');

                    Route::get('/{schedule}/edit', [InterviewScheduleController::class, 'edit'])
                        ->name('interview-schedule.edit');

                    Route::put('/{schedule}', [InterviewScheduleController::class, 'update'])
                        ->name('interview-schedule.update');

                    Route::delete('/{schedule}', [InterviewScheduleController::class, 'destroy'])
                        ->name('interview-schedule.destroy');

                    Route::get('/interview-schedule/get-interviewers', [InterviewScheduleController::class, 'getInterviewersByApplicant'])
                        ->name('interview-schedule.get-interviewers');

                });

                // Detail satu jadwal (bisa dilihat oleh semua role terkait)
                Route::get('/{schedule}', [InterviewScheduleController::class, 'show'])
                    ->name('interview-schedule.show');
            });

    });

    Route::post('/applicants/{id}/convert-to-employee', [ApplicantController::class, 'convertToEmployee'])
        ->name('applicants.convertToEmployee');
    Route::get('/employees/convert/{id}', [EmployeeController::class, 'convert'])
        ->name('employees.convert');

    // tambahan khusus approve/reject
    Route::post('overtime-applications/{overtime_application}/approve', [OvertimeApplicationController::class, 'approve'])->name('overtime.approve');
    Route::post('overtime-applications/{overtime_application}/reject', [OvertimeApplicationController::class, 'reject'])->name('overtime.reject');

    // Overtime Application (resource)
    Route::resource('overtime-applications', OvertimeApplicationController::class);

    Route::patch('/overtime-tasks/{task}/toggle', [OvertimeApplicationController::class, 'toggleTask'])
        ->name('overtime-tasks.toggle');

    // === SUPERADMIN & DIREKSI ROUTES ===
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':superadmin,hc')->group(function () {
        // Announcement - Superadmin dan direksi
        Route::resource('announcement', AnnouncementController::class);
        Route::get('/announcement/{id}/export-polling', [AnnouncementController::class, 'exportPolling'])->name('announcement.export_polling');
    });
    Route::get('announcement/{announcement}', [AnnouncementController::class, 'show'])->name('announcement.show');

    // === SUPERADMIN, DIREKSI, MANAGER, SECTION_HEAD ROUTES ===
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':superadmin,direksi,hc')->group(function () {
        Route::get('/career-path', [EmployeeController::class, 'indexCareer'])->name('career.index');
        Route::get('/career-path/{employee}', [EmployeeController::class, 'showCareer'])->name('career.show');
    });

    // === ALL AUTHENTICATED USERS ROUTES ===
    // Employee view - Semua user bisa lihat data employee mereka sendiri
    Route::get('employees/{employee}/career', [EmployeeController::class, 'showCareer'])->name('employees.showCareer');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

    // Employee Management - Semua role bisa akses sesuai dengan menu
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':superadmin,hc,direksi,manager,section_head,staff_bisnis,staff_support')->group(function () {
        // Employee tab: address
        Route::get('employees/{employee}/address', [EmployeeController::class, 'editAddress'])->name('employees.address.edit');

        // Employee tab: health
        Route::get('/employees/{employee}/health', [HealthRecordController::class, 'edit'])->name('employees.health.edit');

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

        // Career History - semua user login bisa akses index, tapi cek dibatasi di controller
        Route::middleware('auth')->group(function () {
            Route::get('employees/{employee}/career_histories', [CareerHistoryController::class, 'index'])
                ->name('employees.career_histories.index');

            Route::resource('employees.career_histories', CareerHistoryController::class)
                ->parameters(['career_histories' => 'careerHistory'])
                ->except(['index', 'show']);
        });


        // Career Projection
        // Semua user bisa melihat daftar career path miliknya (atau milik karyawan lain jika diizinkan)
        Route::get(
            '/employees/{employee}/career-projections',
            [CareerProjectionController::class, 'index']
        )->name('career-projections.index');
        Route::get(
            '/employees/{employee}/career-projections/create',
            [CareerProjectionController::class, 'create']
        )->name('career-projections.create');

        Route::post(
            '/employees/{employee}/career-projections',
            [CareerProjectionController::class, 'store']
        )->name('career-projections.store');

        Route::get(
            '/employees/{employee}/career-projections/{careerProjection}/edit',
            [CareerProjectionController::class, 'edit']
        )->name('career-projections.edit');

        Route::put(
            '/employees/{employee}/career-projections/{careerProjection}',
            [CareerProjectionController::class, 'update']
        )->name('career-projections.update');

        Route::delete(
            '/employees/{employee}/career-projections/{careerProjection}',
            [CareerProjectionController::class, 'destroy']
        )->name('career-projections.destroy');
    });

    // === ALL AUTHENTICATED USERS ROUTES ===
    // Polling - Semua user yang login bisa vote
    Route::post('/polling/{polling}/vote', [PollingController::class, 'vote'])->name('polling.vote');
    Route::post('/announcements/{id}/vote', [AnnouncementController::class, 'vote'])->name('announcement.vote');

    // === EMPLOYEE EDIT REQUEST - Only HC & SUPERADMIN ===
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':superadmin,hc')->group(function () {
        Route::prefix('employee-edit-requests')->name('employee-edit-requests.')->group(function () {
            Route::get('/', [EmployeeEditRequestController::class, 'index'])->name('index');
            Route::get('/{id}', [EmployeeEditRequestController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [EmployeeEditRequestController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [EmployeeEditRequestController::class, 'reject'])->name('reject');
        });
    });
    // === REQUEST EDIT DATA PRIBADI - Untuk semua karyawan ===
    Route::middleware('auth')->group(function () {
        Route::post('/employee-edit-requests', [EmployeeEditRequestController::class, 'store'])->name('employee-edit-requests.store');
    });

    // === NOTIFICATIONS ===    
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::get('/notifications/redirect/{id}', [NotificationController::class, 'redirect'])
        ->name('notifications.redirect');

    Route::get('/notifications/read/{id}', [NotificationController::class, 'readAndRedirect'])
        ->name('notifications.readAndRedirect');

    // Struktur Organisasi: Semua role bisa akses halaman index
    Route::get('/organization/structure', [OrganizationalStructureController::class, 'index'])->name('organization.structure.index');
    Route::get('/organization/division', [DivisionController::class, 'index'])->name('organization.division.index');
    Route::get('/test-notif', function () {
        $target = User::whereIn('role', ['hc', 'superadmin'])->first();

        $target->notify(new EmployeeEditRequestNotification("Samuel", 999));

        return "Notifikasi sudah dicoba kirim ke user ID {$target->id}";
    });

    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':hc,manager')->group(function () {
        Route::get('/kpi-reports', [KpiReportController::class, 'index'])->name('kpi-reports.index');
        Route::get('/kpi-reports/export', [KpiReportController::class, 'export'])->name('kpi-reports.export');
        Route::get('kpi/reports/{kpiAssessment}', [KpiReportController::class, 'show'])->name('kpi-reports.show');
    });

    // Akses untuk semua role kecuali superadmin
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':hc,direksi,manager,section_head,staff_bisnis,staff_support')->group(function () {
        // ========================================================================
        // KPI ASSESSMENT PROCESS (SUPERVISOR & EMPLOYEE)
        // ========================================================================
        Route::resource('kpi-assessments', KpiAssessmentController::class)->except(['destroy', 'edit']);
    });

    // hanya hc
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':hc')->prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::get('/{cdr}', [ApprovalController::class, 'show'])->name('show');

        // Aksi
        Route::post('/{cdr}/check', [ApprovalController::class, 'check'])->name('check');
        Route::post('/{cdr}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{cdr}/reject', [ApprovalController::class, 'reject'])->name('reject');
    });


    // Hanya superadmin, hc, manager, dan section_head yang boleh CRUD
    // Route::middleware(['auth', 'role:superadmin,hc,manager,section_head'])->group(function () {
    //     Route::get('/employees/{employee}/career-projections/create', 
    //         [CareerProjectionController::class, 'create']
    //     )->name('career-projections.create');

    //     Route::post('/employees/{employee}/career-projections', 
    //         [CareerProjectionController::class, 'store']
    //     )->name('career-projections.store');

    //     Route::get('/employees/{employee}/career-projections/{careerProjection}/edit', 
    //         [CareerProjectionController::class, 'edit']
    //     )->name('career-projections.edit');

    //     Route::put('/employees/{employee}/career-projections/{careerProjection}', 
    //         [CareerProjectionController::class, 'update']
    //     )->name('career-projections.update');

    //     Route::delete('/employees/{employee}/career-projections/{careerProjection}', 
    //         [CareerProjectionController::class, 'destroy']
    //     )->name('career-projections.destroy');
    // });
});
// === GUEST ROUTES ===
// Routes yang tidak memerlukan authentication bisa ditambahkan di sini
