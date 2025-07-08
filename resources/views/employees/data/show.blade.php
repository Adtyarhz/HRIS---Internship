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
                                <a href="{{ asset('storage/'.$employee->cv_file) }}" target="_blank" class="cv-link"><i class="fas fa-file-alt"></i> Lihat File</a>
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

    <!-- Full-width Column for Collapsible Card -->
    <div class="full-width-column">
        <div class="detail-card collapsible-card">
            <div class="card-header collapsible-header" data-bs-toggle="collapse" href="#healthHistoryCollapse" role="button" aria-expanded="false" aria-controls="healthHistoryCollapse">
                <h3 class="card-title"><i class="fas fa-heartbeat"></i> Health History</h3>
                <i class="fas fa-chevron-down collapse-icon"></i>
            </div>
            <div class="collapse" id="healthHistoryCollapse">
                <div class="card-content">
                    @if($healthRecord)
                        <div class="data-item"><span class="data-label">Height</span><span class="data-value">{{ $healthRecord->height ?? '-' }} cm</span></div>
                        <div class="data-item"><span class="data-label">Weight</span><span class="data-value">{{ $healthRecord->weight ?? '-' }} kg</span></div>
                        <div class="data-item"><span class="data-label">Blood Type</span><span class="data-value">{{ $healthRecord->blood_type ?? '-' }}</span></div>
                        <div class="data-item"><span class="data-label">Known Allergies</span><span class="data-value">{{ $healthRecord->known_allergies ?? '-' }}</span></div>
                        <div class="data-item"><span class="data-label">Chronic Diseases</span><span class="data-value">{{ $healthRecord->chronic_diseases ?? '-' }}</span></div>
                        <div class="data-item"><span class="data-label">Last Checkup Date</span><span class="data-value">{{ $healthRecord->last_checkup_date ? \Carbon\Carbon::parse($healthRecord->last_checkup_date)->format('d F Y') : '-' }}</span></div>
                        <div class="data-item"><span class="data-label">Checkup Location</span><span class="data-value">{{ $healthRecord->checkup_loc ?? '-' }}</span></div>
                        <div class="data-item"><span class="data-label">Checkup Price</span><span class="data-value">{{ $healthRecord->price_last_checkup ? 'Rp ' . number_format($healthRecord->price_last_checkup, 0, ',', '.') : '-' }}</span></div>
                        <div class="data-item"><span class="data-label">Notes</span><span class="data-value">{{ $healthRecord->notes ?? '-' }}</span></div>
                    @else
                        <p>No health history data available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Ikuti card kesehatan diatas untuk menerapkan expand card untuk menampilkan data lainnya --}}
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const collapseEl = document.getElementById('healthHistoryCollapse');
            collapseEl.addEventListener('show.bs.collapse', function () {
                var icon = this.previousElementSibling.querySelector('.collapse-icon');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            });
            collapseEl.addEventListener('hide.bs.collapse', function () {
                var icon = this.previousElementSibling.querySelector('.collapse-icon');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            });
        });
    </script>
@endpush

