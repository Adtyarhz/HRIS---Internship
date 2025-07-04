<style>
    .form-grid {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 12px 20px;
        max-width: 600px;
    }

    .form-grid label {
        font-size: 12px;
        font-weight: 500;
        font-family: 'Noto Sans Georgian', sans-serif;
        margin-bottom: 4px;
    }

    .form-grid .required::after {
        content: '*';
        color: red;
        margin-left: 2px;
    }

    .form-control {
        height: 30px;
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 5px;
        border: 1px solid rgba(0, 0, 0, 0.2);
    }

    textarea.form-control {
        height: auto;
        min-height: 80px;
    }

    .salary-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .salary-currency {
        background: #F2F2F2;
        padding: 6px 10px;
        border-radius: 5px;
        font-weight: 700;
        font-family: Montserrat, sans-serif;
        font-size: 13px;
        border: 1px solid rgba(0, 0, 0, 0.2);
    }

    .file-upload-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        background-color: #fefef9;
        padding: 5px 10px;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 6px;
    }

    .file-upload-wrapper i {
        color: black;
        font-size: 14px;
    }

    .file-upload-wrapper input[type="file"] {
        font-size: 12px;
        border: none;
        outline: none;
        background: none;
        padding: 0;
        margin: 0;
        height: 25px;
        line-height: 1;
        width: 100px;
        cursor: pointer;
    }

    .file-upload-name {
        font-size: 12px;
        color: #225E7F;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }
</style>

<div class="form-grid">

    {{-- Company Name --}}
    <label class="required">Company Name</label>
    <input type="text" name="company_name" class="form-control" required
        value="{{ old('company_name', $workExperience?->company_name) }}">

    {{-- Company Address --}}
    <label class="required">Company Address</label>
    <input type="text" name="company_address" class="form-control"
        value="{{ old('company_address', $workExperience?->company_address) }}">

    {{-- Company Phone --}}
    <label class="required">Company Phone</label>
    <input type="text" name="company_phone" class="form-control"
        value="{{ old('company_phone', $workExperience?->company_phone) }}">

    {{-- Position Title --}}
    <label class="required">Position Title</label>
    <input type="text" name="position_title" class="form-control" required
        value="{{ old('position_title', $workExperience?->position_title) }}">

    {{-- Start Date --}}
    <label class="required">Start Date</label>
    <input type="date" name="start_date" class="form-control"
        value="{{ old('start_date', optional($workExperience?->start_date)->format('Y-m-d')) }}">

    {{-- End Date --}}
    <label class="required">End Date</label>
    <input type="date" name="end_date" class="form-control"
        value="{{ old('end_date', optional($workExperience?->end_date)->format('Y-m-d')) }}">

    {{-- Responsibilities --}}
    <label class="required">Responsibilities</label>
    <textarea name="responsibilities" class="form-control">{{ old('responsibilities', $workExperience?->responsibilities) }}</textarea>

    {{-- Reason to Leave --}}
    <label class="required">Reason to Leave</label>
    <textarea name="reason_to_leave" class="form-control">{{ old('reason_to_leave', $workExperience?->reason_to_leave) }}</textarea>

    {{-- Last Salary --}}
    <label class="required">Last Salary</label>
    <div class="salary-wrapper">
        <input type="text" id="formatted_salary" class="form-control" placeholder="Rp 0,00"
            value="{{ number_format(old('last_salary', $workExperience?->last_salary ?? 0), 2, ',', '.') }}">
        <input type="hidden" name="last_salary" id="raw_salary"
            value="{{ old('last_salary', $workExperience?->last_salary) }}">
        <span class="salary-currency">IDR</span>
    </div>

    {{-- Reference Letter --}}
    <label class="required">Reference Letter File</label>
    <div class="file-upload-wrapper">
        <i class="fa-regular fa-file"></i>
        <input type="file" name="reference_letter_file" onchange="updateFileName(this, 'ref_filename')">
        <span class="file-upload-name" id="ref_filename">
        </span>
        <a href="{{ asset('storage/'.$workExperience?->reference_letter_file) }}" target="_blank">
        {{ Str::afterLast($workExperience?->reference_letter_file, '_') }}</a>
    </div>

    {{-- Salary Slip --}}
    <label class="required">Salary Slip File</label>
    <div class="file-upload-wrapper">
        <i class="fa-regular fa-file"></i>
        <input type="file" name="salary_slip_file" onchange="updateFileName(this, 'slip_filename')">
        <span class="file-upload-name" id="slip_filename">
        </span>
        <a href="{{ asset('storage/'.$workExperience?->salary_slip_file) }}" target="_blank">
        {{ Str::afterLast($workExperience?->salary_slip_file, '_') }}</a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
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

        formattedInput.addEventListener('input', function () {
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
