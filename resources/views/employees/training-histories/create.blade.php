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
                    <form action="{{ route('employees.training-histories.store', $employee->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-12">

                                {{-- Training Name --}}
                                <div class="form-group row align-items-center">
                                    <label for="training_name" class="col-md-2 col-form-label">
                                        Training Name <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control @error('training_name') is-invalid @enderror"
                                            id="training_name" name="training_name" value="{{ old('training_name') }}"
                                            placeholder="e.g., Leadership Training" required>
                                        @error('training_name')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Provider --}}
                                <div class="form-group row align-items-center">
                                    <label for="provider" class="col-md-2 col-form-label">
                                        Provider <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control @error('provider') is-invalid @enderror"
                                            id="provider" name="provider" value="{{ old('provider') }}"
                                            placeholder="e.g., BPR Perdana" required>
                                        @error('provider')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Description --}}
                                <div class="form-group row">
                                    <label for="description" class="col-md-2 col-form-label">
                                        Description <span class="text-danger">*</span>:
                                    </label>
                                    <div class="col-md-4">
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                            id="description" name="description" rows="6"
                                            placeholder="Brief description of the training program">{{ old('description') }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
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
                                            <input type="date"
                                                class="form-control @error('start_date') is-invalid @enderror"
                                                id="start_date" name="start_date" value="{{ old('start_date') }}" required>
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
                                        End Date <span class="text-danger">*</span>:
                                    </label>
                                    <div class="col-md-2">
                                        <div class="input-group date-input-group">
                                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                                id="end_date" name="end_date" value="{{ old('end_date') }}" required>
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

                                {{-- Cost --}}
                                <div class="form-group row align-items-center">
                                    <label for="cost" class="col-md-2 col-form-label">
                                        Cost <span class="text-danger">*</span>:
                                    </label>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <input type="number" step="any"
                                                class="form-control @error('cost') is-invalid @enderror" id="cost"
                                                name="cost" value="{{ old('cost') }}"
                                                placeholder="Enter training program cost" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">IDR</span>
                                            </div>
                                        </div>
                                        @error('cost')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Location --}}
                                <div class="form-group row">
                                    <label for="location" class="col-md-2 col-form-label">
                                        Location <span class="text-danger">*</span>:
                                    </label>
                                    <div class="col-md-4">
                                        <textarea class="form-control @error('location') is-invalid @enderror" id="location"
                                            name="location" rows="3" placeholder="e.g., Jakarta, Online via Zoom"
                                            required>{{ old('location') }}</textarea>
                                        @error('location')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Certificate File --}}
<div class="form-group row align-items-center">
    <label for="certificate_file" class="col-md-2 col-form-label">
        Certificate File :
    </label>
    <div class="col-md-4">
        <input type="file"
            class="form-control @error('certificate_file') is-invalid @enderror"
            id="certificate_file" name="certificate_file"
            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.zip">
        <small class="form-text text-muted">
            Upload certificate file (PDF, JPG, PNG, DOC, DOCX, ZIP, max 10MB).
        </small>
        @error('certificate_file')
            <span class="text-danger small mt-1">{{ $message }}</span>
        @enderror
    </div>
</div>

                                <div class="form-group row align-items-center">
                                    <label for="material_files" class="col-md-2 col-form-label">Training Record Files :</label>
                                    <div class="col-md-4">
                                        <input type="file"
                                            class="form-control @error('material_files.*') is-invalid @enderror"
                                            id="material_files" name="material_files[]" multiple>
                                        <small class="form-text text-muted">You can select more than one file if needed
                                            (PDF, JPG, PNG, DOC, DOCX, ZIP, max 10MB
                                            per file, max 10 files).</small>
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
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">
                                    <a href="{{ route('employees.training-histories.index', $employee->id) }}"
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