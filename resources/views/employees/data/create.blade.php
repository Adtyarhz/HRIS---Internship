@extends('layouts.admin')

@section('title', 'Add New Employee Account')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Add New Employee Account')

@push('styles')
    {{-- Dedicated CSS for this form page --}}
    <link rel="stylesheet" href="{{ asset('css/form-create.css') }}">
    <style>
        /* Sembunyikan form sampai user konfirmasi (jika offering accepted) */
        #create-form-wrapper { display: none; }
    </style>
@endpush

@section('content')
    {{-- Flash messages (jika layout utama belum menampilkannya) --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

   {{-- Modal Konfirmasi: tampil bila offering letter accepted --}}
@if(isset($offeringLetter) && $offeringLetter->offering_status === 'accepted')
    <div class="modal fade" id="offerConfirmModal" tabindex="-1" aria-labelledby="offerConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="offerConfirmModalLabel">Konfirmasi Penerimaan Penawaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure to add this applicant data to employee data?</p>
                    <div class="mt-3">
    <div class="d-flex mb-2">
        <div style="width: 140px;"><strong>Nama Kandidat</strong></div>
        <div>: {{ $applicant->full_name ?? '-' }}</div>
    </div>
    <div class="d-flex mb-2">
        <div style="width: 140px;"><strong>Posisi yang Dilamar</strong></div>
        <div>: {{ $applicant->position->title ?? '-' }}</div>
    </div>
    <div class="d-flex mb-2">
        <div style="width: 140px;"><strong>Jenis Kontrak</strong></div>
        <div>: {{ $offeringLetter->contract_type ?? '-' }}</div>
    </div>
</div>

                </div>
                <div class="modal-footer">
                    <a href="{{ route('applicants.show', $applicant->id) }}" class="btn btn-secondary">Batal</a>
                    <button type="button" id="confirm-offer-btn" class="btn btn-primary">Konfirmasi & Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>
@endif

    {{-- Form create employee (sembunyi sampai konfirmasi jika modal ada) --}}
    <div class="card form-container" id="create-form-wrapper">
        <div class="card-body">
            <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <!-- Left Column: Main Information -->
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-12">
                                <h5>Personal & Contact Information</h5>
                                <hr>
                            </div>

                            <!-- Full Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="full_name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                        id="full_name" name="full_name" placeholder="Enter full name"
                                        value="{{ old('full_name', $applicant->full_name ?? '') }}" required>
                                    @error('full_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- NIK -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nik">NIK <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nik') is-invalid @enderror"
                                        id="nik" name="nik" placeholder="Input NIK"
                                        value="{{ old('nik', $applicant->nik ?? '') }}" inputmode="numeric" maxlength="32" required>
                                    @error('nik') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Gender -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gender <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center" style="height: 38px;">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="male"
                                                value="Laki-laki" {{ old('gender', $applicant->gender ?? '') == 'Laki-laki' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="male">Laki-laki</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="female"
                                                value="Perempuan" {{ old('gender', $applicant->gender ?? '') == 'Perempuan' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="female">Perempuan</label>
                                        </div>
                                    </div>
                                    @error('gender') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Religion -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="religion">Religion <span class="text-danger">*</span></label>
                                    <select class="form-control @error('religion') is-invalid @enderror" id="religion" name="religion">
                                        <option value="">Select Religion</option>
                                        @foreach (['Islam','Kristen','Katolik','Hindu','Buddha','Khonghucu'] as $religion)
                                            <option value="{{ $religion }}" {{ old('religion', $applicant->religion ?? '') == $religion ? 'selected' : '' }}>
                                                {{ $religion }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('religion') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Birth Place & Birth Date -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="birth_place">Birth Place <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('birth_place') is-invalid @enderror"
                                        id="birth_place" name="birth_place" placeholder="City"
                                        value="{{ old('birth_place', $applicant->birth_place ?? '') }}">
                                    @error('birth_place') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="birth_date">Birth Date <span class="text-danger">*</span></label>
                                    <div class="input-group date-input-group">
                                        <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                                            id="birth_date" name="birth_date" value="{{ old('birth_date', $applicant->birth_date ?? '') }}">
                                        <label for="birth_date" class="input-group-append">
                                            <span class="input-group-text"><img src="{{ asset('img/calendar_icon.png') }}" alt="calendar"></span>
                                        </label>
                                    </div>
                                    @error('birth_date') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Phone & Email -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone_number">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('phone_number') is-invalid @enderror"
                                        id="phone_number" name="phone_number" value="{{ old('phone_number', $applicant->phone_number ?? $applicant->phone ?? '') }}"
                                        placeholder="+62..." inputmode="tel" maxlength="20" required>
                                    @error('phone_number') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" placeholder="employee@example.com"
                                        value="{{ old('email', $applicant->email ?? '') }}" required>
                                    @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Marital Status -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="marital_status">Marital Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('marital_status') is-invalid @enderror" id="marital_status" name="marital_status">
                                        <option value="">Select Marital Status</option>
                                        @foreach (['Lajang','Pernikahan Pertama','Pernikahan Kedua','Pernikahan Ketiga','Cerai Hidup','Cerai Mati'] as $status)
                                            <option value="{{ $status }}" {{ old('marital_status', $applicant->marital_status ?? '') == $status ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('marital_status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Dependents -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dependents">Dependents <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('dependents') is-invalid @enderror"
                                        id="dependents" name="dependents" placeholder="0"
                                        value="{{ old('dependents', $applicant->dependents ?? 0) }}">
                                    @error('dependents') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <h5>Address Information</h5>
                                <hr>
                            </div>

                            <!-- KTP Address -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="ktp_address">ID Address (KTP) <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('ktp_address') is-invalid @enderror" id="ktp_address" name="ktp_address"
                                        rows="3" placeholder="Enter address as per ID card">{{ old('ktp_address', $applicant->ktp_address ?? '') }}</textarea>
                                    @error('ktp_address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Current Address -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="current_address">Current Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('current_address') is-invalid @enderror" id="current_address"
                                        name="current_address" rows="3" placeholder="Enter current residential address">{{ old('current_address', $applicant->address ?? '') }}</textarea>
                                    @error('current_address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Employment & Other Details -->
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="col-12">
                                <h5>Employment Details</h5>
                                <hr>
                            </div>

                            <!-- NIP -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="nip">NIP</label>
                                    <input type="text" class="form-control @error('nip') is-invalid @enderror"
                                        id="nip" name="nip" value="{{ old('nip') }}" placeholder="Input NIP" inputmode="numeric">
                                    @error('nip') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- NPWP -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="npwp">NPWP</label>
                                    <input type="text" class="form-control @error('npwp') is-invalid @enderror"
                                        id="npwp" name="npwp" value="{{ old('npwp') }}" placeholder="Input NPWP" inputmode="numeric">
                                    @error('npwp') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Hire Date -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="hire_date">Hire Date <span class="text-danger">*</span></label>
                                    <div class="input-group date-input-group">
                                        <input type="date" class="form-control @error('hire_date') is-invalid @enderror" id="hire_date"
                                            name="hire_date" value="{{ old('hire_date') }}">
                                        <label for="hire_date" class="input-group-append">
                                            <span class="input-group-text"><img src="{{ asset('img/calendar_icon.png') }}" alt="calendar"></span>
                                        </label>
                                    </div>
                                    @error('hire_date') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Separation Date -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="separation_date">Separation Date</label>
                                    <div class="input-group date-input-group">
                                        <input type="date" class="form-control @error('separation_date') is-invalid @enderror" id="separation_date"
                                            name="separation_date" value="{{ old('separation_date') }}">
                                        <label for="separation_date" class="input-group-append">
                                            <span class="input-group-text"><img src="{{ asset('img/calendar_icon.png') }}" alt="calendar"></span>
                                        </label>
                                    </div>
                                    @error('separation_date') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="Aktif" {{ old('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Tidak Aktif" {{ old('status') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                           <div class="col-12">
    <div class="form-group">
        <label for="employee_type">Employee Type <span class="text-danger">*</span></label>
        @php
            $prefType = old('employee_type', $employee->employee_type ?? '');
            $types = ['PKWT', 'PKWTT', 'Probation', 'Intern'];
        @endphp
        <select class="form-control @error('employee_type') is-invalid @enderror"
                id="employee_type"
                name="employee_type"
                required>
            <option value="">-- Select Type --</option>
            @foreach($types as $type)
                <option value="{{ $type }}" {{ $prefType === $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
            @endforeach
        </select>
        @error('employee_type')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>

                            <!-- Office -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="office">Office</label>
                                    <select class="form-control @error('office') is-invalid @enderror" id="office" name="office">
                                        <option value="">Select Office</option>
                                        <option value="Kantor Pusat" {{ old('office') == 'Kantor Pusat' ? 'selected' : '' }}>Kantor Pusat</option>
                                        <option value="Kantor Cabang" {{ old('office') == 'Kantor Cabang' ? 'selected' : '' }}>Kantor Cabang</option>
                                    </select>
                                    @error('office') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Division (Readonly) -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Division</label>
                                    <input type="text" id="division_name" class="form-control"
                                        value="{{ old('division_name', $applicant->division->name ?? '-') }}" readonly>

                                    <input type="hidden" name="division_id" id="division_id"
                                        value="{{ old('division_id', $applicant->division_id ?? '') }}">
                                </div>
                            </div>

                            <!-- Position (User Select) -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="required">Position</label>
                                    <select name="position_id" id="position_id" class="form-control" required>
                                        <option value="">-- Select Position --</option>
                                        @foreach ($positions as $pos)
                                            <option value="{{ $pos->id }}"
                                                data-division-id="{{ $pos->division_id }}"
                                                data-division="{{ $pos->division->name ?? '-' }}"
                                                {{ old('position_id', $applicant->applied_position ?? '') == $pos->id ? 'selected' : '' }}>
                                                {{ $pos->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('position_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Connect to User -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="user_id">Connect to User</label>
                                    <select class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                        <option value="">-- Not Connected --</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Files -->
                            <div class="col-12 mt-3">
                                <h5>Files & Documents</h5>
                                <hr>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label for="photo">Photo</label>
                                    <img id="photo-preview" src="{{ $applicant->photo_url ?? 'https://placehold.co/200x200/EFEFEF/AAAAAA?text=Photo+Preview' }}" alt="Photo Preview" class="photo-preview mb-2">
                                    <input type="file" class="form-control-file @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg">
                                    @error('photo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label for="cv_file">CV File (pdf, doc, docx)</label>
                                    <input type="file" class="form-control-file @error('cv_file') is-invalid @enderror" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx">
                                    @error('cv_file') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="form-buttons-container">
                            <a href="{{ route('employees.index') }}" class="btn btn-cancel">Cancel</a>
                            <button type="submit" class="btn btn-submit">Create</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formWrapper = document.getElementById('create-form-wrapper');

            // Jika modal konfirmasi ada (offering accepted), tampilkan modal terlebih dahulu
            const modalEl = document.getElementById('offerConfirmModal');
            if (modalEl) {
                // Pastikan Bootstrap 5 JS dimuat di layout
                const bsModal = new bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
                bsModal.show();

                // Jika user confirm -> tutup modal dan tampilkan form
                document.getElementById('confirm-offer-btn').addEventListener('click', function() {
                    bsModal.hide();
                    formWrapper.style.display = 'block';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });

                // Jika user klik close atau batal, modal 'Batal' link sudah mengarah ke applicants.show
            } else {
                // Jika tidak ada modal (mis. tidak dari offering), tampilkan form langsung
                formWrapper.style.display = 'block';
            }

            // Photo preview
            const photoInput = document.getElementById('photo');
            const photoPreview = document.getElementById('photo-preview');
            if (photoInput && photoPreview) {
                photoInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            photoPreview.src = e.target.result;
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
    <script>
        document.getElementById('position_id').addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const divisionName = selected.getAttribute('data-division') || '-';
            const divisionId = selected.getAttribute('data-division-id') || '';

            document.getElementById('division_name').value = divisionName;
            document.getElementById('division_id').value = divisionId;
        });
    </script>
@endpush
