{{--
    File: _form.blade.php
    Deskripsi:
    Formulir untuk data personal dan kepegawaian karyawan,
    disusun ulang dengan tata letak grid yang responsif dan seimbang.
--}}

<div class="row">
    <!-- Kolom Kiri: Foto Profil dan File -->
    <div class="col-lg-4 col-md-5">
        <div class="form-group text-center">
            <label>Profile Picture</label>
            <img id="photo-preview" src="{{ $employee->photo ? asset('storage/photo/' . $employee->photo) : 'https://placehold.co/200x220/EFEFEF/AAAAAA?text=Upload+Photo' }}" alt="Profile Picture" class="profile-picture-preview">
            <input type="file" name="photo" id="photo" class="form-control-file mt-2 @error('photo') is-invalid @enderror" accept="image/*">
            @error('photo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="cv_file">CV File (pdf, doc, docx)</label>
            <input type="file" class="form-control-file @error('cv_file') is-invalid @enderror" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx">
            @if($employee->cv_file)
                <small class="form-text text-muted">Current file: <a href="{{ asset('storage/cv/' . $employee->cv_file) }}" target="_blank">View CV</a></small>
            @endif
            @error('cv_file') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
        </div>
    </div>

    <!-- Kolom Kanan: Data Utama -->
    <div class="col-lg-8 col-md-7">
        <div class="row">
            <!-- Detail Personal -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="full_name">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name', $employee->full_name) }}" required>
                    @error('full_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6"
        >
                <div class="form-group">
                    <label>Gender <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center" style="height: 38px;">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="male" value="Laki-laki" {{ old('gender', $employee->gender) == 'Laki-laki' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="male">Laki-laki</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="female" value="Perempuan" {{ old('gender', $employee->gender) == 'Perempuan' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="female">Perempuan</label>
                        </div>
                    </div>
                    @error('gender') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="birth_place">Birth Place <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('birth_place') is-invalid @enderror" name="birth_place" placeholder="City" value="{{ old('birth_place', $employee->birth_place) }}" required>
                    @error('birth_place') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="birth_date">Birth Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('birth_date') is-invalid @enderror" name="birth_date" value="{{ old('birth_date', $employee->birth_date ? $employee->birth_date->format('Y-m-d') : '') }}" required>
                    @error('birth_date') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="religion">Religion <span class="text-danger">*</span></label>
                    <select class="form-control @error('religion') is-invalid @enderror" id="religion" name="religion" required>
                        <option value="">Not Specified</option>
                        <option value="Islam" {{ old('religion', $employee->religion) == 'Islam' ? 'selected' : '' }}>Islam</option>
                        <option value="Kristen" {{ old('religion', $employee->religion) == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                        <option value="Katolik" {{ old('religion', $employee->religion) == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                        <option value="Hindu" {{ old('religion', $employee->religion) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                        <option value="Buddha" {{ old('religion', $employee->religion) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                        <option value="Khonghucu" {{ old('religion', $employee->religion) == 'Khonghucu' ? 'selected' : '' }}>Khonghucu</option>
                    </select>
                    @error('religion') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nik">NIK <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik', $employee->nik) }}" required>
                    @error('nik') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nip">NIP</label>
                    <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip" value="{{ old('nip', $employee->nip) }}">
                    @error('nip') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="npwp">NPWP</label>
                    <input type="text" class="form-control @error('npwp') is-invalid @enderror" id="npwp" name="npwp" value="{{ old('npwp', $employee->npwp) }}">
                    @error('npwp') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="marital_status">Marital Status <span class="text-danger">*</span></label>
                    <select class="form-control @error('marital_status') is-invalid @enderror" name="marital_status" required>
                         @foreach (['Lajang', 'Pernikahan Pertama', 'Pernikahan Kedua', 'Pernikahan Ketiga', 'Cerai Hidup', 'Cerai Mati'] as $status)
                            <option value="{{ $status }}" {{ old('marital_status', $employee->marital_status) == $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('marital_status') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="dependents">Dependents <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('dependents') is-invalid @enderror" name="dependents" value="{{ old('dependents', $employee->dependents) }}" required min="0" placeholder="0">
                    @error('dependents') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="phone_number">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', $employee->phone_number) }}" required>
                    @error('phone_number') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $employee->email) }}" required>
                    @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="status">Status <span class="text-danger">*</span></label>
                    <select class="form-control @error('status') is-invalid @enderror" name="status" required>
                        <option value="Aktif" {{ old('status', $employee->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Tidak Aktif" {{ old('status', $employee->status) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                 <div class="form-group">
                    <label for="employee_type">Employee Type <span class="text-danger">*</span></label>
                    <select class="form-control @error('employee_type') is-invalid @enderror" name="employee_type" required>
                        <option value="Kontrak" {{ old('employee_type', $employee->employee_type) == 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                        <option value="Magang" {{ old('employee_type', $employee->employee_type) == 'Magang' ? 'selected' : '' }}>Magang</option>
                        <option value="Masa Percobaan" {{ old('employee_type', $employee->employee_type) == 'Masa Percobaan' ? 'selected' : '' }}>Masa Percobaan</option>
                        <option value="Fulltime" {{ old('employee_type', $employee->employee_type) == 'Fulltime' ? 'selected' : '' }}>Fulltime</option>
                    </select>
                    @error('employee_type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="division_id">Division</label>
                    <select class="form-control @error('division_id') is-invalid @enderror" name="division_id">
                        <option value="">-- No Division --</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ old('division_id', $employee->division_id) == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                        @endforeach
                    </select>
                    @error('division_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="position_id">Position</label>
                    <select class="form-control @error('position_id') is-invalid @enderror" name="position_id">
                        <option value="">-- No Position --</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" {{ old('position_id', $employee->position_id) == $position->id ? 'selected' : '' }}>{{ $position->title }}</option>
                        @endforeach
                    </select>
                    @error('position_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="hire_date">Date of Entry <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('hire_date') is-invalid @enderror" name="hire_date" value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}" required>
                    @error('hire_date') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="separation_date">Exit Date</label>
                    <input type="date" class="form-control @error('separation_date') is-invalid @enderror" name="separation_date" value="{{ old('separation_date', $employee->separation_date ? $employee->separation_date->format('Y-m-d') : '') }}">
                    @error('separation_date') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                 <div class="form-group">
                    <label for="office">Office <span class="text-danger">*</span></label>
                    <select class="form-control @error('office') is-invalid @enderror" name="office" required>
                        <option value="Kantor Pusat" {{ old('office', $employee->office) == 'Kantor Pusat' ? 'selected' : '' }}>Kantor Pusat</option>
                        <option value="Kantor Cabang" {{ old('office', $employee->office) == 'Kantor Cabang' ? 'selected' : '' }}>Kantor Cabang</option>
                    </select>
                    @error('office') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
             <div class="col-md-6">
                <div class="form-group">
                    <label for="user_id">Connect to User</label>
                    <select class="form-control @error('user_id') is-invalid @enderror" name="user_id">
                        <option value="">-- Not Connected --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden fields for data not in this form tab --}}
<input type="hidden" name="ktp_address" value="{{ $employee->ktp_address ?? 'Default KTP Address' }}">
<input type="hidden" name="current_address" value="{{ $employee->current_address ?? 'Default Current Address' }}">

<div class="row mt-4">
    <div class="col-12">
        <div class="form-buttons-container">
            <a href="{{ route('employees.index') }}" class="btn btn-cancel">Cancel</a>
            <button type="submit" class="btn btn-submit">Update</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photo-preview');
        
        if (photoInput) {
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
@endpush
