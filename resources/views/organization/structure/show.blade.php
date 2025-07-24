@extends('layouts.admin')

@section('title', 'Detail Jabatan')
@section('header_icon', 'icon-park-outline--info')
@section('content_header', 'Detail Jabatan')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-edit.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail untuk: <strong>{{ $position->title }}</strong></h3>
            <div class="card-tools">
                <a href="{{ route('organization.structure.edit', $position->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Jabatan
                </a>
                <a href="{{ route('organization.structure.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Bagan
                </a>
            </div>
        </div>
        <div class="card-body">
            <h4>Karyawan yang Menjabat</h4>
            @if($position->employees->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>No.</th>
                                <th>Nama Karyawan</th>
                                <th>NIK</th>
                                <th>Divisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($position->employees as $employee)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $employee->full_name }}</td>
                                <td>{{ $employee->nik }}</td>
                                <td>{{ $employee->division->name ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-info">
                                        Lihat Detail Karyawan
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    Saat ini tidak ada karyawan yang menduduki posisi ini.
                </div>
            @endif
        </div>
    </div>
@endsection
