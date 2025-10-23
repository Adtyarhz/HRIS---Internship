@extends('layouts.admin')

@section('title', 'Employee Information')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
        .existing-files {
            list-style: none;
            padding-left: 0;
        }

        .existing-files li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .existing-files a {
            text-decoration: none;
            color: #007bff;
        }

        .btn-delete-material {
            background-color: #FF4242;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-family: 'Montserrat', sans-serif;
            font-size: 10px;
            font-weight: 500;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
        }

        .btn-delete-material:hover {
            background-color: #e63939;
            color: #eee;
        }

        @media (max-width: 768px) {
            .form-buttons-container {
                flex-direction: column-reverse;
                gap: 15px;
            }

            .btn-submit,
            .btn-cancel,
            .btn-delete {
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
                    <form id="updateForm"
                        action="{{ route('employees.certifications.update', [$employee->id, $certification->id]) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">

                        <div class="row">
                            <div class="col-12">
                                {{-- Certification Name --}}
                                <div class="form-group row align-items-center">
                                    <label for="certification_name" class="col-md-2 col-form-label">Certification Name <span
                                            class="text-danger">*</span> :</label>
                                    <div class="col-md-3">
                                        <input type="text"
                                            class="form-control @error('certification_name') is-invalid @enderror"
                                            id="certification_name" name="certification_name"
                                            value="{{ old('certification_name', $certification->certification_name) }}"
                                            placeholder="e.g., AWS Certified Solutions Architect" required>
                                        @error('certification_name')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Issuer --}}
                                <div class="form-group row align-items-center">
                                    <label for="issuer" class="col-md-2 col-form-label">Issuer <span
                                            class="text-danger">*</span> :</label>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control @error('issuer') is-invalid @enderror"
                                            id="issuer" name="issuer" value="{{ old('issuer', $certification->issuer) }}"
                                            placeholder="e.g., Amazon Web Services" required>
                                        @error('issuer')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Description --}}
                                <div class="form-group row">
                                    <label for="description" class="col-md-2 col-form-label">Description :</label>
                                    <div class="col-md-4">
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                            id="description" name="description" rows="3"
                                            placeholder="Brief description of certification">{{ old('description', $certification->description) }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Date Obtained --}}
                                <div class="form-group row align-items-center">
                                    <label for="date_obtained" class="col-md-2 col-form-label">Date Obtained <span
                                            class="text-danger">*</span> :</label>
                                    <div class="col-md-2">
                                        <div class="input-group date-input-group">
                                            <input type="date"
                                                class="form-control @error('date_obtained') is-invalid @enderror"
                                                id="date_obtained" name="date_obtained"
                                                value="{{ old('date_obtained', $certification->date_obtained) }}"
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
                                    <label for="expiry_date" class="col-md-2 col-form-label">Expiry Date :</label>
                                    <div class="col-md-2">
                                        <div class="input-group date-input-group">
                                            <input type="date"
                                                class="form-control @error('expiry_date') is-invalid @enderror"
                                                id="expiry_date" name="expiry_date"
                                                value="{{ old('expiry_date', optional($certification->expiry_date)->format('Y-m-d')) }}">
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

                                {{-- Cost --}}
                                <div class="form-group row align-items-center">
                                    <label for="cost" class="col-md-2 col-form-label">
                                        Cost <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <input type="number" class="form-control @error('cost') is-invalid @enderror"
                                                id="cost" name="cost" value="{{ old('cost', $certification->cost) }}"
                                                placeholder="Enter certification cost" required>
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
                                    <label for="certificate_file" class="col-md-2 col-form-label">Main Certificate :</label>
                                    <div class="col-md-4">
                                        <input type="file"
                                            class="form-control @error('certificate_file') is-invalid @enderror"
                                            id="certificate_file" name="certificate_file">
                                        <small class="form-text text-muted">Main certificate file (PDF, JPG, PNG, max 5MB).
                                            Leave empty if you don’t want to replace.</small>
                                        @if ($certification->certificate_file)
                                            <p class="mt-2">Current file:
                                                <a href="{{ asset('storage/' . $certification->certificate_file) }}"
                                                    target="_blank">{{ Str::afterLast($certification->certificate_file, '_') }}</a>
                                            </p>
                                            <input type="hidden" name="existing_certificate_file"
                                                value="{{ $certification->certificate_file }}">
                                        @endif
                                        @error('certificate_file')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Supporting Material Files --}}
                                <div class="form-group row align-items-center">
                                    <label class="col-md-2 col-form-label">Certification Files :</label>
                                    <div class="col-md-4">
                                        @if ($certification->certificationMaterials->isNotEmpty())
                                            <ul class="existing-files">
                                                @foreach ($certification->certificationMaterials as $material)
                                                    <li>
                                                        <a href="{{ asset('storage/certifications/materials/' . $material->file_path) }}"
                                                            target="_blank">
                                                            <i class="fas fa-file-alt"></i>
                                                            {{ Str::afterLast($material->file_path, '_') }}
                                                        </a>
                                                        <button type="button" class="btn btn-delete-material"
                                                            onclick="showDeleteModal('delete-material', '{{ route('employees.certifications.materials.destroy', [$employee->id, $certification->id, $material->id]) }}')">Delete
                                                            File</button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted">No certification files available.</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label for="material_files" class="col-md-2 col-form-label">Add Certification
                                        Files
                                        :</label>
                                    <div class="col-md-4">
                                        <input type="file"
                                            class="form-control @error('material_files.*') is-invalid @enderror"
                                            id="material_files" name="material_files[]" multiple>
                                        <small class="form-text text-muted">Select more than one file if needed (PDF, JPG,
                                            PNG, DOC, DOCX, ZIP, max 10MB per file, max 10 files).</small>
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

                        {{-- Buttons --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">
                                    <button type="button" class="btn btn-delete"
                                        onclick="showDeleteModal('certification-{{ $certification->id }}')">Delete</button>
                                    <a href="{{ route('employees.certifications.index', $employee->id) }}"
                                        class="btn btn-cancel">Cancel</a>
                                    <button type="submit" class="btn btn-submit" form="updateForm">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Delete Certification Modal -->
                    <x-delete-modal modalId="certification-{{ $certification->id }}"
                        :action="route('employees.certifications.destroy', [$employee->id, $certification->id])"
                        message="Are you sure you want to delete this certification and all its files?" />

                    <!-- Delete Material Modal -->
                    <x-delete-modal-material modalId="delete-material"
                        message="Are you sure you want to delete this file?" />
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Disable submit button on form submission
        document.getElementById('updateForm').addEventListener('submit', function (e) {
            console.log('Form submitted with method: PUT');
            console.log('Form data:', new FormData(this));
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Saving...';
            }
        });
    </script>
@endpush