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

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form id="updateForm"
                    action="{{ route('employees.family-dependents.update', [$employee->id, $familyDependent->id]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    <div class="row">
                        <div class="col-12">
                            
                          @include('employees.family-dependents._form', ['familyDependent' => $familyDependent])

                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-buttons-container">
                                <button type="button" class="btn btn-delete" onclick="showDeleteModal()">Delete</button>
                                <a href="{{ route('employees.family-dependents.index', $employee->id) }}"
                                    class="btn btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-submit" form="updateForm">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Modal Konfirmasi Hapus Sertifikasi -->
                <div id="deleteModal" class="modal">
                    <div class="modal-content">
                        <h5>Konfirmasi Penghapusan Family Dependent</h5>
                        <p>Apakah Anda yakin ingin menghapus data ini?</p>
                        <div class="modal-buttons">
                            <button class="btn btn-cancel" onclick="closeDeleteModal()">Batal</button>
                            <form
                                action="{{ route('employees.family-dependents.destroy', [$employee->id, $familyDependent->id]) }}"
                                method="POST" style="display: inline;">
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
