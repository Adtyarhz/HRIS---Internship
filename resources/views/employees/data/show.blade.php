@extends('layouts.admin')

@section('title', 'Detail Karyawan')
@section('content_header', 'Employee Information')

@section('content')
    <div class="employee-detail-page">
        <!-- =================================== -->
        <!-- ==      HEADER HALAMAN KUSTOM      == -->
        <!-- =================================== -->
        <header class="detail-header">
            <h1 class="page-title">
                Employee Detail : {{ $employee->full_name }}
            </h1>
            <div class="header-actions">
                 <a href="{{ route('employees.index') }}" class="action-button btn-back">
                    <i class="fas fa-arrow-left"></i> Back to list
                </a>
                <a href="#" class="action-button btn-education">
                    <i class="fas fa-graduation-cap"></i> View Education History
                </a>
                <a href="{{ route('employees.edit', $employee) }}" class="action-button btn-edit-data">
                    <i class="fas fa-id-card"></i> Edit Employee Data
                </a>
                <a href="#" class="action-button btn-edit-login">
                    <i class="fas fa-user-cog"></i> Edit Login Account
                </a>
            </div>
        </header>

        <!-- =================================== -->
        <!-- ==      KONTEN UTAMA (2 KOLOM)     == -->
        <!-- =================================== -->
        <div class="detail-container">
            <!-- Kolom Kiri -->
            <div class="detail-column left-column">
                <!-- Card Data Kepegawaian -->
                <div class="detail-card">
                    <h3 class="card-title"><i class="fas fa-briefcase"></i> Employment Data</h3>
                    <div class="card-content">
                        <div class="data-item">
                            <span class="data-label">Employee Status</span>
                            <span class="data-value">
                                @if($employee->status == 'Aktif')
                                    <span class="status-badge status-active">Active</span>
                                @else
                                    <span class="status-badge status-inactive">Inactive</span>
                                @endif
                            </span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Division</span>
                            <span class="data-value">{{ $employee->division->name ?? 'N/A' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Position</span>
                            <span class="data-value">{{ $employee->position->name ?? 'N/A' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Date Of Entry</span>
                            <span class="data-value">{{ $employee->hire_date->format('d F Y') }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Exit Date</span>
                            <span class="data-value">{{ $employee->separation_date ? $employee->separation_date->format('d F Y') : '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">CV File</span>
                            <span class="data-value">
                                <a href="#" class="cv-link"><i class="fas fa-file-alt"></i> Lihat File</a>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Data Akun Login -->
                <div class="detail-card">
                    <h3 class="card-title"><i class="fas fa-user-lock"></i> Login Account Data</h3>
                    <div class="card-content">
                        <div class="data-item">
                            <span class="data-label">Login Name</span>
                            <span class="data-value">{{ $employee->user->name ?? 'Tidak terhubung' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Email Login</span>
                            <span class="data-value">{{ $employee->user->email ?? '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Role</span>
                            <span class="data-value">{{-- Placeholder for Role --}}Manajer</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="detail-column right-column">
                <!-- Card Data Pribadi -->
                <div class="detail-card">
                    <h3 class="card-title"><i class="fas fa-id-card"></i> Personal Data</h3>
                    <div class="card-content">
                        <div class="data-item"><span class="data-label">Full Name</span><span class="data-value">{{ $employee->full_name }}</span></div>
                        <div class="data-item"><span class="data-label">NIK</span><span class="data-value">{{ $employee->nik }}</span></div>
                        <div class="data-item"><span class="data-label">NPWP</span><span class="data-value">{{ $employee->npwp ?? '-' }}</span></div>
                        <div class="data-item"><span class="data-label">Gender</span><span class="data-value">{{ $employee->gender }}</span></div>
                        <div class="data-item"><span class="data-label">Date, Place of Birth</span><span class="data-value">{{ $employee->birth_place }}, {{ $employee->birth_date->format('d F Y') }}</span></div>
                        <div class="data-item"><span class="data-label">Age</span><span class="data-value">{{ $age ? $age . ' Tahun' : 'N/A' }}</span></div>
                        <div class="data-item"><span class="data-label">Marital Status</span><span class="data-value">{{ $employee->marital_status }}</span></div>
                        <div class="data-item"><span class="data-label">Employee Email</span><span class="data-value">{{ $employee->email }}</span></div>
                        <div class="data-item"><span class="data-label">Phone Number</span><span class="data-value">{{ $employee->phone_number }}</span></div>
                        <div class="data-item"><span class="data-label">ID Card Address</span><span class="data-value">{{ $employee->ktp_address }}</span></div>
                        <div class="data-item"><span class="data-label">Domicile Address</span><span class="data-value">{{ $employee->current_address }}</span></div>
                        <div class="data-item"><span class="data-label">City</span><span class="data-value">Jakarta Selatan</span></div>
                        <div class="data-item"><span class="data-label">State/Province</span><span class="data-value">DKI Jakarta</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Mengganti gaya default dari layout admin */
    .content-wrapper {
        background-color: #FEFEF9;
    }
    .content {
        padding: 20px 25px;
    }
    .employee-detail-page {
        width: 100%;
    }

    /* Header Halaman Kustom */
    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
    }
    .page-title {
        margin: 0;
        font-size: 30px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 400;
        color: black;
    }
    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .action-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 5px;
        text-decoration: none;
        color: white;
        font-size: 12px;
        font-family: 'Montserrat', sans-serif;
        border: none;
    }
    .action-button i {
        font-size: 12px;
    }
    .btn-back { background-color: #495154; }
    .btn-education { background-color: #317985; }
    .btn-edit-data { background-color: #C4A652; color: black; }
    .btn-edit-login { background-color: #067DCF; }

    /* Kontainer dan Kolom */
    .detail-container {
        display: flex;
        gap: 25px;
    }
    .detail-column {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    .left-column { flex: 1; }
    .right-column { flex: 1.5; } /* Membuat kolom kanan sedikit lebih lebar */

    /* Card Styling */
    .detail-card {
        background-color: #FFFFF6;
        border: 1px solid rgba(0, 0, 0, 0.10);
        border-radius: 5px;
        padding: 20px;
        flex-grow: 1;
    }
    .card-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 14px;
        font-weight: 400;
        margin-top: 0;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: black;
    }
    .card-title i { color: black; }
    .card-content { display: flex; flex-direction: column; gap: 18px; }

    /* Item Data dalam Card */
    .data-item {
        display: flex;
        font-size: 12px;
        font-family: 'Montserrat', sans-serif;
    }
    .data-label {
        width: 150px;
        min-width: 150px;
        font-weight: 700;
        color: black;
    }
    .data-value {
        font-weight: 500;
        word-break: break-word;
        color: black;
    }
    .status-badge {
        padding: 3px 12px;
        border-radius: 5px;
        font-size: 10px;
        font-weight: 500;
        color: #FEFEF9;
    }
    .status-active { background-color: #39813F; }
    .status-inactive { background-color: #CB3A31; }

    .cv-link {
        color: #0187C7;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .cv-link i { font-size: 14px; }

    /* Responsive */
    @media (max-width: 1200px) {
        .right-column { flex: 1; }
    }
    @media (max-width: 992px) {
        .detail-container { flex-direction: column; }
    }
    @media (max-width: 768px) {
        .detail-header { flex-direction: column; align-items: flex-start; }
        .header-actions { flex-direction: column; align-items: stretch; width: 100%; }
        .action-button { justify-content: center; }
        .page-title { font-size: 24px; }
    }
</style>
@endpush
