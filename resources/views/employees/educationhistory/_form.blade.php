{{-- Education Level --}}
<div class="form-group row align-items-center">
    <label for="education_level" class="col-md-2 col-form-label">Education Level <span class="text-danger">*</span> :</label>
    <div class="col-md-3">
        <select name="education_level" id="education_level" class="form-control @error('education_level') is-invalid @enderror" required>
            <option value="">-- Select Level --</option>
            @foreach(['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'] as $level)
                <option value="{{ $level }}" {{ old('education_level', $education?->education_level) == $level ? 'selected' : '' }}>{{ $level }}</option>
            @endforeach
        </select>
        @error('education_level')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Institution Name --}}
<div class="form-group row align-items-center">
    <label for="institution_name" class="col-md-2 col-form-label">Institution Name <span class="text-danger">*</span> :</label>
    <div class="col-md-4">
        <input type="text" id="institution_name" name="institution_name"
            class="form-control @error('institution_name') is-invalid @enderror"
            value="{{ old('institution_name', $education?->institution_name) }}"
            placeholder="Enter institution name" required>
        @error('institution_name')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Institution Address --}}
<div class="form-group row align-items-center">
    <label for="institution_address" class="col-md-2 col-form-label">Institution Address <span class="text-danger">*</span> :</label>
    <div class="col-md-4">
        <input type="text" id="institution_address" name="institution_address"
            class="form-control @error('institution_address') is-invalid @enderror"
            value="{{ old('institution_address', $education?->institution_address) }}"
            placeholder="Enter institution address" required>
        @error('institution_address')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Major --}}
<div class="form-group row align-items-center">
    <label for="major" class="col-md-2 col-form-label">Major <span class="text-danger">*</span> :</label>
    <div class="col-md-3">
        <input type="text" id="major" name="major"
            class="form-control @error('major') is-invalid @enderror"
            value="{{ old('major', $education?->major) }}"
            placeholder="Enter major" required>
        @error('major')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Start Year --}}
<div class="form-group row align-items-center">
    <label for="start_year" class="col-md-2 col-form-label">Start Year <span class="text-danger">*</span> :</label>
    <div class="col-md-2">
        <input type="number" id="start_year" name="start_year"
            class="form-control @error('start_year') is-invalid @enderror"
            min="1900" max="2099"
            value="{{ old('start_year', $education?->start_year) }}"
            placeholder="YYYY" required>
        @error('start_year')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- End Year --}}
<div class="form-group row align-items-center">
    <label for="end_year" class="col-md-2 col-form-label">End Year <span class="text-danger">*</span> :</label>
    <div class="col-md-2">
        <input type="number" id="end_year" name="end_year"
            class="form-control @error('end_year') is-invalid @enderror"
            min="1900" max="2099"
            value="{{ old('end_year', $education?->end_year) }}"
            placeholder="YYYY" required>
        @error('end_year')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- GPA / Score --}}
<div class="form-group row align-items-center">
    <label for="gpa_or_score" class="col-md-2 col-form-label">GPA / Score <span class="text-danger">*</span> :</label>
    <div class="col-md-2">
        <input type="number" id="gpa_or_score" name="gpa_or_score"
            class="form-control @error('gpa_or_score') is-invalid @enderror"
            step="0.01" min="0" max="9999.99"
            value="{{ old('gpa_or_score', $education?->gpa_or_score) }}"
            placeholder="e.g., 3.75 or 90" required>
        @error('gpa_or_score')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Certificate Number --}}
<div class="form-group row align-items-center">
    <label for="certificate_number" class="col-md-2 col-form-label">Certificate Number :</label>
    <div class="col-md-3">
        <input type="text" id="certificate_number" name="certificate_number"
            class="form-control @error('certificate_number') is-invalid @enderror"
            value="{{ old('certificate_number', $education?->certificate_number) }}"
            placeholder="Enter certificate number (if any)">
        @error('certificate_number')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>
