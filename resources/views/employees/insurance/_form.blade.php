@push('styles')
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
@endpush

<div class="form-grid">
    {{-- Insurance Number --}}
    <label class="required">Insurance Number</label>
    <input type="text" name="insurance_number" class="form-control" required
        value="{{ old('insurance_number', $insurance->insurance_number ?? '') }}">

    {{-- Insurance Type --}}
    <label class="required">Insurance Type</label>
    <select name="insurance_type" class="form-control" required>
        <option value="">-- Select --</option>
        @foreach(['KES', 'TK', 'N-BPJS'] as $type)
            <option value="{{ $type }}" {{ old('insurance_type', $insurance->insurance_type ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
        @endforeach
    </select>

    {{-- Start Date --}}
    <label class="required">Start Date</label>
    <input type="date" name="start_date" class="form-control" required
        value="{{ old('start_date', optional($insurance?->start_date)->format('Y-m-d')) }}">

    {{-- Expiry Date --}}
    <label class="required">Expiry Date</label>
    <input type="date" name="expiry_date" class="form-control" required
        value="{{ old('expiry_date', optional($insurance?->expiry_date)->format('Y-m-d')) }}">

    {{-- Status --}}
    <label class="required">Status</label>
    <select name="status" class="form-control" required>
        @foreach(['AKTIF', 'NONAKTIF'] as $status)
            <option value="{{ $status }}" {{ old('status', $insurance->status ?? '') === $status ? 'selected' : '' }}>{{ ucfirst(strtolower($status)) }}</option>
        @endforeach
    </select>

    {{-- Insurance File --}}
    <label>Insurance File</label>
    <div class="file-upload-wrapper">
        <i class="fa-regular fa-file"></i>
        <input type="file" name="insurance_file" onchange="updateFileName(this, 'insurance_filename')">
        <span class="file-upload-name" id="insurance_filename"></span>
        @if (!empty($insurance?->insurance_file))
            <a href="{{ asset('storage/' . $insurance->insurance_file) }}" target="_blank">
                {{ Str::afterLast($insurance->insurance_file, '_') }}
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
