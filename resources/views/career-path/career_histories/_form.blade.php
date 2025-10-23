@props(['employee', 'positions', 'divisions', 'careerHistory' => null])

<div class="row">
    <div class="col-12">
        {{-- Position --}}
        <div class="form-group row align-items-center">
            <label for="position_id" class="col-md-2 col-form-label">Position <span class="text-danger">*</span> :
            </label>
            <div class="col-md-3">
                <select name="position_id" id="position_id"
                    class="form-control @error('position_id') is-invalid @enderror" required>
                    <option value="">Select Position</option>
                    @foreach ($positions as $pos)
                        <option value="{{ $pos->id }}" data-division="{{ $pos->division->name ?? '-' }}"
                            data-division-id="{{ $pos->division_id }}" {{ old('position_id', $careerHistory->position_id ?? '') == $pos->id ? 'selected' : '' }}>
                            {{ $pos->title }}
                        </option>
                    @endforeach

                </select>
            </div>
            @error('position_id')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        {{-- Division (readonly & auto from Position) --}}
        <div class="form-group row align-items-center">
            <label class="col-md-2 col-form-label">Division :</label>
            <div class="col-md-3">
                <input type="text" id="division_name" class="form-control"
                    value="{{ $careerHistory->position->division->name ?? '-' }}" readonly>
            </div>
            <input type="hidden" name="division_id" id="division_id"
                value="{{ $careerHistory->position->division_id ?? '' }}">
        </div>

        {{-- Employee Type --}}
        <div class="form-group row align-items-center">
            <label for="employee_type" class="col-md-2 col-form-label">Employee Type <span class="text-danger">*</span>
                :</label>
            <div class="col-md-3">
                <select name="employee_type" id="employee_type"
                    class="form-control @error('employee_type') is-invalid @enderror" required>
                    <option value="">Select Employee Type</option>
                    @php
                        $selectedType = old('employee_type', $careerHistory->employee_type ?? $employee->employee_type);
                    @endphp
                    @foreach (['Kontrak', 'Magang', 'Masa Percobaan', 'Fulltime'] as $type)
                        <option value="{{ $type }}" {{ $selectedType == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
                @error('employee_type')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Start Date --}}
        <div class="form-group row align-items-center">
            <label for="start_date" class="col-md-2 col-form-label">Start Date <span class="text-danger">*</span>
                :</label>
            <div class="col-md-3">
                <div class="input-group date-input-group">
                    <input type="date" name="start_date" id="start_date"
                        class="form-control @error('start_date') is-invalid @enderror"
                        value="{{ old('start_date', optional($careerHistory)->start_date ? $careerHistory->start_date : '') }}"
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
            <label for="end_date" class="col-md-2 col-form-label">End Date :</label>
            <div class="col-md-3">
                <div class="input-group date-input-group">
                    <input type="date" name="end_date" id="end_date"
                        class="form-control @error('end_date') is-invalid @enderror"
                        value="{{ old('end_date', optional($careerHistory)->end_date ? $careerHistory->end_date : '') }}"
                        @if (isset($careerHistory) && is_null($careerHistory->end_date)) disabled @endif>
                    <label for="end_date" class="input-group-append">
                        <span class="input-group-text">
                            <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                        </span>
                    </label>
                </div>
                @error('end_date')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
                @if (isset($careerHistory) && is_null($careerHistory->end_date))
                    <small class="form-text text-muted">End date untuk entri aktif akan diatur otomatis saat membuat entri
                        baru.</small>
                @endif
            </div>
        </div>

        {{-- Type --}}
        <div class="form-group row align-items-center">
            <label for="type" class="col-md-2 col-form-label">Type <span class="text-danger">*</span> :</label>
            <div class="col-md-3">
                <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                    <option value="">Select Type</option>
                    @foreach (['Promosi', 'Mutasi', 'Demosi', 'Awal Masuk'] as $moveType)
                        <option value="{{ $moveType }}" {{ old('type', $careerHistory->type ?? '') == $moveType ? 'selected' : '' }}>
                            {{ $moveType }}
                        </option>
                    @endforeach
                </select>
                @error('type')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Notes --}}
        <div class="form-group row">
            <label for="notes" class="col-md-2 col-form-label">Notes :</label>
            <div class="col-md-4">
                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="6"
                    placeholder="Notes of Your Career History">{{ old('notes', $careerHistory->notes ?? '') }}</textarea>
                @error('notes')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.getElementById('position_id').addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            document.getElementById('division_name').value = selected.getAttribute('data-division') || '-';
            document.getElementById('division_id').value = selected.getAttribute('data-division-id') || '';
        });
    </script>
@endpush