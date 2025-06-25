@extends('layouts.admin')

@section('title', 'Detail Karyawan')
@section('content_header')
    Detail Karyawan: {{ $employee->full_name }}
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informasi Lengkap</h3>
        <div class="card-tools">
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h4>Data Pribadi</h4>
                <table class="table table-bordered">
                    <tr><th style="width: 200px;">Nama Lengkap</th><td>{{ $employee->full_name }}</td></tr>
                    <tr><th>NIK</th><td>{{ $employee->nik }}</td></tr>
                    <tr><th>NIP</th><td>{{ $employee->nip ?? '-' }}</td></tr>
                    <tr><th>NPWP</th><td>{{ $employee->npwp ?? '-' }}</td></tr>
                    <tr><th>Jenis Kelamin</th><td>{{ $employee->gender }}</td></tr>
                    <tr><th>Tempat, Tanggal Lahir</th><td>{{ $employee->birth_place }}, {{ $employee->birth_date->format('d F Y') }}</td></tr>
                    <tr><th>Umur</th><td>{{ $age ? $age . ' tahun' : 'N/A' }}</td></tr>
                    <tr><th>Agama</th><td>{{ $employee->religion }}</td></tr>
                    <tr><th>Status Pernikahan</th><td>{{ $employee->marital_status }}</td></tr>
                    <tr><th>Jumlah Tanggungan</th><td>{{ $employee->dependents }} orang</td></tr>
                    <tr><th>Nomor Telepon</th><td>{{ $employee->phone_number }}</td></tr>
                    <tr><th>Email</th><td>{{ $employee->email }}</td></tr>
                </table>

                <h4 class="mt-4">Alamat</h4>
                 <table class="table table-bordered">
                    <tr><th style="width: 200px;">Alamat KTP</th><td>{{ $employee->ktp_address }}</td></tr>
                    <tr><th>Alamat Domisili</th><td>{{ $employee->current_address }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h4>Data Kepegawaian</h4>
                 <table class="table table-bordered">
                    <tr><th style="width: 200px;">Status Karyawan</th><td>
                         @if($employee->status == 'Aktif')
                            <span class="badge badge-success">{{ $employee->status }}</span>
                        @else
                            <span class="badge badge-danger">{{ $employee->status }}</span>
                        @endif
                    </td></tr>
                    <tr><th>Tipe Karyawan</th><td>{{ $employee->employee_type }}</td></tr>
                    <tr><th>Tanggal Masuk</th><td>{{ $employee->hire_date->format('d F Y') }}</td></tr>
                    <tr><th>Tanggal Keluar</th><td>{{ $employee->separation_date ? $employee->separation_date->format('d F Y') : '-' }}</td></tr>
                    <tr><th>Divisi</th><td>{{ $employee->division->name ?? 'N/A' }}</td></tr>
                    <tr><th>Jabatan</th><td>{{ $employee->position->name ?? 'N/A' }}</td></tr>
                 </table>

                 <h4 class="mt-4">Akun Terhubung</h4>
                 <table class="table table-bordered">
                    <tr><th style="width: 200px;">Nama User</th><td>{{ $employee->user->name ?? 'Tidak terhubung' }}</td></tr>
                    <tr><th>Email User</th><td>{{ $employee->user->email ?? '-' }}</td></tr>
                 </table>
            </div>
        </div>
    </div>
</div>
@endsection
