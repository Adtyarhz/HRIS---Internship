{{-- Company Name --}}
<div class="form-group row align-items-center">
    <label for="company_name" class="col-md-2 col-form-label">
        Company Name <span class="text-danger">*</span> :
    </label>
    <div class="col-md-3">
        <input type="text" id="company_name" name="company_name"
            class="form-control @error('company_name') is-invalid @enderror"
            value="{{ old('company_name', $workExperience?->company_name) }}"
            placeholder="e.g., PT BPR Daya Perdana Nusantara" required>
        @error('company_name')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Company Address --}}
<div class="form-group row align-items-center">
    <label for="company_address" class="col-md-2 col-form-label">
        Company Address <span class="text-danger">*</span> :
    </label>
    <div class="col-md-4">
        <input type="text" id="company_address" name="company_address"
            class="form-control @error('company_address') is-invalid @enderror"
            value="{{ old('company_address', $workExperience?->company_address) }}"
            placeholder="e.g., Jl. Raya Bogor, Mekarsari, Cimanggis District, Depok City">
        @error('company_address')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Company Phone --}}
<div class="form-group row align-items-center">
    <label for="company_phone" class="col-md-2 col-form-label">
        Company Phone <span class="text-danger">*</span> :
    </label>
    <div class="col-md-3">
        <input type="text" id="company_phone" name="company_phone"
            class="form-control @error('company_phone') is-invalid @enderror"
            value="{{ old('company_phone', $workExperience?->company_phone) }}" placeholder="e.g., (021) 8720479">
        @error('company_phone')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Position Title --}}
<div class="form-group row align-items-center">
    <label for="position_title" class="col-md-2 col-form-label">
        Position Title <span class="text-danger">*</span> :
    </label>
    <div class="col-md-3">
        <input type="text" id="position_title" name="position_title"
            class="form-control @error('position_title') is-invalid @enderror"
            value="{{ old('position_title', $workExperience?->position_title) }}" placeholder="e.g., Software Engineer"
            required>
        @error('position_title')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Start Date --}}
<div class="form-group row align-items-center">
    <label for="start_date" class="col-md-2 col-form-label">
        Start Date <span class="text-danger">*</span> :
    </label>
    <div class="col-md-2">
        <div class="input-group date-input-group">
            <input
                type="date"
                id="start_date"
                name="start_date"
                class="form-control @error('start_date') is-invalid @enderror"
                value="{{ old('start_date', optional($workExperience?->start_date ? \Carbon\Carbon::parse($workExperience->start_date) : null)->format('Y-m-d')) }}"
                required>
            <label for="start_date" class="input-group-append">
                <span class="input-group-text">
                    <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                </span>
            </label>
        </div>
        @error('start_date')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- End Date --}}
<div class="form-group row align-items-center">
    <label for="end_date" class="col-md-2 col-form-label">
        End Date <span class="text-danger">*</span> :
    </label>
    <div class="col-md-2">
        <div class="input-group date-input-group">
            <input
                type="date"
                id="end_date"
                name="end_date"
                class="form-control @error('end_date') is-invalid @enderror"
                value="{{ old('end_date', optional($workExperience?->end_date ? \Carbon\Carbon::parse($workExperience->end_date) : null)->format('Y-m-d')) }}">
            <label for="end_date" class="input-group-append">
                <span class="input-group-text">
                    <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                </span>
            </label>
        </div>
        @error('end_date')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Responsibilities --}}
<div class="form-group row align-items-start">
    <label for="responsibilities" class="col-md-2 col-form-label">
        Responsibilities <span class="text-danger">*</span> :
    </label>
    <div class="col-md-4">
        <textarea id="responsibilities" name="responsibilities"
            class="form-control @error('responsibilities') is-invalid @enderror" rows="4"
            placeholder="Describe your main responsibilities and tasks">{{ old('responsibilities', $workExperience?->responsibilities) }}</textarea>
        @error('responsibilities')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Reason to Leave --}}
<div class="form-group row align-items-start">
    <label for="reason_to_leave" class="col-md-2 col-form-label">
        Reason to Leave <span class="text-danger">*</span> :
    </label>
    <div class="col-md-4">
        <textarea id="reason_to_leave" name="reason_to_leave"
            class="form-control @error('reason_to_leave') is-invalid @enderror" rows="4"
            placeholder="e.g., Career advancement, relocation, better opportunity">{{ old('reason_to_leave', $workExperience?->reason_to_leave) }}</textarea>
        @error('reason_to_leave')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Last Salary --}}
<div class="form-group row align-items-center">
    <label for="last_salary" class="col-md-2 col-form-label">
        Last Salary <span class="text-danger">*</span> :
    </label>
    <div class="col-md-3">
        <div class="input-group">
            <input type="text" id="formatted_salary" class="form-control" placeholder="e.g., 10000000,00"
                value="{{ old('last_salary', $workExperience?->last_salary)
                    ? number_format(old('last_salary', $workExperience?->last_salary), 2, ',', '.')
                    : '' }}">
            <input type="hidden" name="last_salary" id="raw_salary"
                value="{{ old('last_salary', $workExperience?->last_salary) }}">

            <div class="input-group-append">
                <span class="input-group-text">IDR</span>
            </div>
        </div>
        @error('last_salary')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>


{{-- Reference Letter --}}
<div class="form-group row align-items-center">
    <label for="reference_letter_file" class="col-md-2 col-form-label">
        Reference Letter <span class="text-danger">*</span> :
    </label>
    <div class="col-md-4">
        <input type="file" name="reference_letter_file"
            class="form-control @error('reference_letter_file') is-invalid @enderror"
            onchange="updateFileName(this, 'ref_filename')">
        <span class="file-upload-name text-primary" id="ref_filename"></span>
        @if (!empty($workExperience?->reference_letter_file))
            <a href="{{ asset('storage/' . $workExperience->reference_letter_file) }}" target="_blank"
                class="d-block mt-1">
                {{ Str::afterLast($workExperience->reference_letter_file, '_') }}
            </a>
        @endif
        @error('reference_letter_file')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Salary Slip --}}
<div class="form-group row align-items-center">
    <label for="salary_slip_file" class="col-md-2 col-form-label">
        Salary Slip <span class="text-danger">*</span> :
    </label>
    <div class="col-md-4">
        <input type="file" name="salary_slip_file"
            class="form-control @error('salary_slip_file') is-invalid @enderror"
            onchange="updateFileName(this, 'slip_filename')">
        <span class="file-upload-name text-primary" id="slip_filename"></span>
        @if (!empty($workExperience?->salary_slip_file))
            <a href="{{ asset('storage/' . $workExperience->salary_slip_file) }}" target="_blank"
                class="d-block mt-1">
                {{ Str::afterLast($workExperience->salary_slip_file, '_') }}
            </a>
        @endif
        @error('salary_slip_file')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cleave = new Cleave('#formatted_salary', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.',
                numeralDecimalScale: 2,
                rawValueTrimPrefix: true,
            });

            const rawInput = document.getElementById('raw_salary');
            const formattedInput = document.getElementById('formatted_salary');

            formattedInput.addEventListener('input', function() {
                rawInput.value = cleave.getRawValue();
            });

            rawInput.value = cleave.getRawValue();
        });

        function updateFileName(input, targetId) {
            const fileName = input.files.length > 0 ? input.files[0].name : '';
            document.getElementById(targetId).textContent = fileName;
        }
    </script>
@endpush
