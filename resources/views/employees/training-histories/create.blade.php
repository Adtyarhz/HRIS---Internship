@extends('layouts.admin')

@section('title', 'Employee Information')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        {{-- 1. Panggil partial menu tab --}}
        @include('employees.partials.tab-menu', ['employee' => $employee])

        {{-- 2. Container untuk konten form --}}
        <div class="form-content-container">
            <div class="card-body">                
                <form action="{{ route('employees.training-histories.store', $employee->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-12">

                            <div class="form-group row align-items-center">
                                <label for="training_name" class="col-md-2 col-form-label">Training Name <span class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control @error('traingin_name') is-invalid @enderror" id="training_name" name="training_name" value="{{ old('training_name') }}" required>
                                    @error('training_name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label for="provider" class="col-md-2 col-form-label">Provider <span class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control @error('provider') is-invalid @enderror" id="provider" name="provider" value="{{ old('provider') }}" required>
                                    @error('provider') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="description" class="col-md-2 col-form-label">Description <span class="text-danger">*</span>:</label>
                                <div class="col-md-4">
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="8">{{ old('description') }}</textarea>
                                    @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label for="start_date" class="col-md-2 col-form-label">Start Date <span class="text-danger">*</span> :</label>
                                <div class="col-md-2">
                                    <div class="input-group date-input-group">
                                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                        <label for="start_date" class="input-group-append">
                                            <span class="input-group-text">
                                                <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                                            </span>
                                        </label>
                                    </div>
                                    @error('start_date') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label for="end_date" class="col-md-2 col-form-label">End Date <span class="text-danger">*</span>:</label>
                                <div class="col-md-2">
                                    <div class="input-group date-input-group">
                                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}">
                                        <label for="end_date" class="input-group-append">
                                            <span class="input-group-text">
                                                <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                                            </span>
                                        </label>
                                    </div>
                                    @error('end_date') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label for="cost" class="col-md-2 col-form-label">Cost <span class="text-danger">*</span>:</label>
                                <div class="col-md-3">
                                    <input type="number" step="any" class="form-control @error('cost') is-invalid @enderror" id="cost" name="cost" value="{{ old('cost') }}">
                                    @error('cost') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="location" class="col-md-2 col-form-label">Location <span class="text-danger">*</span>:</label>
                                <div class="col-md-4">
                                    <textarea class="form-control @error('location') is-invalid @enderror" id="location" name="location" rows="8">{{ old('location') }}</textarea>
                                    @error('location') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label for="certificate_number" class="col-md-2 col-form-label">Certificate Number <span class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control @error('certificate_number') is-invalid @enderror" id="certificate_number" name="certificate_number" value="{{ old('certificate_number') }}" required>
                                    @error('certificate_number') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label for="material_files" class="col-md-2 col-form-label">Training Record File :</label>
                                <div class="col-md-4">
                                    <input type="file" class="form-control @error('material_files.*') is-invalid @enderror" id="material_files" name="material_files[]" multiple>
                                    <small class="form-text text-muted">Pilih lebih dari satu file jika perlu (PDF, JPG, PNG, DOC, DOCX, ZIP, max 10MB per file, max 10 file).</small>
                                    @error('material_files.*') <span class="text-danger small mt-1">{{ $message }}</span> @enderror
                                    @error('material_files') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-buttons-container">
                                <a href="{{ route('employees.training-histories.index', $employee->id) }}" class="btn btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection