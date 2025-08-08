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
            border-radius: 4px;
            border-radius: 5px;
            color: white;
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

        .alert {
            margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        @include('employees.partials.tab-menu', ['employee' => $employee])

        <div class="form-content-container">
            <div class="card-body">
                <h4 class="mb-4">Edit Pelatihan untuk: <strong>{{ $employee->full_name }}</strong></h4>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form id="updateForm"
                    action="{{ route('employees.training-histories.update', [$employee->id, $trainingHistory->id]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group row align-items-center">
                                <label for="training_name" class="col-md-2 col-form-label">Training Name <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control @error('training_name') is-invalid @enderror"
                                        id="training_name" name="training_name"
                                        value="{{ old('training_name', $trainingHistory->training_name) }}" required>
                                    @error('training_name')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label for="provider" class="col-md-2 col-form-label">Provider <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control @error('provider') is-invalid @enderror"
                                        id="provider" name="provider"
                                        value="{{ old('provider', $trainingHistory->provider) }}" required>
                                    @error('provider')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="location" class="col-md-2 col-form-label">Description <span class="text-danger">*</span>:</label>
                                <div class="col-md-4">
                                    <textarea class="form-control @error('location') is-invalid @enderror" id="location" name="location" rows="3">{{ old('location', $trainingHistory->location) }}</textarea>
                                    @error('location')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label for="start_date" class="col-md-2 col-form-label">Start Date <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <div class="input-group date-input-group">
                                        <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                            id="start_date" name="start_date"
                                            value="{{ old('start_date', $trainingHistory->start_date->format('Y-m-d')) }}"
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

                            <div class="form-group row align-items-center">
                                <label for="end_date" class="col-md-2 col-form-label">End Date <span class="text-danger">*</span>:</label>
                                <div class="col-md-3">
                                    <div class="input-group date-input-group">
                                        <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                            id="end_date" name="end_date"
                                            value="{{ old('end_date', optional($trainingHistory->end_date)->format('Y-m-d')) }}">
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

                            <div class="form-group row align-items-center">
                                <label for="cost" class="col-md-2 col-form-label">Cost <span class="text-danger">*</span>:</label>
                                <div class="col-md-3">
                                    <input type="number" class="form-control @error('cost') is-invalid @enderror"
                                        id="cost" name="cost" value="{{ old('cost', $trainingHistory->cost) }}"
                                        step="0.01">
                                    @error('cost')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="location" class="col-md-2 col-form-label">Location <span class="text-danger">*</span>:</label>
                                <div class="col-md-4">
                                    <textarea class="form-control @error('location') is-invalid @enderror" id="location" name="location" rows="3">{{ old('location', $trainingHistory->location) }}</textarea>
                                    @error('location')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label for="certificate_number" class="col-md-2 col-form-label">Certificate Number <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-4">
                                    <input type="text"
                                        class="form-control @error('certificate_number') is-invalid @enderror"
                                        id="certificate_number" name="certificate_number"
                                        value="{{ old('certificate_number', $trainingHistory->certificate_number) }}" required>
                                    @error('certificate_number')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label class="col-md-2 col-form-label">Training Record File :</label>
                                <div class="col-md-4">
                                    @if ($trainingHistory->trainingMaterials->isNotEmpty())
                                        <ul class="existing-files">
                                            @foreach ($trainingHistory->trainingMaterials as $material)
                                                <li>
                                                    <a href="{{ asset('storage/training_materials/' . $material->file_path) }}"
                                                        target="_blank">
                                                        <i class="fas fa-file-alt"></i>
                                                        {{ Str::afterLast($material->file_path, '_') }}
                                                    </a>
                                                    <button type="button" class="btn btn-delete-material"
                                                        onclick="showDeleteModal('delete-material', '{{ route('employees.training-histories.materials.destroy', [$employee->id, $trainingHistory->id, $material->id]) }}')">Delete File</button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted">Tidak ada file materi pendukung.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label for="material_files" class="col-md-2 col-form-label">Tambah File Materi Pendukung
                                    :</label>
                                <div class="col-md-4">
                                    <input type="file"
                                        class="form-control @error('material_files.*') is-invalid @enderror"
                                        id="material_files" name="material_files[]" multiple>
                                    <small class="form-text text-muted">Pilih lebih dari satu file jika perlu (PDF, JPG,
                                        PNG, DOC, DOCX, ZIP, max 10MB per file, max 10 file).</small>
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
                                <button type="button" class="btn btn-delete" onclick="showDeleteModal('training-history-{{ $trainingHistory->id }}')">Delete</button>
                                <a href="{{ route('employees.training-histories.index', $employee->id) }}"
                                    class="btn btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-submit" form="updateForm">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Komponen Modal Delete -->
                <x-delete-modal 
                    modalId="training-history-{{ $trainingHistory->id }}" 
                    :action="route('employees.training-histories.destroy', [$employee->id, $trainingHistory->id])" 
                    message="Are you sure to delete this Training Record and all its files?" 
                />

                <!-- Komponen Modal Delete Mterial -->
                <x-delete-modal-material 
                    modalId="delete-material"
                    message="Are you sure to delete this file?" 
                />
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Nonaktifkan tombol submit saat pengiriman dan log data
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            console.log('Form submitted with method: PUT');
            console.log('Form data:', new FormData(this));
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Menyimpan...';
            }
        });
    </script>
@endpush
