{{-- Insurance Number --}}
<div class="form-group row align-items-center">
    <label for="insurance_number" class="col-md-2 col-form-label">Insurance Number <span class="text-danger">*</span>
        :</label>
    <div class="col-md-4">
        <input type="text" class="form-control @error('insurance_number') is-invalid @enderror" id="insurance_number"
            name="insurance_number" value="{{ old('insurance_number', $insurance->insurance_number ?? '') }}"
            placeholder="Enter insurance number" required>
        @error('insurance_number')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Insurance Type --}}
<div class="form-group row align-items-center">
    <label for="insurance_type" class="col-md-2 col-form-label">Insurance Type <span class="text-danger">*</span>
        :</label>
    <div class="col-md-3">
        <select id="insurance_type" class="form-control @error('insurance_type') is-invalid @enderror"
            name="insurance_type" required>
            <option value="">-- Select --</option>
            @foreach (['KES', 'TK', 'N-BPJS'] as $type)
                <option value="{{ $type }}"
                    {{ old('insurance_type', $insurance->insurance_type ?? '') === $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
            @endforeach
        </select>
        @error('insurance_type')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Faskes Name --}}
<div class="form-group row align-items-center">
    <label for="faskes_name" class="col-md-2 col-form-label">Faskes Name <span class="text-danger">*</span>:</label>
    <div class="col-md-4">
        <input type="text" 
               class="form-control @error('faskes_name') is-invalid @enderror" 
               id="faskes_name" 
               name="faskes_name"
               value="{{ old('faskes_name', $insurance->faskes_name ?? '') }}"
               placeholder="Enter hospital or clinic name">
        @error('faskes_name')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Faskes Address --}}
<div class="form-group row align-items-start">
    <label for="faskes_address" class="col-md-2 col-form-label">Faskes Address <span class="text-danger">*</span>:</label>
    <div class="col-md-6">
        <textarea class="form-control @error('faskes_address') is-invalid @enderror"
                  id="faskes_address" 
                  name="faskes_address" 
                  rows="2"
                  placeholder="Enter hospital or clinic address">{{ old('faskes_address', $insurance->faskes_address ?? '') }}</textarea>
        @error('faskes_address')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Start Date --}}
<div class="form-group row align-items-center">
    <label for="start_date" class="col-md-2 col-form-label">Start Date <span class="text-danger">*</span> :</label>
    <div class="col-md-3">
        <div class="input-group date-input-group">
            <input type="date" 
    class="form-control @error('start_date') is-invalid @enderror" 
    id="start_date"
    name="start_date"
    value="{{ old('start_date', isset($insurance) && $insurance->start_date ? \Carbon\Carbon::parse($insurance->start_date)->format('Y-m-d') : '') }}"
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

{{-- Expiry Date --}}
<div class="form-group row align-items-center">
    <label for="expiry_date" class="col-md-2 col-form-label">Expiry Date <span class="text-danger">*</span> :</label>
    <div class="col-md-3">
        <div class="input-group date-input-group">
<input type="date" 
    class="form-control @error('expiry_date') is-invalid @enderror" 
    id="expiry_date"
    name="expiry_date"
    value="{{ old('expiry_date', isset($insurance) && $insurance->expiry_date ? \Carbon\Carbon::parse($insurance->expiry_date)->format('Y-m-d') : '') }}"
    required>


            <label for="expiry_date" class="input-group-append">
                <span class="input-group-text">
                    <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                </span>
            </label>
        </div>
        @error('expiry_date')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Status --}}
<div class="form-group row align-items-center">
    <label for="status" class="col-md-2 col-form-label">Status <span class="text-danger">*</span> :</label>
    <div class="col-md-3">
        <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
            @foreach (['AKTIF', 'NONAKTIF'] as $status)
                <option value="{{ $status }}"
                    {{ old('status', $insurance->status ?? '') === $status ? 'selected' : '' }}>
                    {{ ucfirst(strtolower($status)) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Insurance File --}}
<div class="form-group row align-items-center">
    <label for="insurance_file" class="col-md-2 col-form-label">Insurance File :</label>
    <div class="col-md-4">
        <input type="file" name="insurance_file" class="form-control @error('insurance_file') is-invalid @enderror">
        <small class="form-text text-muted">Upload insurance document (PDF, JPG, PNG, max 5MB). Leave empty if you don’t
            want to replace.</small>

        @if (!empty($insurance?->insurance_file))
            <p class="mt-2">Current file:
                <a href="{{ asset('storage/' . $insurance->insurance_file) }}" target="_blank">
                    {{ Str::afterLast($insurance->insurance_file, '_') }}
                </a>
            </p>
            <input type="hidden" name="existing_insurance_file" value="{{ $insurance->insurance_file }}">
        @endif

        @error('insurance_file')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>
