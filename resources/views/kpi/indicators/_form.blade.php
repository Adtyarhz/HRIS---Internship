<div class="row">
    <div class="col-12">
        <!-- Indicator Name -->
        <div class="form-group row align-items-center">
            <label for="indicator_name" class="col-md-2 col-form-label">Indicator Name <span class="text-danger">*</span> :</label>
            <div class="col-md-3">
                <input type="text" class="form-control @error('indicator_name') is-invalid @enderror" id="indicator_name" name="indicator_name" value="{{ old('indicator_name', $kpiIndicator->indicator_name) }}" required placeholder="Enter Indicator Name">
                @error('indicator_name')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Description -->
        <div class="form-group row">
            <label for="description" class="col-md-2 col-form-label">Description :</label>
            <div class="col-md-4">
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Enter Indicator Description">{{ old('description', $kpiIndicator->description) }}</textarea>
                @error('description')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Measurement Unit -->
        <div class="form-group row align-items-center">
            <label for="measurement_unit" class="col-md-2 col-form-label">Measurement Unit <span class="text-danger">*</span> :</label>
            <div class="col-md-3">
                <input type="text" class="form-control @error('measurement_unit') is-invalid @enderror" id="measurement_unit" name="measurement_unit" value="{{ old('measurement_unit', $kpiIndicator->measurement_unit) }}" placeholder="Example: %, Rupiah, Unit, Point" required>
                @error('measurement_unit')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Higher Is Better -->
        <div class="form-group row align-items-center">
            <label for="higher_is_better" class="col-md-2 col-form-label">Evaluation Type <span class="text-danger">*</span> :</label>
            <div class="col-md-3">
                <select class="form-control @error('higher_is_better') is-invalid @enderror" id="higher_is_better" name="higher_is_better" required>
                    <option value="1" {{ old('higher_is_better', $kpiIndicator->higher_is_better) == '1' ? 'selected' : '' }}>Higher Value is Better (e.g. Sales)</option>
                    <option value="0" {{ old('higher_is_better', $kpiIndicator->higher_is_better) == '0' ? 'selected' : '' }}>Lower Value is Better (e.g. Cost, Errors)</option>
                </select>
                @error('higher_is_better')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
</div>
