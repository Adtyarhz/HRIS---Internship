<div class="row">
    <div class="col-12">
        <div class="form-group">
            <label for="ktp_address">KTP Address <span class="text-danger">*</span></label>
            <textarea class="form-control @error('ktp_address') is-invalid @enderror" id="ktp_address" name="ktp_address" rows="5" placeholder="Enter address as per ID card" required>{{ old('ktp_address', $employee->ktp_address) }}</textarea>
            @error('ktp_address')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <div class="form-group">
            <label for="current_address">Current Address <span class="text-danger">*</span></label>
            <textarea class="form-control @error('current_address') is-invalid @enderror" id="current_address" name="current_address" rows="5" placeholder="Enter current residential address" required>{{ old('current_address', $employee->current_address) }}</textarea>
            @error('current_address')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

{{-- Tombol Aksi --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="form-buttons-container">
            <a href="{{ route('employees.index') }}" class="btn btn-cancel">Cancel</a>
            <button type="submit" form="updateForm" class="btn btn-submit">Update</button>
        </div>
    </div>
</div>

{{-- Hidden fields for Personal Details --}}
<input type="hidden" name="full_name" value="{{ $employee->full_name ?? 'Default Name' }}">
<input type="hidden" name="gender" value="{{ $employee->gender ?? 'Laki-laki' }}">
<input type="hidden" name="birth_place" value="{{ $employee->birth_place ?? 'Default Birth Place' }}">
<input type="hidden" name="birth_date" value="{{ $employee->birth_date ?? '' }}">
<input type="hidden" name="religion" value="{{ $employee->religion ?? 'Islam' }}">
<input type="hidden" name="nik" value="{{ $employee->nik ?? 'Default NIK' }}">
<input type="hidden" name="nip" value="{{ $employee->nip ?? '' }}">
<input type="hidden" name="npwp" value="{{ $employee->npwp ?? '' }}">
<input type="hidden" name="marital_status" value="{{ $employee->marital_status ?? 'Lajang' }}">
<input type="hidden" name="dependents" value="{{ $employee->dependents ?? 0 }}">
<input type="hidden" name="phone_number" value="{{ $employee->phone_number ?? '0000000000' }}">
<input type="hidden" name="email" value="{{ $employee->email ?? 'default@email.com' }}">
<input type="hidden" name="status" value="{{ $employee->status ?? 'Aktif' }}">
<input type="hidden" name="employee_type" value="{{ $employee->employee_type ?? 'PKWT' }}">
<input type="hidden" name="division_id" value="{{ $employee->division_id ?? '' }}">
<input type="hidden" name="position_id" value="{{ $employee->position_id ?? '' }}">
<input type="hidden" name="hire_date" value="{{ $employee->hire_date ?? '' }}">
<input type="hidden" name="separation_date" value="{{ $employee->separation_date ?? '' }}">
<input type="hidden" name="office" value="{{ $employee->office ?? 'Kantor Pusat' }}">
<input type="hidden" name="user_id" value="{{ $employee->user_id ?? '' }}">
