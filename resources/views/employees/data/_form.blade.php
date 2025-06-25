{{--
File ini berisi semua elemen form yang digunakan bersama oleh create.blade.php dan edit.blade.php.
Variabel $employee akan ada saat mode edit, dan tidak ada saat mode create.
Helper old('nama_field', $employee->nama_field ?? null) digunakan untuk:
1. Menampilkan data lama jika validasi gagal (old('nama_field')).
2. Menampilkan data dari database saat edit ($employee->nama_field).
3. Menampilkan null (kosong) saat create.
--}}

@csrf
<div class="card-body">
    <div class="row">
        {{-- Kolom Kiri --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="full_name">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name"
                    name="full_name" value="{{ old('full_name', $employee->full_name ?? null) }}" required>
                @error('full_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="nik">NIK <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik"
                    name="nik" value="{{ old('nik', $employee->nik ?? null) }}" required>
                @error('nik')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="nip">NIP</label>
                <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip"
                    name="nip" value="{{ old('nip', $employee->nip ?? null) }}">
                @error('nip')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="npwp">NPWP</label>
                <input type="text" class="form-control @error('npwp') is-invalid @enderror" id="npwp"
                    name="npwp" value="{{ old('npwp', $employee->npwp ?? null) }}">
                @error('npwp')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="gender">Jenis Kelamin <span class="text-danger">*</span></label>
                <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender"
                    required>
                    <option value="" disabled selected>-- Pilih Jenis Kelamin --</option>
                    <option value="Laki-laki"
                        {{ old('gender', $employee->gender ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan"
                        {{ old('gender', $employee->gender ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('gender')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="religion">Agama <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('religion') is-invalid @enderror" id="religion"
                    name="religion" value="{{ old('religion', $employee->religion ?? null) }}" required>
                @error('religion')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="birth_place">Tempat Lahir <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('birth_place') is-invalid @enderror"
                            id="birth_place" name="birth_place"
                            value="{{ old('birth_place', $employee->birth_place ?? null) }}" required>
                        @error('birth_place')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="birth_date">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                            id="birth_date" name="birth_date"
                            value="{{ old('birth_date', isset($employee->birth_date) ? $employee->birth_date->format('Y-m-d') : null) }}"
                            required>
                        @error('birth_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="marital_status">Status Pernikahan <span class="text-danger">*</span></label>
                <select class="form-control @error('marital_status') is-invalid @enderror" id="marital_status"
                    name="marital_status" required>
                    <option value="" disabled selected>-- Pilih Status --</option>
                    @foreach (['Lajang', 'Pernikahan Pertama', 'Pernikahan Kedua', 'Pernikahan Ketiga', 'Cerai Hidup', 'Cerai Mati'] as $status)
                        <option value="{{ $status }}"
                            {{ old('marital_status', $employee->marital_status ?? '') == $status ? 'selected' : '' }}>
                            {{ $status }}</option>
                    @endforeach
                </select>
                @error('marital_status')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="dependents">Jumlah Tanggungan <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('dependents') is-invalid @enderror" id="dependents"
                    name="dependents" value="{{ old('dependents', $employee->dependents ?? 0) }}" required
                    min="0">
                @error('dependents')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="ktp_address">Alamat KTP <span class="text-danger">*</span></label>
                <textarea class="form-control @error('ktp_address') is-invalid @enderror" id="ktp_address" name="ktp_address"
                    rows="3" required>{{ old('ktp_address', $employee->ktp_address ?? null) }}</textarea>
                @error('ktp_address')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="current_address">Alamat Domisili <span class="text-danger">*</span></label>
                <textarea class="form-control @error('current_address') is-invalid @enderror" id="current_address"
                    name="current_address" rows="3" required>{{ old('current_address', $employee->current_address ?? null) }}</textarea>
                @error('current_address')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Kolom Kanan --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="phone_number">Nomor Telepon <span class="text-danger">*</span></label>
                <input type="tel" class="form-control @error('phone_number') is-invalid @enderror"
                    id="phone_number" name="phone_number"
                    value="{{ old('phone_number', $employee->phone_number ?? null) }}" required>
                @error('phone_number')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                    name="email" value="{{ old('email', $employee->email ?? null) }}" required>
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <hr>
            <div class="form-group">
                <label for="status">Status Karyawan <span class="text-danger">*</span></label>
                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status"
                    required>
                    <option value="Aktif"
                        {{ old('status', $employee->status ?? 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Tidak Aktif"
                        {{ old('status', $employee->status ?? '') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif
                    </option>
                </select>
                @error('status')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="employee_type">Tipe Karyawan <span class="text-danger">*</span></label>
                <select class="form-control @error('employee_type') is-invalid @enderror" id="employee_type"
                    name="employee_type" required>
                    <option value="" disabled selected>-- Pilih Tipe --</option>
                    @foreach (['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime'] as $type)
                        <option value="{{ $type }}"
                            {{ old('employee_type', $employee->employee_type ?? '') == $type ? 'selected' : '' }}>
                            {{ $type }}</option>
                    @endforeach
                </select>
                @error('employee_type')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="hire_date">Tanggal Masuk <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('hire_date') is-invalid @enderror"
                            id="hire_date" name="hire_date"
                            value="{{ old('hire_date', isset($employee->hire_date) ? $employee->hire_date->format('Y-m-d') : null) }}"
                            required>
                        @error('hire_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="separation_date">Tanggal Keluar</label>
                        <input type="date" class="form-control @error('separation_date') is-invalid @enderror"
                            id="separation_date" name="separation_date"
                            value="{{ old('separation_date', isset($employee->separation_date) ? $employee->separation_date->format('Y-m-d') : null) }}">
                        @error('separation_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="division_id">Divisi</label>
                <select class="form-control @error('division_id') is-invalid @enderror" id="division_id"
                    name="division_id">
                    <option value="" selected>-- Tidak Ada Divisi --</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}"
                            {{ old('division_id', $employee->division_id ?? '') == $division->id ? 'selected' : '' }}>
                            {{ $division->name }}</option>
                    @endforeach
                </select>
                @error('division_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="position_id">Jabatan</label>
                <select class="form-control @error('position_id') is-invalid @enderror" id="position_id"
                    name="position_id">
                    <option value="" selected>-- Tidak Ada Jabatan --</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}"
                            {{ old('position_id', $employee->position_id ?? '') == $position->id ? 'selected' : '' }}>
                            {{ $position->name }}</option>
                    @endforeach
                </select>
                @error('position_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="user_id">Akun User Terhubung</label>
                <select class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                    <option value="" selected>-- Tidak Terhubung ke Akun Manapun --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}"
                            {{ old('user_id', $employee->user_id ?? '') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Hubungkan data karyawan ini ke akun user untuk login.</small>
                @error('user_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
</div>
<!-- /.card-body -->

<div class="card-footer">
    <button type="submit" class="btn btn-primary">{{ isset($employee) ? 'Update' : 'Simpan' }}</button>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Batal</a>
</div>
