@push('styles')
<style>
    .form-grid {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 12px 20px;
        max-width: 700px;
    }

    .form-grid label {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .form-grid .required::after {
        content: '*';
        color: red;
    }

    .form-control {
        height: 30px;
        font-size: 13px;
        padding: 6px 12px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .file-upload-wrapper {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .file-upload-name {
        font-size: 12px;
        color: #225E7F;
    }
</style>
@endpush

<div class="form-grid">
    <label class="required">Full Name</label>
    <input type="text" name="full_name" class="form-control" required value="{{ old('full_name', $applicant->full_name ?? '') }}">

    <label class="required">Email</label>
    <input type="email" name="email" class="form-control" required value="{{ old('email', $applicant->email ?? '') }}">

    <label class="required">Phone</label>
    <input type="text" name="phone" class="form-control" required value="{{ old('phone', $applicant->phone ?? '') }}">

    <label class="required">Address</label>
    <input type="text" name="address" class="form-control" required value="{{ old('address', $applicant->address ?? '') }}">

    <label class="required">Applied Position</label>
    <input type="text" name="applied_position" class="form-control" required value="{{ old('applied_position', $applicant->applied_position ?? '') }}">

    <label>Last Education</label>
    <input type="text" name="last_education" class="form-control" value="{{ old('last_education', $applicant->last_education ?? '') }}">

    <label>Origin</label>
    <input type="text" name="origin" class="form-control" value="{{ old('origin', $applicant->origin ?? '') }}">

    <label>GPA Score</label>
    <input type="number" step="0.01" name="gpa_score" class="form-control" value="{{ old('gpa_score', $applicant->gpa_score ?? '') }}">

    <label>Division</label>
    <select name="division_id" class="form-control">
        <option value="">-- Select Division --</option>
        @foreach ($divisions as $division)
            <option value="{{ $division->id }}" {{ old('division_id', $applicant->division_id ?? '') == $division->id ? 'selected' : '' }}>
                {{ $division->name }}
            </option>
        @endforeach
    </select>

    <label>Resume (PDF)</label>
    <div class="file-upload-wrapper">
        <input type="file" name="resume_file" onchange="updateFileName(this, 'resume_filename')">
        @if (!empty($applicant?->resume_file))
            <a href="{{ asset('storage/' . $applicant->resume_file) }}" target="_blank">
                {{ Str::afterLast($applicant->resume_file, '_') }}
            </a>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function updateFileName(input, targetId) {
        const fileName = input.files.length > 0 ? input.files[0].name : '';
        document.getElementById(targetId).textContent = fileName;
    }
</script>
@endpush
