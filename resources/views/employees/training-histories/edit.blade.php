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
                        action="{{ route('employees.training-histories.update', [$employee->id, $trainingHistory->id]) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group row align-items-center">
                                    <label for="training_name" class="col-md-2 col-form-label">Training Name <span
                                            class="text-danger">*</span> :</label>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control @error('training_name') is-invalid @enderror"
                                            id="training_name" name="training_name"
                                            value="{{ old('training_name', $trainingHistory->training_name) }}"
                                            placeholder="e.g., Leadership Training" required>
                                        @error('training_name')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label for="provider" class="col-md-2 col-form-label">Provider <span
                                            class="text-danger">*</span> :</label>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control @error('provider') is-invalid @enderror"
                                            id="provider" name="provider"
                                            value="{{ old('provider', $trainingHistory->provider) }}"
                                            placeholder="e.g., BPR Perdana" required>
                                        @error('provider')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="description" class="col-md-2 col-form-label">Description <span
                                            class="text-danger">*</span>:</label>
                                    <div class="col-md-4">
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                            id="description" name="description" rows="6"
                                            placeholder="Brief description of the training program">{{ old('description', $trainingHistory->description) }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label for="start_date" class="col-md-2 col-form-label">Start Date <span
                                            class="text-danger">*</span> :</label>
                                    <div class="col-md-2">
                                        <div class="input-group date-input-group">
                                            <input type="date"
                                                class="form-control @error('start_date') is-invalid @enderror"
                                                id="start_date" name="start_date"
                                                value="{{ old('start_date', \Carbon\Carbon::parse($trainingHistory->start_date)->format('Y-m-d')) }}"
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
                                    <label for="end_date" class="col-md-2 col-form-label">End Date <span
                                            class="text-danger">*</span>:</label>
                                    <div class="col-md-2">
                                        <div class="input-group date-input-group">
                                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                                id="end_date" name="end_date"
                                                value="{{ old('end_date', optional(\Carbon\Carbon::parse($trainingHistory->end_date ?? ''))->format('Y-m-d')) }}"
                                                required>
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
                                    <label for="cost" class="col-md-2 col-form-label">Cost <span
                                            class="text-danger">*</span>:</label>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <input type="number" class="form-control @error('cost') is-invalid @enderror"
                                                id="cost" name="cost" value="{{ old('cost', $trainingHistory->cost) }}"
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

                                <div class="form-group row">
                                    <label for="location" class="col-md-2 col-form-label">Location <span
                                            class="text-danger">*</span>:</label>
                                    <div class="col-md-4">
                                        <textarea class="form-control @error('location') is-invalid @enderror" id="location"
                                            name="location" rows="3" placeholder="e.g., Jakarta, Online via Zoom"
                                            required>{{ old('location', $trainingHistory->location) }}</textarea>
                                        @error('location')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
    <label for="certificate_file" class="col-md-2 col-form-label">Certificate File :</label>
    <div class="col-md-4">
        {{-- ✅ Tampilkan file yang sudah ada (jika ada) --}}
        @if ($trainingHistory->certificate_file)
            <p class="mb-2">
                <a href="{{ asset('storage/certificates/' . $trainingHistory->certificate_file) }}" target="_blank">
                    <i class="fas fa-file-alt"></i>
                    {{ Str::afterLast($trainingHistory->certificate_file, '_') }}
                </a>
            </p>
        @else
            <p class="text-muted">No certificate file uploaded yet.</p>
        @endif

        {{-- ✅ Input file baru --}}
        <input type="file" class="form-control mt-2 @error('certificate_file') is-invalid @enderror"
            id="certificate_file" name="certificate_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
        <small class="form-text text-muted">Allowed types: PDF, JPG, PNG, DOC, DOCX (max 10MB).</small>
        @error('certificate_file')
            <span class="text-danger small mt-1">{{ $message }}</span>
        @enderror
    </div>
</div>

                                <div class="form-group row align-items-center">
                                    <label class="col-md-2 col-form-label">Training Record Files :</label>
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
                                                            onclick="showDeleteModal('delete-material', '{{ route('employees.training-histories.materials.destroy', [$employee->id, $trainingHistory->id, $material->id]) }}')">Delete
                                                            File</button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-muted">No training record files available.</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label for="material_files" class="col-md-2 col-form-label">Add Files
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

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">
                                    <button type="button" class="btn btn-delete"
                                        onclick="showDeleteModal('training-history-{{ $trainingHistory->id }}')">Delete</button>
                                    <a href="{{ route('employees.training-histories.index', $employee->id) }}"
                                        class="btn btn-cancel">Cancel</a>
                                    <button type="submit" class="btn btn-submit" form="updateForm">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Delete Modal --}}
                    <x-delete-modal modalId="training-history-{{ $trainingHistory->id }}"
                        :action="route('employees.training-histories.destroy', [$employee->id, $trainingHistory->id])"
                        message="Are you sure to delete this Training Record and all its files?" />

                    {{-- Delete Material Modal --}}
                    <x-delete-modal-material modalId="delete-material" message="Are you sure to delete this file?" />
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
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