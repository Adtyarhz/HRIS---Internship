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

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 30%;
            text-align: center;
        }

        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
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
                                <label for="location" class="col-md-2 col-form-label">location :</label>
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
                                <label for="end_date" class="col-md-2 col-form-label">End Date :</label>
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
                                <label for="cost" class="col-md-2 col-form-label">Cost :</label>
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
                                <label for="location" class="col-md-2 col-form-label">Location :</label>
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
                                        value="{{ old('certificate_number', $trainingHistory->provider) }}" required>
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
                                                        {{ Str::limit($material->file_path, 40) }}
                                                    </a>
                                                    <button type="button" class="btn btn-delete-material"
                                                        onclick="showDeleteMaterialModal('{{ route('employees.training-histories.materials.destroy', [$employee->id, $trainingHistory->id, $material->id]) }}')">Hapus</button>
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
                                <button type="button" class="btn btn-delete" onclick="showDeleteModal()">Hapus
                                    Sertifikasi</button>
                                <a href="{{ route('employees.training-histories.index', $employee->id) }}"
                                    class="btn btn-cancel">Batal</a>
                                <button type="submit" class="btn btn-submit" form="updateForm">Simpan Perubahan</button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Modal Konfirmasi Hapus Sertifikasi -->
                <div id="deleteModal" class="modal">
                    <div class="modal-content">
                        <h5>Konfirmasi Penghapusan Sertifikasi</h5>
                        <p>Apakah Anda yakin ingin menghapus sertifikasi ini beserta semua filenya?</p>
                        <div class="modal-buttons">
                            <button class="btn btn-cancel" onclick="closeDeleteModal()">Batal</button>
                            <form
                                action="{{ route('employees.training-histories.destroy', [$employee->id, $trainingHistory->id]) }}"
                                method="POST" style="display: inline;">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-delete">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Konfirmasi Hapus File Materi -->
                <div id="deleteMaterialModal" class="modal">
                    <div class="modal-content">
                        <h5>Konfirmasi Penghapusan File Materi</h5>
                        <p>Apakah Anda yakin ingin menghapus file materi ini?</p>
                        <div class="modal-buttons">
                            <button class="btn btn-cancel" onclick="closeDeleteMaterialModal()">Batal</button>
                            <form id="deleteMaterialForm" method="POST" style="display: inline;">
                                @csrf
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-delete">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Fungsi untuk menampilkan modal hapus sertifikasi
        function showDeleteModal() {
            document.getElementById('deleteModal').style.display = 'block';
        }

        // Fungsi untuk menutup modal hapus sertifikasi
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Fungsi untuk menampilkan modal hapus file materi
        function showDeleteMaterialModal(url) {
            const form = document.getElementById('deleteMaterialForm');
            form.action = url; // Set rute penghapusan secara dinamis
            document.getElementById('deleteMaterialModal').style.display = 'block';
        }

        // Fungsi untuk menutup modal hapus file materi
        function closeDeleteMaterialModal() {
            document.getElementById('deleteMaterialModal').style.display = 'none';
        }

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
