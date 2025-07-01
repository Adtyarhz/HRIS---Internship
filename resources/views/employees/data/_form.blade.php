{{--
    File: _form.blade.php
    Deskripsi:
    File ini telah direstrukturisasi untuk menerapkan gaya CSS kustom dengan positioning absolut.
    - Struktur Bootstrap (.row, .col-*) telah digantikan dengan div yang sesuai dengan desain.
    - Semua logika Blade (old, @error, value, @foreach) tetap dipertahankan untuk fungsionalitas backend.
    - File ini dapat di-include ke dalam view create atau edit.
--}}

@push('styles')
<style>
    /* Styling Dasar untuk Form dalam Konteks Desain Kustom */
    .body-wrapper {
        width: 1175px;
        height: 813px;
        position: relative;
        overflow: hidden;
        margin: auto; /* Pusatkan form jika container lebih besar */
        font-family: 'Montserrat', 'Manrope', sans-serif;
    }

    .kontainer {
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        position: absolute;
        background: #fefef9;
    }

    /* === GAYA LABEL DAN JUDUL === */
    .employee-status, .division, .position, .religion-, .full-name-,
    .nik-number, .nip-number, .npwp-number, .gender, .birth-place-,
    .marital-status-, .dependent, .id-address-, .date-of-entry, .exit-date,
    .connect-to-user {
        position: absolute;
        font-family: 'Montserrat', sans-serif;
        font-weight: 400;
        font-size: 10px;
        color: black;
        line-height: 12px;
    }

    .religion_span_02, .fullname_span_02, .birthplace_span_02,
    .maritalstatus_span_02, .idaddress_span_02, .text-danger {
        color: #9a3b3b;
    }

    /* Posisi Label */
    .full-name- { left: 301px; top: 92px; }
    .nik-number { left: 301px; top: 149px; }
    .id-address- { left: 579px; top: 149px; }
    .nip-number { left: 301px; top: 202px; }
    .npwp-number { left: 579px; top: 202px; }
    .gender { left: 303px; top: 256px; }
    .religion- { left: 301px; top: 311px; }
    .birth-place- { left: 301px; top: 365px; }
    .marital-status- { left: 303px; top: 421px; }
    .dependent { left: 301px; top: 482px; }
    .employee-status { left: 301px; top: 543px; }
    .division { left: 579px; top: 543px; }
    .position { left: 854px; top: 543px; }
    .date-of-entry { left: 301px; top: 601px; }
    .exit-date { left: 606px; top: 601px; }
    .connect-to-user { left: 301px; top: 660px; }


    /* === GAYA WADAH INPUT === */
    .input-wrapper {
        position: absolute;
        overflow: hidden;
        border-radius: 5px;
        outline: 1px rgba(0, 0, 0, 0.2) solid;
        outline-offset: -1px;
        background: white;
    }

    /* Posisi Wadah Input */
    .front-name { width: 450px; height: 21.8px; left: 301px; top: 113.8px; }
    .initial-name { width: 254.2px; height: 21.8px; left: 301px; top: 167.69px; }
    .id-card-number { width: 400px; height: 60px; left: 579px; top: 167.69px; }
    .initial-name_01 { width: 254.2px; height: 21.8px; left: 301px; top: 220.69px; }
    .official-name { width: 254.2px; height: 21.8px; left: 579px; top: 220.69px; }
    .religion { width: 254.2px; height: 21.8px; left: 301px; top: 329.69px; }
    .birth-place { width: 254.2px; height: 21.8px; left: 301px; top: 385.77px; }
    .date-birth { width: 254.2px; height: 21.8px; left: 577.88px; top: 385.77px; }
    .marital { width: 254.2px; height: 21.8px; left: 303px; top: 439.69px; }
    .tax-registered-name { width: 254.2px; height: 21.8px; left: 301px; top: 500.69px; }
    .dialect { width: 254.2px; height: 21.8px; left: 301px; top: 561.69px; }
    .dialect_01 { width: 254.2px; height: 21.8px; left: 579px; top: 561.69px; }
    .position_01 { width: 254.2px; height: 21.8px; left: 854px; top: 561.69px; }
    .date-expiry { width: 254.2px; height: 21.8px; left: 301px; top: 619.69px; }
    .date-expiry_01 { width: 254.2px; height: 21.8px; left: 606px; top: 619.69px; }
    .dialect_02 { width: 254.2px; height: 21.8px; left: 301px; top: 678.69px; }


    /* === GAYA INPUT UNIVERSAL === */
    .custom-form-input {
        width: 100%;
        height: 100%;
        border: none;
        background: transparent;
        padding: 0 8px;
        font-size: 10px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 400;
        color: black;
        box-sizing: border-box;
    }
    .custom-form-input:focus {
        outline: none;
    }
    /* Khusus untuk Select/Dropdown */
    select.custom-form-input {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        cursor: pointer;
    }
    /* Khusus untuk Textarea */
    textarea.custom-form-input {
        padding-top: 5px;
        resize: none;
    }
    /* Placeholder text */
    .custom-form-input::placeholder {
        color: rgba(0, 0, 0, 0.3);
    }
    /* Menghilangkan panah di input number */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    .invalid-feedback {
        position: absolute;
        font-size: 9px;
        color: #9a3b3b;
        margin-top: 1px;
    }


    /* === GAYA ELEMEN LAINNYA === */
    .profil-karyawan {
        width: 208.43px;
        height: 219.08px;
        left: 49px;
        top: 92.38px;
        position: absolute;
        box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.25);
        border-radius: 10px;
        border: 1px rgba(0, 0, 0, 0.15) solid;
        object-fit: cover; /* Agar gambar tidak gepeng */
    }

    /* Radio Button */
    .gender-option { position: absolute; display: flex; align-items: center; cursor: pointer; }
    .male { left: 328px; top: 278px; }
    .female { left: 400px; top: 278px; }
    .gender-option .label-text { font-size: 10px; font-family: 'Manrope', sans-serif; margin-left: 5px; }
    .radio-custom {
        width: 8.17px; height: 8.31px; border-radius: 50%; border: 1px solid #121212; display: inline-block; position: relative;
    }
    .radio-custom .marker {
        width: 3.41px; height: 3.46px; border-radius: 50%; background: white; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); display: none;
    }
    input[type="radio"]:checked + .radio-custom { background: #3da5df; }
    input[type="radio"]:checked + .radio-custom .marker { display: block; }
    input[type="radio"] { opacity: 0; width: 0; height: 0; position: absolute; }


    /* Tombol */
    .btn-submit, .btn-cancel { position: absolute; width: 110px; height: 37px; border-radius: 5px; border: none; cursor: pointer; }
    .btn-submit { left: 1031px; top: 721px; background: #367fa9; }
    .btn-cancel { left: 912px; top: 721px; background: #9a3b3b; display: flex; justify-content: center; align-items: center; text-decoration: none; }
    .btn-submit span, .btn-cancel span { color: white; font-size: 12px; font-family: 'Montserrat', sans-serif; font-weight: 400; }

    /* Ikon Dropdown (Dekoratif) */
    .dropdown-arrow {
        position: absolute;
        width: 10.4px;
        height: 6.15px;
        background: rgba(0, 0, 0, 0.6);
        pointer-events: none; /* Agar tidak menghalangi klik pada select */
    }
    .lsicondown-filled { top: 569px; left: 532px; }
    .lsicondown-filled_01 { top: 569px; left: 810px; }
    .lsicondown-filled_02 { top: 569px; left: 1086px; }
    .lsicondown-filled_03 { top: 337px; left: 532px; }
    .lsicondown-filled_04 { top: 447px; left: 534px; }
    .lsicondown-filled_05 { top: 686px; left: 532px; }

</style>
@endpush

@csrf
<div class="body-wrapper">

    {{-- FOTO PROFIL --}}
    <img class="profil-karyawan" src="{{ asset('img/profile_dummy.png') }}" alt="Foto Profil" />
    {{-- TODO: Tambahkan input file jika diperlukan --}}
    {{-- <input type="file" name="photo" style="position:absolute; top:320px; left: 49px;"> --}}

    {{-- NAMA LENGKAP --}}
    <div class="full-name-">
        <label for="full_name">Nama Lengkap <span class="fullname_span_02">*</span></label>
    </div>
    <div class="input-wrapper front-name">
        <input type="text" class="custom-form-input @error('full_name') is-invalid @enderror" id="full_name"
               name="full_name" value="{{ old('full_name', $employee->full_name ?? null) }}" required
               placeholder="Masukkan nama lengkap">
    </div>
    @error('full_name') <span class="invalid-feedback" style="left: 301px; top: 137px;">{{ $message }}</span> @enderror

    {{-- NIK --}}
    <div class="nik-number">
        <label for="nik">NIK Number <span class="text-danger">*</span></label>
    </div>
    <div class="input-wrapper initial-name">
        <input type="text" class="custom-form-input @error('nik') is-invalid @enderror" id="nik"
               name="nik" value="{{ old('nik', $employee->nik ?? null) }}" required>
    </div>
    @error('nik') <span class="invalid-feedback" style="left: 301px; top: 191px;">{{ $message }}</span> @enderror

    {{-- ALAMAT KTP --}}
    <div class="id-address-">
        <label for="ktp_address">Alamat KTP <span class="idaddress_span_02">*</span></label>
    </div>
    <div class="input-wrapper id-card-number">
        <textarea class="custom-form-input @error('ktp_address') is-invalid @enderror" id="ktp_address" name="ktp_address" required>{{ old('ktp_address', $employee->ktp_address ?? null) }}</textarea>
    </div>
    @error('ktp_address') <span class="invalid-feedback" style="left: 579px; top: 230px;">{{ $message }}</span> @enderror

    {{-- NIP --}}
    <div class="nip-number">
        <label for="nip">NIP Number</label>
    </div>
    <div class="input-wrapper initial-name_01">
        <input type="text" class="custom-form-input @error('nip') is-invalid @enderror" id="nip"
               name="nip" value="{{ old('nip', $employee->nip ?? null) }}">
    </div>
    @error('nip') <span class="invalid-feedback" style="left: 301px; top: 244px;">{{ $message }}</span> @enderror

    {{-- NPWP --}}
    <div class="npwp-number">
        <label for="npwp">NPWP Number</label>
    </div>
    <div class="input-wrapper official-name">
        <input type="text" class="custom-form-input @error('npwp') is-invalid @enderror" id="npwp"
               name="npwp" value="{{ old('npwp', $employee->npwp ?? null) }}">
    </div>
    @error('npwp') <span class="invalid-feedback" style="left: 579px; top: 244px;">{{ $message }}</span> @enderror

    {{-- JENIS KELAMIN --}}
    <div class="gender"><span>Gender <span class="text-danger">*</span></span></div>
    <label class="gender-option male">
        <input type="radio" name="gender" value="Laki-laki" {{ old('gender', $employee->gender ?? '') == 'Laki-laki' ? 'checked' : '' }} required>
        <span class="radio-custom"><span class="marker"></span></span>
        <span class="label-text">Male</span>
    </label>
    <label class="gender-option female">
        <input type="radio" name="gender" value="Perempuan" {{ old('gender', $employee->gender ?? '') == 'Perempuan' ? 'checked' : '' }} required>
        <span class="radio-custom"><span class="marker"></span></span>
        <span class="label-text">Female</span>
    </label>
    @error('gender') <span class="invalid-feedback" style="left: 303px; top: 298px;">{{ $message }}</span> @enderror

    {{-- AGAMA --}}
    <div class="religion-">
        <label for="religion">Religion <span class="religion_span_02">*</span></label>
    </div>
    <div class="input-wrapper religion">
        <input type="text" class="custom-form-input @error('religion') is-invalid @enderror" id="religion"
               name="religion" value="{{ old('religion', $employee->religion ?? null) }}" required>
    </div>
    @error('religion') <span class="invalid-feedback" style="left: 301px; top: 353px;">{{ $message }}</span> @enderror

    {{-- TEMPAT & TANGGAL LAHIR --}}
    <div class="birth-place-">
        <label for="birth_place">Birth Place & Date <span class="birthplace_span_02">*</span></label>
    </div>
    <div class="input-wrapper birth-place">
        <input type="text" class="custom-form-input @error('birth_place') is-invalid @enderror" id="birth_place"
               name="birth_place" value="{{ old('birth_place', $employee->birth_place ?? null) }}" required>
    </div>
    <div class="input-wrapper date-birth">
        <input type="date" class="custom-form-input @error('birth_date') is-invalid @enderror" id="birth_date"
               name="birth_date" value="{{ old('birth_date', isset($employee->birth_date) ? $employee->birth_date->format('Y-m-d') : null) }}" required>
    </div>
    @error('birth_place') <span class="invalid-feedback" style="left: 301px; top: 409px;">{{ $message }}</span> @enderror
    @error('birth_date') <span class="invalid-feedback" style="left: 578px; top: 409px;">{{ $message }}</span> @enderror

    {{-- STATUS PERNIKAHAN --}}
    <div class="marital-status-">
        <label for="marital_status">Marital Status <span class="maritalstatus_span_02">*</span></label>
    </div>
    <div class="input-wrapper marital">
        <select class="custom-form-input @error('marital_status') is-invalid @enderror" id="marital_status" name="marital_status" required>
            <option value="" disabled {{ old('marital_status', $employee->marital_status ?? '') == '' ? 'selected' : '' }}>-- Pilih Status --</option>
            @foreach (['Lajang', 'Pernikahan Pertama', 'Pernikahan Kedua', 'Pernikahan Ketiga', 'Cerai Hidup', 'Cerai Mati'] as $status)
                <option value="{{ $status }}" {{ old('marital_status', $employee->marital_status ?? '') == $status ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="dropdown-arrow lsicondown-filled_04"></div>
    @error('marital_status') <span class="invalid-feedback" style="left: 303px; top: 463px;">{{ $message }}</span> @enderror

    {{-- TANGGUNGAN --}}
    <div class="dependent">
        <label for="dependents">Dependent</label>
    </div>
    <div class="input-wrapper tax-registered-name">
        <input type="number" class="custom-form-input @error('dependents') is-invalid @enderror" id="dependents"
               name="dependents" value="{{ old('dependents', $employee->dependents ?? 0) }}" required min="0">
    </div>
    @error('dependents') <span class="invalid-feedback" style="left: 301px; top: 524px;">{{ $message }}</span> @enderror

    {{-- STATUS KARYAWAN --}}
    <div class="employee-status">
        <label for="status">Employee Status</label>
    </div>
    <div class="input-wrapper dialect">
        <select class="custom-form-input @error('status') is-invalid @enderror" id="status" name="status" required>
            <option value="Aktif" {{ old('status', $employee->status ?? 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="Tidak Aktif" {{ old('status', $employee->status ?? '') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
    </div>
    <div class="dropdown-arrow lsicondown-filled"></div>
    @error('status') <span class="invalid-feedback" style="left: 301px; top: 585px;">{{ $message }}</span> @enderror

    {{-- DIVISI --}}
    <div class="division">
        <label for="division_id">Division</label>
    </div>
    <div class="input-wrapper dialect_01">
        <select class="custom-form-input @error('division_id') is-invalid @enderror" id="division_id" name="division_id">
            <option value="">-- Tidak Ada Divisi --</option>
            @foreach ($divisions as $division)
                <option value="{{ $division->id }}" {{ old('division_id', $employee->division_id ?? '') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dropdown-arrow lsicondown-filled_01"></div>
    @error('division_id') <span class="invalid-feedback" style="left: 579px; top: 585px;">{{ $message }}</span> @enderror

    {{-- JABATAN --}}
    <div class="position">
        <label for="position_id">Position</label>
    </div>
    <div class="input-wrapper position_01">
        <select class="custom-form-input @error('position_id') is-invalid @enderror" id="position_id" name="position_id">
            <option value="">-- Tidak Ada Jabatan --</option>
            @foreach ($positions as $position)
                <option value="{{ $position->id }}" {{ old('position_id', $employee->position_id ?? '') == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dropdown-arrow lsicondown-filled_02"></div>
    @error('position_id') <span class="invalid-feedback" style="left: 854px; top: 585px;">{{ $message }}</span> @enderror

    {{-- TANGGAL MASUK & KELUAR --}}
    <div class="date-of-entry">
        <label for="hire_date">Date of Entry <span class="text-danger">*</span></label>
    </div>
    <div class="input-wrapper date-expiry">
        <input type="date" class="custom-form-input @error('hire_date') is-invalid @enderror" id="hire_date" name="hire_date"
               value="{{ old('hire_date', isset($employee->hire_date) ? $employee->hire_date->format('Y-m-d') : null) }}" required>
    </div>
    @error('hire_date') <span class="invalid-feedback" style="left: 301px; top: 643px;">{{ $message }}</span> @enderror

    <div class="exit-date">
        <label for="separation_date">Exit Date</label>
    </div>
    <div class="input-wrapper date-expiry_01">
        <input type="date" class="custom-form-input @error('separation_date') is-invalid @enderror" id="separation_date" name="separation_date"
               value="{{ old('separation_date', isset($employee->separation_date) ? $employee->separation_date->format('Y-m-d') : null) }}">
    </div>
    @error('separation_date') <span class="invalid-feedback" style="left: 606px; top: 643px;">{{ $message }}</span> @enderror

    {{-- AKUN USER TERHUBUNG --}}
    <div class="connect-to-user">
        <label for="user_id">Connect to User</label>
    </div>
    <div class="input-wrapper dialect_02">
        <select class="custom-form-input @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
            <option value="">-- Tidak Terhubung --</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ old('user_id', $employee->user_id ?? '') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="dropdown-arrow lsicondown-filled_05"></div>
    @error('user_id') <span class="invalid-feedback" style="left: 301px; top: 702px;">{{ $message }}</span> @enderror

</div>
{{-- /.body-wrapper --}}


{{-- Tombol diletakkan di luar .body-wrapper agar tidak terpengaruh oleh positioning --}}
<div style="position: relative; width: 1175px; margin: auto;">
    <button type="submit" class="btn-submit">
        <span>{{ isset($employee) ? 'Update' : 'Simpan' }}</span>
    </button>
    <a href="{{ route('employees.index') }}" class="btn-cancel">
        <span>Batal</span>
    </a>
</div>

{{-- Memasukkan field yang tidak ada di desain agar controller tetap bekerja --}}
<div style="display: none;">
    <label for="current_address">Alamat Domisili</label>
    <textarea name="current_address">{{ old('current_address', $employee->current_address ?? 'Default Address') }}</textarea>

    <label for="phone_number">Nomor Telepon</label>
    <input type="tel" name="phone_number" value="{{ old('phone_number', $employee->phone_number ?? '081234567890') }}">

    <label for="email">Email</label>
    <input type="email" name="email" value="{{ old('email', $employee->email ?? 'default@example.com') }}">
    
    <label for="employee_type">Tipe Karyawan</label>
    <input type="text" name="employee_type" value="{{ old('employee_type', $employee->employee_type ?? 'Fulltime') }}">
</div>
