@extends('layouts.admin')

@section('title', 'Detail Karyawan')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endpush

@section('content')
<div class="employee-detail-page">
    <!-- Custom Page Header -->
    <div class="page-header-container">
        <h1 class="page-title">
            Employee Detail : {{ $employee->full_name }}
        </h1>
        <div class="page-header-actions">
            <a href="{{ route('employees.index') }}" class="action-button btn-back">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <div class="right-actions">
                <a href="{{ route('employees.edit', $employee) }}" class="action-button btn-edit-data">
                    <i class="fas fa-edit"></i> Edit Employee Data
                </a>
                <a href="#" class="action-button btn-edit-login">
                    <i class="fas fa-user-cog"></i> Edit Login Account
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content (2 Columns) -->
    <div class="detail-container">
        <!-- Left Column -->
        <div class="detail-column left-column">
            <!-- Employment Data Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-briefcase"></i> Employment Data</h3>
                </div>
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
                    <div class="data-item"><span class="data-label">Employment Type</span><span class="data-value">{{ $employee->employee_type }}</span></div>
                    <div class="data-item"><span class="data-label">Division</span><span class="data-value">{{ $employee->division->name ?? 'N/A' }}</span></div>
                    <div class="data-item">
                        <span class="data-label">Position</span>
                        <span class="data-value">{{ $employee->position->title ?? 'N/A' }}</span>
                    </div>
                    <div class="data-item"><span class="data-label">Office</span><span class="data-value">{{ $employee->office }}</span></div>
                    <div class="data-item">
                        <span class="data-label">Date Of Entry</span>
                        <span class="data-value">{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d F Y') : '-' }}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Exit Date</span>
                        <span class="data-value">{{ $employee->separation_date ? \Carbon\Carbon::parse($employee->separation_date)->format('d F Y') : '-' }}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">CV File</span>
                        <span class="data-value">
                            @if($employee->cv_file)
                                <a href="{{ $employee->cv_file_url }}" target="_blank" class="cv-link"><i class="fas fa-file-alt"></i> Lihat File</a>
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Login Account Data Card -->
            <div class="detail-card">
                 <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-lock"></i> Login Account Data</h3>
                </div>
                <div class="card-content">
                    <div class="data-item">
                        <span class="data-label">Login Name</span>
                        <span class="data-value">{{ $employee->user->name ?? 'Not Connected' }}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Email Login</span>
                        <span class="data-value">{{ $employee->user->email ?? '-' }}</span>
                    </div>
                    {{-- <div class="data-item">
                        <span class="data-label">Role</span>
                        <span class="data-value">{{ $employee->user->role ?? '-' }}</span>
                    </div> --}}
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="detail-column right-column">
            <!-- Personal Data Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-card"></i> Personal Data</h3>
                </div>
                <div class="card-content">
                    <div class="data-item"><span class="data-label">Full Name</span><span class="data-value">{{ $employee->full_name }}</span></div>
                    <div class="data-item"><span class="data-label">NIK</span><span class="data-value">{{ $employee->nik }}</span></div>
                    <div class="data-item"><span class="data-label">NPWP</span><span class="data-value">{{ $employee->npwp ?? '-' }}</span></div>
                    <div class="data-item"><span class="data-label">Gender</span><span class="data-value">{{ $employee->gender }}</span></div>
                    <div class="data-item"><span class="data-label">Religion</span><span class="data-value">{{ $employee->religion }}</span></div>
                    <div class="data-item"><span class="data-label">Date, Place of Birth</span><span class="data-value">{{ $employee->birth_place }}, {{ $employee->birth_date ? \Carbon\Carbon::parse($employee->birth_date)->format('d F Y') : '' }}</span></div>
                    <div class="data-item"><span class="data-label">Age</span><span class="data-value">{{ $age ? $age . ' Tahun' : 'N/A' }}</span></div>
                    <div class="data-item">
                        <span class="data-label">Marital Status</span><span class="data-value">{{ $employee->marital_status }}
                            @if($employee->marital_status !== 'Lajang')
                                @if($employee->dependents == 0)
                                    , Tidak ada tanggungan
                                @else
                                    , {{ $employee->dependents }} Tanggungan
                                @endif
                            @endif
                        </span>
                    </div>
                    <div class="data-item"><span class="data-label">ID Card Address</span><span class="data-value">{{ $employee->ktp_address }}</span></div>
                    <div class="data-item"><span class="data-label">Domicile Address</span><span class="data-value">{{ $employee->current_address }}</span></div>
                    <div class="data-item"><span class="data-label">Email</span><span class="data-value">{{ $employee->email }}</span></div>
                    <div class="data-item"><span class="data-label">Phone Number</span><span class="data-value">{{ $employee->phone_number }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
