<div class="form-grid">
    <label class="required">Education Level</label>
    <select name="education_level" class="form-control" required>
        @foreach(['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'] as $level)
            <option value="{{ $level }}" {{ old('education_level', $education?->education_level) == $level ? 'selected' : '' }}>{{ $level }}</option>
        @endforeach
    </select>

    <label class="required">Institution Name</label>
    <input type="text" name="institution_name" class="form-control" required value="{{ old('institution_name', $education?->institution_name) }}">

    <label class="required">Institution Address</label>
    <input type="text" name="institution_address" class="form-control" required value="{{ old('institution_address', $education?->institution_address) }}">

    <label class="required">Major</label>
    <input type="text" name="major" class="form-control" required value="{{ old('major', $education?->major) }}">

    <label class="required">Start Year</label>
    <input type="number" name="start_year" class="form-control" min="1900" max="2099" required value="{{ old('start_year', $education?->start_year) }}">

    <label class="required">End Year</label>
    <input type="number" name="end_year" class="form-control" min="1900" max="2099" required value="{{ old('end_year', $education?->end_year) }}">

    <label class="required">GPA / Score</label>
    <input type="number" name="gpa_or_score" class="form-control" step="0.01" min="0" max="9999.99"
      required value="{{ old('gpa_or_score', $education?->gpa_or_score) }}">

    <label>Certificate Number</label>
    <input type="text" name="certificate_number" class="form-control" value="{{ old('certificate_number', $education?->certificate_number) }}">
</div>
