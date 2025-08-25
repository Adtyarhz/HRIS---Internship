@extends('layouts.admin')

@section('title', 'Tambah Sertifikasi Karyawan')
@section('header_icon', 'icon-park-outline--certificate')
@section('content_header', 'Tambah Sertifikasi')

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
                <form action="{{ route('employees.certifications.store', $employee->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-12">

                            {{-- Nama Sertifikasi --}}
                            <div class="form-group row align-items-center">
                                <label for="certification_name" class="col-md-2 col-form-label">Nama Sertifikasi <span class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control @error('certification_name') is-invalid @enderror" id="certification_name" name="certification_name" value="{{ old('certification_name') }}" required>
                                    @error('certification_name') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Penerbit --}}
                            <div class="form-group row align-items-center">
                                <label for="issuer" class="col-md-2 col-form-label">Penerbit <span class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <input type="text" class="form-control @error('issuer') is-invalid @enderror" id="issuer" name="issuer" value="{{ old('issuer') }}" required>
                                    @error('issuer') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Deskripsi --}}
                            <div class="form-group row">
                                <label for="description" class="col-md-2 col-form-label">Deskripsi :</label>
                                <div class="col-md-4">
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="8">{{ old('description') }}</textarea>
                                    @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Tanggal Diperoleh --}}
                            <div class="form-group row align-items-center">
                                <label for="date_obtained" class="col-md-2 col-form-label">Tanggal Diperoleh <span class="text-danger">*</span> :</label>
                                <div class="col-md-2">
                                    <div class="input-group date-input-group">
                                        <input type="date" class="form-control @error('date_obtained') is-invalid @enderror" id="date_obtained" name="date_obtained" value="{{ old('date_obtained') }}" required>
                                        <label for="date_obtained" class="input-group-append">
                                            <span class="input-group-text">
                                                <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                                            </span>
                                        </label>
                                    </div>
                                    @error('date_obtained') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Tanggal Kedaluwarsa --}}
                            <div class="form-group row align-items-center">
                                <label for="expiry_date" class="col-md-2 col-form-label">Tanggal Kedaluwarsa :</label>
                                <div class="col-md-2">
                                    <div class="input-group date-input-group">
                                        <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
                                        <label for="expiry_date" class="input-group-append">
                                            <span class="input-group-text">
                                                <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                                            </span>
                                        </label>
                                    </div>
                                    @error('expiry_date') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Biaya (Rp) --}}
<div class="form-group row align-items-center">
    <label for="cost" class="col-md-2 col-form-label">
        Biaya (Rp) <span class="text-danger">*</span> :
    </label>
    <div class="col-md-3">
        <input type="number" step="any" class="form-control @error('cost') is-invalid @enderror" 
               id="cost" name="cost" value="{{ old('cost') }}" required>
        @error('cost') 
            <span class="invalid-feedback d-block">{{ $message }}</span> 
        @enderror
    </div>
</div>

                            {{-- File Sertifikat Utama --}}
                            <div class="form-group row align-items-center">
                                <label for="certificate_file" class="col-md-2 col-form-label">File Sertifikat Utama <span class="text-danger">*</span> :</label>
                                <div class="col-md-4">
                                    <input type="file" class="form-control @error('certificate_file') is-invalid @enderror" id="certificate_file" name="certificate_file" required>
                                    <small class="form-text text-muted">File utama sertifikat (PDF, JPG, PNG, max 5MB). Wajib diisi.</small>
                                    @error('certificate_file') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- File Materi Pendukung --}}
                            <div class="form-group row align-items-center">
                                <label for="material_files" class="col-md-2 col-form-label">File Materi Pendukung :</label>
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
                                <a href="{{ route('employees.certifications.index', $employee->id) }}" class="btn btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection