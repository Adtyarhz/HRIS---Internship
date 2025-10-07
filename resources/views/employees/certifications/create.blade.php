@extends('layouts.admin')

@section('title', 'Employee Information')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
        @media (max-width: 768px) {
            .form-buttons-container {
                flex-direction: column-reverse;
                gap: 15px;
            }

            .btn-submit,
            .btn-cancel {
                width: 100%;
                max-width: 100%;
            }

            .btn-submit {
                margin-left: 0px;
            }
        }
    </style>
@endpush

@section('content-wrapper')
    @include('employees.partials.tab-menu', ['employee' => $employee])
    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">
                    <form action="{{ route('employees.certifications.store', $employee->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-12">

                                {{-- Certification Name --}}
                                <div class="form-group row align-items-center">
                                    <label for="certification_name" class="col-md-2 col-form-label">
                                        Certification Name <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-3">
                                        <input type="text"
                                            class="form-control @error('certification_name') is-invalid @enderror"
                                            id="certification_name" name="certification_name"
                                            value="{{ old('certification_name') }}"
                                            placeholder="e.g., AWS Certified Solutions Architect" required>
                                        @error('certification_name')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Issuer --}}
                                <div class="form-group row align-items-center">
                                    <label for="issuer" class="col-md-2 col-form-label">
                                        Issuer <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control @error('issuer') is-invalid @enderror"
                                            id="issuer" name="issuer" value="{{ old('issuer') }}"
                                            placeholder="e.g., Amazon Web Services" required>
                                        @error('issuer')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Description --}}
                                <div class="form-group row">
                                    <label for="description" class="col-md-2 col-form-label">
                                        Description :
                                    </label>
                                    <div class="col-md-4">
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                            id="description" name="description" rows="3"
                                            placeholder="Brief description of certification">{{ old('description') }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Date Obtained --}}
                                <div class="form-group row align-items-center">
                                    <label for="date_obtained" class="col-md-2 col-form-label">
                                        Date Obtained <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-2">
                                        <div class="input-group date-input-group">
                                            <input type="date"
                                                class="form-control @error('date_obtained') is-invalid @enderror"
                                                id="date_obtained" name="date_obtained" value="{{ old('date_obtained') }}"
                                                required>
                                            <label for="date_obtained" class="input-group-append">
                                                <span class="input-group-text">
                                                    <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                                                </span>
                                            </label>
                                        </div>
                                        @error('date_obtained')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Expiry Date --}}
                                <div class="form-group row align-items-center">
                                    <label for="expiry_date" class="col-md-2 col-form-label">
                                        Expiry Date :
                                    </label>
                                    <div class="col-md-2">
                                        <div class="input-group date-input-group">
                                            <input type="date"
                                                class="form-control @error('expiry_date') is-invalid @enderror"
                                                id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
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

                                {{-- Cost (Rp) --}}
                                <div class="form-group row align-items-center">
                                    <label for="cost" class="col-md-2 col-form-label">
                                        Cost <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <input type="number" step="any"
                                                class="form-control @error('cost') is-invalid @enderror" id="cost"
                                                name="cost" value="{{ old('cost') }}" placeholder="Enter certification cost"
                                                required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">IDR</span>
                                            </div>
                                        </div>
                                        @error('cost')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Main Certificate File --}}
                                <div class="form-group row align-items-center">
                                    <label for="certificate_file" class="col-md-2 col-form-label">
                                        Main Certificate <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-4">
                                        <input type="file"
                                            class="form-control @error('certificate_file') is-invalid @enderror"
                                            id="certificate_file" name="certificate_file" required
                                            placeholder="Upload main certificate file">
                                        <small class="form-text text-muted">
                                            Main certificate file (PDF, JPG, PNG, max 5MB). Required.
                                        </small>
                                        @error('certificate_file')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Supporting Material Files --}}
                                <div class="form-group row align-items-center">
                                    <label for="material_files" class="col-md-2 col-form-label">
                                        Certification Files :
                                    </label>
                                    <div class="col-md-4">
                                        <input type="file"
                                            class="form-control @error('material_files.*') is-invalid @enderror"
                                            id="material_files" name="material_files[]" multiple
                                            placeholder="Upload supporting materials">
                                        <small class="form-text text-muted">
                                            You can select more than one file if needed (PDF, JPG, PNG, DOC, DOCX, ZIP, max
                                            10MB
                                            per file, max 10 files).
                                        </small>
                                        @error('material_files.*')
                                            <span class="text-danger small mt-1">{{ $message }}</span>
                                        @enderror
                                        @error('material_files')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Form Buttons --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">
                                    <a href="{{ route('employees.certifications.index', $employee->id) }}"
                                        class="btn btn-cancel">Cancel</a>
                                    <button type="submit" class="btn btn-submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection