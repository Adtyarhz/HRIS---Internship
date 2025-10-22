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
            <div class="page-header-actions d-flex justify-content-between align-items-center">
            {{-- Back to List hanya untuk superadmin --}}
            @if(in_array(Auth::user()->role, ['superadmin','hc']))
                <a href="{{ route('employees.index') }}" class="action-button btn-back">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            @else
                {{-- Tambahkan elemen kosong agar space-between tetap bekerja --}}
                <div></div>
            @endif

            <div class="right-actions d-flex gap-2">
                <!-- Tombol Deactive -->
                @if(in_array(Auth::user()->role, ['superadmin','hc']))
                    <a href="{{ route('employees.deactivate.form', $employee) }}" class="action-button btn-deactivet-data">
                        <span class="material-symbols--tab-close-inactive"></span> Deactive Employee
                    </a>
                @else
                    <div></div>
                @endif
                <!-- Modal Deactive -->
                {{-- <x-delete-modal 
                    modalId="deactivate-employee-{{ $employee->id }}" 
                    :action="route('employees.deactivate', $employee)" 
                    method="POST" 
                    title="Deactive Confirmation"
                    message="Are you sure you want to deactivate this employee?" 
                    iconClass="tab-close-inactive"
                /> --}}
                <a href="{{ route('employees.edit', $employee) }}" class="action-button btn-edit-data">
                    <i class="fas fa-edit"></i> Edit Employee Data
                </a>
                <a href="{{ route('employees.data.edit_login', $employee->id) }}" class="action-button btn-edit-login">
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
                                @if ($employee->status == 'Aktif')
                                    <span class="status-badge status-active">Active</span>
                                @else
                                    <span class="status-badge status-inactive">Inactive</span>
                                @endif
                            </span>
                        </div>
                        <div class="data-item"><span class="data-label">Employment Type</span><span
                                class="data-value">{{ $employee->employee_type }}</span></div>
                        <div class="data-item"><span class="data-label">Division</span><span
                                class="data-value">{{ $employee->division->name ?? 'N/A' }}</span></div>
                        <div class="data-item">
                            <span class="data-label">Position</span>
                            <span class="data-value">{{ $employee->position->title ?? 'N/A' }}</span>
                        </div>
                        <div class="data-item"><span class="data-label">Office</span><span
                                class="data-value">{{ $employee->office }}</span></div>
                        <div class="data-item">
                            <span class="data-label">Date Of Entry</span>
                            <span
                                class="data-value">{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d F Y') : '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Exit Date</span>
                            <span
                                class="data-value">{{ $employee->separation_date ? \Carbon\Carbon::parse($employee->separation_date)->format('d F Y') : '-' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">CV File</span>
                            <span class="data-value">
                                @if ($employee->cv_file)
                                    <a href="{{ asset('storage/' . $employee->cv_file) }}" target="_blank"
                                        class="cv-link"><i class="fas fa-file-alt"></i> Lihat File</a>
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
                        <div class="data-item">
                        <span class="data-label">Role</span>
                        <span class="data-value">{{ $employee->user->role ?? '-' }}</span>
                    </div>
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
                        <div class="data-item"><span class="data-label">Full Name</span><span
                                class="data-value">{{ $employee->full_name }}</span></div>
                        <div class="data-item"><span class="data-label">NIK</span><span
                                class="data-value">{{ $employee->nik }}</span></div>
                        <div class="data-item"><span class="data-label">NIP</span><span
                                class="data-value">{{ $employee->nip ?? '-' }}</span></div>
                        <div class="data-item"><span class="data-label">NPWP</span><span
                                class="data-value">{{ $employee->npwp ?? '-' }}</span></div>
                        <div class="data-item"><span class="data-label">Gender</span><span
                                class="data-value">{{ $employee->gender }}</span></div>
                        <div class="data-item"><span class="data-label">Religion</span><span
                                class="data-value">{{ $employee->religion }}</span></div>
                        <div class="data-item"><span class="data-label">Date, Place of Birth</span><span
                                class="data-value">{{ $employee->birth_place }},
                                {{ $employee->birth_date ? \Carbon\Carbon::parse($employee->birth_date)->format('d F Y') : '' }}</span>
                        </div>
                        <div class="data-item"><span class="data-label">Age</span><span
                                class="data-value">{{ $age ? $age . ' Tahun' : 'N/A' }}</span></div>
                        <div class="data-item">
                            <span class="data-label">Marital Status</span><span
                                class="data-value">{{ $employee->marital_status }}
                                @if ($employee->marital_status !== 'Lajang')
                                    @if ($employee->dependents == 0)
                                        , Tidak ada tanggungan
                                    @else
                                        , {{ $employee->dependents }} Tanggungan
                                    @endif
                                @endif
                            </span>
                        </div>
                        <div class="data-item"><span class="data-label">ID Card Address</span><span
                                class="data-value">{{ $employee->ktp_address }}</span></div>
                        <div class="data-item"><span class="data-label">Domicile Address</span><span
                                class="data-value">{{ $employee->current_address }}</span></div>
                        <div class="data-item"><span class="data-label">Email</span><span
                                class="data-value">{{ $employee->email }}</span></div>
                        <div class="data-item"><span class="data-label">Phone Number</span><span
                                class="data-value">{{ $employee->phone_number }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full-width Column for Collapsible Card -->
        <div class="full-width-column">
            <div class="detail-card collapsible-card">
                <div class="card-header collapsible-header" data-bs-toggle="collapse" href="#healthHistoryCollapse"
                    role="button" aria-expanded="false" aria-controls="healthHistoryCollapse">
                    <h3 class="card-title"><i class="fas fa-heartbeat"></i> Health History</h3>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </div>
                <div class="collapse" id="healthHistoryCollapse">
                    <div class="card-content">
                        @if ($healthRecord)
                            <div class="data-item"><span class="data-label">Height</span><span
                                    class="data-value">{{ $healthRecord->height ?? '-' }} cm</span></div>
                            <div class="data-item"><span class="data-label">Weight</span><span
                                    class="data-value">{{ $healthRecord->weight ?? '-' }} kg</span></div>
                            <div class="data-item"><span class="data-label">Blood Type</span><span
                                    class="data-value">{{ $healthRecord->blood_type ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Known Allergies</span><span
                                    class="data-value">{{ $healthRecord->known_allergies ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Chronic Diseases</span><span
                                    class="data-value">{{ $healthRecord->chronic_diseases ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Last Checkup Date</span><span
                                    class="data-value">{{ $healthRecord->last_checkup_date ? \Carbon\Carbon::parse($healthRecord->last_checkup_date)->format('d F Y') : '-' }}</span>
                            </div>
                            <div class="data-item"><span class="data-label">Checkup Location</span><span
                                    class="data-value">{{ $healthRecord->checkup_loc ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Checkup Price</span><span
                                    class="data-value">{{ $healthRecord->price_last_checkup ? 'Rp ' . number_format($healthRecord->price_last_checkup, 0, ',', '.') : '-' }}</span>
                            </div>
                            <div class="data-item"><span class="data-label">Notes</span><span
                                    class="data-value">{{ $healthRecord->notes ?? '-' }}</span></div>
                        @else
                            <p>No health history data available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Education History Collapsible Card -->
        <div class="full-width-column">
            <div class="detail-card collapsible-card">
                <div class="card-header collapsible-header" data-bs-toggle="collapse" href="#educationHistoryCollapse"
                    role="button" aria-expanded="false" aria-controls="educationHistoryCollapse">
                    <h3 class="card-title"><i class="fas fa-user-graduate"></i> Education History</h3>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </div>
                <div class="collapse" id="educationHistoryCollapse">
                    <div class="card-content">
                        @forelse ($educationHistories as $education)
                            <div class="data-item"><span class="data-label">Level</span><span
                                    class="data-value">{{ $education->education_level ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Institution</span><span
                                    class="data-value">{{ $education->institution_name ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Address</span><span
                                    class="data-value">{{ $education->institution_address ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Major</span><span
                                    class="data-value">{{ $education->major ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Start Year</span><span
                                    class="data-value">{{ $education->start_year ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">End Year</span><span
                                    class="data-value">{{ $education->end_year ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">GPA/Score</span><span
                                    class="data-value">{{ $education->gpa_or_score ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Certificate No.</span><span
                                    class="data-value">{{ $education->certificate_number ?? '-' }}</span></div>
                            <hr>
                        @empty
                            <p>No education history available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Family & Dependents Collapsible Card -->
        <div class="full-width-column">
            <div class="detail-card collapsible-card">
                <div class="card-header collapsible-header" data-bs-toggle="collapse" href="#familyDependentsCollapse"
                    role="button" aria-expanded="false" aria-controls="familyDependentsCollapse">
                    <h3 class="card-title"><i class="fas fa-users"></i> Family & Dependents</h3>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </div>
                <div class="collapse" id="familyDependentsCollapse">
                    <div class="card-content">
                        @forelse ($dependents as $dependent)
                            <div class="data-item"><span class="data-label">Name</span><span
                                    class="data-value">{{ $dependent->contact_name ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Relationship</span><span
                                    class="data-value">{{ $dependent->relationship ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Phone</span><span
                                    class="data-value">{{ $dependent->phone_number ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Address</span><span
                                    class="data-value">{{ $dependent->address ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">City</span><span
                                    class="data-value">{{ $dependent->city ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Province</span><span
                                    class="data-value">{{ $dependent->province ?? '-' }}</span></div>
                            <hr>
                        @empty
                            <p>No dependent data available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Certifications Collapsible Card -->
        <div class="full-width-column">
            <div class="detail-card collapsible-card">
                <div class="card-header collapsible-header" data-bs-toggle="collapse" href="#certificationsCollapse"
                    role="button" aria-expanded="false" aria-controls="certificationsCollapse">
                    <h3 class="card-title"><i class="fas fa-certificate"></i> Certifications</h3>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </div>
                <div class="collapse" id="certificationsCollapse">
                    <div class="card-content">
                        @forelse ($employee->certifications()->with('certificationMaterials')->latest()->get() as $certification)
                            <div class="data-item"><span class="data-label">Name</span><span
                                    class="data-value">{{ $certification->certification_name ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Issuer</span><span
                                    class="data-value">{{ $certification->issuer ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Description</span><span
                                    class="data-value">{{ $certification->description ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Date Obtained</span><span
                                    class="data-value">{{ $certification->date_obtained ? \Carbon\Carbon::parse($certification->date_obtained)->format('d F Y') : '-' }}</span>
                            </div>
                            <div class="data-item"><span class="data-label">Expiry Date</span><span
                                    class="data-value">{{ $certification->expiry_date ? \Carbon\Carbon::parse($certification->expiry_date)->format('d F Y') : '-' }}</span>
                            </div>
                            <div class="data-item"><span class="data-label">Cost</span><span
                                    class="data-value">{{ $certification->cost ? 'Rp ' . number_format($certification->cost, 0, ',', '.') : '-' }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">Certificate File</span>
                                <span class="data-value">
                                    @if ($certification->certificate_file)
                                        <a href="{{ asset('storage/' . $certification->certificate_file) }}"
                                            target="_blank">
                                            <i class="fas fa-file-alt"></i>
                                            {{ Str::afterLast($certification->certificate_file, '_') }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">Certification Materials</span>
                                <span class="data-value">
                                    @if ($certification->certificationMaterials && $certification->certificationMaterials->count())
                                        <ul class="list-unstyled mb-0">
                                            @foreach ($certification->certificationMaterials as $index => $material)
                                                <li>
                                                    <a href="{{ asset('storage/' . $material->file_path) }}"
                                                        target="_blank">
                                                        <i class="fas fa-file-alt"></i> 
                                                        {{ Str::afterLast($material->file_path, '_') }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <hr>
                        @empty
                            <p>No certifications available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Insurance Collapsible Card -->
        <div class="full-width-column">
            <div class="detail-card collapsible-card">
                <div class="card-header collapsible-header" data-bs-toggle="collapse" href="#insuranceCollapse"
                    role="button" aria-expanded="false" aria-controls="insuranceCollapse">
                    <h3 class="card-title"><i class="fas fa-umbrella"></i> Insurance</h3>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </div>
                <div class="collapse" id="insuranceCollapse">
                    <div class="card-content">
                        @forelse ($insurances as $insurance)
                            <div class="data-item"><span class="data-label">Number</span><span
                                    class="data-value">{{ $insurance->insurance_number ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Type</span><span
                                    class="data-value">{{ $insurance->insurance_type ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Faskes Name</span><span
                                    class="data-value">{{ $insurance->faskes_name ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Faskes Address</span><span
                                    class="data-value">{{ $insurance->faskes_address ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Start Date</span><span
                                    class="data-value">{{ $insurance->start_date ? \Carbon\Carbon::parse($insurance->start_date)->format('d F Y') : '-' }}</span>
                            </div>
                            <div class="data-item"><span class="data-label">Expiry Date</span><span
                                    class="data-value">{{ $insurance->expiry_date ? \Carbon\Carbon::parse($insurance->expiry_date)->format('d F Y') : '-' }}</span>
                            </div>
                            <div class="data-item"><span class="data-label">Status</span><span
                                    class="data-value">{{ $insurance->status ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Insurance File</span>
                                <span class="data-value">
                                    @if ($insurance->insurance_file)
                                        <a href="{{ asset('storage/' . $insurance->insurance_file) }}" target="_blank">
                                            <i class="fas fa-file-alt"></i> {{ basename($insurance->insurance_file) }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <hr>
                        @empty
                            <p>No insurance data available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Experiences Collapsible Card -->
        <div class="full-width-column">
            <div class="detail-card collapsible-card">
                <div class="card-header collapsible-header" data-bs-toggle="collapse" href="#workExperienceCollapse"
                    role="button" aria-expanded="false" aria-controls="workExperienceCollapse">
                    <h3 class="card-title"><i class="fas fa-briefcase"></i> Work Experiences</h3>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </div>
                <div class="collapse" id="workExperienceCollapse">
                    <div class="card-content">
                        @forelse ($workExperiences as $work)
                            <div class="data-item"><span class="data-label">Company Name</span><span
                                    class="data-value">{{ $work->company_name ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Address</span><span
                                    class="data-value">{{ $work->company_address ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Phone</span><span
                                    class="data-value">{{ $work->company_phone ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Position</span><span
                                    class="data-value">{{ $work->position_title ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Start</span><span
                                    class="data-value">{{ $work->start_date ? \Carbon\Carbon::parse($work->start_date)->format('d F Y') : '-' }}</span>
                            </div>
                            <div class="data-item"><span class="data-label">End</span><span
                                    class="data-value">{{ $work->end_date ? \Carbon\Carbon::parse($work->end_date)->format('d F Y') : '-' }}</span>
                            </div>
                            <div class="data-item"><span class="data-label">Responsibilities</span><span
                                    class="data-value">{{ $work->responsibilities ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Reason to Leave</span><span
                                    class="data-value">{{ $work->reason_to_leave ?? '-' }}</span></div>
                            <div class="data-item"><span class="data-label">Last Salary</span><span
                                    class="data-value">{{ $work->last_salary ? 'Rp ' . number_format($work->last_salary, 0, ',', '.') : '-' }}</span>
                            </div>
                            <div class="data-item"><span class="data-label">Reference Letter</span>
                                <span class="data-value">
                                    @if ($work->reference_letter_file)
                                        <a href="{{ asset('storage/' . $work->reference_letter_file) }}" target="_blank">
                                            <i class="fas fa-file-alt"></i> {{ basename($work->reference_letter_file) }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="data-item"><span class="data-label">Salary Slip</span>
                                <span class="data-value">
                                    @if ($work->salary_slip_file)
                                        <a href="{{ asset('storage/' . $work->salary_slip_file) }}" target="_blank">
                                            <i class="fas fa-file-alt"></i> {{ basename($work->salary_slip_file) }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <hr>
                        @empty
                            <p>No work experience data available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Training Records Collapsible Card -->
<div class="full-width-column">
    <div class="detail-card collapsible-card">
        <div class="card-header collapsible-header" data-bs-toggle="collapse" href="#trainingRecordsCollapse"
            role="button" aria-expanded="false" aria-controls="trainingRecordsCollapse">
            <h3 class="card-title"><i class="fas fa-chalkboard-teacher"></i> Training Records</h3>
            <i class="fas fa-chevron-down collapse-icon"></i>
        </div>
        <div class="collapse" id="trainingRecordsCollapse">
            <div class="card-content">
                @forelse ($employee->trainingHistories()->with('trainingMaterials')->latest()->get() as $training)
                    <div class="data-item">
                        <span class="data-label">Training Name</span>
                        <span class="data-value">{{ $training->training_name ?? '-' }}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Provider</span>
                        <span class="data-value">{{ $training->provider ?? '-' }}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Description</span>
                        <span class="data-value">{{ $training->description ?? '-' }}</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Start Date</span>
                        <span class="data-value">
                            {{ $training->start_date ? \Carbon\Carbon::parse($training->start_date)->format('d F Y') : '-' }}
                        </span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">End Date</span>
                        <span class="data-value">
                            {{ $training->end_date ? \Carbon\Carbon::parse($training->end_date)->format('d F Y') : '-' }}
                        </span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Cost</span>
                        <span class="data-value">
                            {{ $training->cost ? 'Rp ' . number_format($training->cost, 0, ',', '.') : '-' }}
                        </span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Location</span>
                        <span class="data-value">{{ $training->location ?? '-' }}</span>
                    </div>

                    {{-- Certificate File --}}
                    <div class="data-item">
                        <span class="data-label">Certificate File</span>
                        <span class="data-value">
                            @if (!empty($training->certificate_file))
                                <a href="{{ asset('storage/' . $training->certificate_file) }}" target="_blank">
                                    <i class="fas fa-file-pdf"></i> View Certificate
                                </a>
                            @else
                                -
                            @endif
                        </span>
                    </div>

                    {{-- Training Materials --}}
                    <div class="data-item">
                        <span class="data-label">Training Materials</span>
                        <span class="data-value">
                            @if ($training->trainingMaterials && $training->trainingMaterials->count())
                                <ul class="list-unstyled mb-0">
                                    @foreach ($training->trainingMaterials as $index => $material)
                                        <li>
                                            <a href="{{ asset('storage/' . $material->file_path) }}"
                                                target="_blank">
                                                <i class="fas fa-file-alt"></i> File {{ $index + 1 }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    <hr>
                @empty
                    <p>No training records available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>


        {{-- Ikuti card kesehatan diatas untuk menerapkan expand card untuk menampilkan data lainnya --}}
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    {{-- <script>
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
    </script> --}}
@endpush
