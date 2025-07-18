{{-- Contact Person Name --}}
<div class="form-group row align-items-center">
    <label for="contact_name" class="col-md-2 col-form-label">Contact Person Name <span class="text-danger">*</span>
        :</label>
    <div class="col-md-3">
        <input type="text" class="form-control @error('contact_name') is-invalid @enderror" id="contact_name"
            name="contact_name" value="{{ old('contact_name', $familyDependent->contact_name ?? '') }}"
            placeholder="Input contact person name" required>
        @error('contact_name')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Relationship --}}
<div class="form-group row align-items-center">
    <label for="relationship" class="col-md-2 col-form-label">Relationship <span class="text-danger">*</span> :</label>
    <div class="col-md-3">
        <input type="text" class="form-control @error('relationship') is-invalid @enderror" id="relationship"
            name="relationship" value="{{ old('relationship', $familyDependent->relationship ?? '') }}"
            placeholder="e.g., Spouse, Child, Parent" required>
        @error('relationship')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Phone Number --}}
<div class="form-group row align-items-center">
    <label for="phone_number" class="col-md-2 col-form-label">Phone Number <span class="text-danger">*</span> :</label>
    <div class="col-md-3">
        <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number"
            name="phone_number" value="{{ old('phone_number', $familyDependent->phone_number ?? '') }}"
            placeholder="Input phone number" inputmode="tel" maxlength="20" required>
        @error('phone_number')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Address --}}
<div class="form-group row align-items-center">
    <label for="address" class="col-md-2 col-form-label">Address <span class="text-danger">*</span> :</label>
    <div class="col-md-4">
        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="4"
            placeholder="Enter full address" required>{{ old('address', $familyDependent->address ?? '') }}</textarea>
        @error('address')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- City --}}
<div class="form-group row align-items-center">
    <label for="city" class="col-md-2 col-form-label">City <span class="text-danger">*</span> :</label>
    <div class="col-md-3">
        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city"
            value="{{ old('city', $familyDependent->city ?? '') }}" placeholder="Enter city" required>
        @error('city')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>

{{-- Province --}}
<div class="form-group row align-items-center">
    <label for="province" class="col-md-2 col-form-label">Province <span class="text-danger">*</span></label>
    <div class="col-md-3">
        <input type="text" class="form-control @error('province') is-invalid @enderror" id="province"
            name="province" value="{{ old('province', $familyDependent->province ?? '') }}"
            placeholder="Enter province" required>
        @error('province')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>
</div>
