@extends('layouts.admin')

@section('title', 'Data Karyawan')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

{{-- Cukup muat file CSS eksternal di sini --}}
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}" />
@endpush

@section('content')
    <header class="page-controls">
        <div class="search-container">
            <h2 class="search-title">Search Employee</h2>
            <form action="{{ route('employees.index') }}" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Input Employee’s Name or NIK" class="search-input"
                    value="{{ request('search') }}">
                <button type="submit" class="search-button">Cari</button>
            </form>
        </div>
        <div class="actions-container">
            <a href="{{ route('employees.create') }}" class="add-employee-button">
                <span class="add-icon">+</span>
                Add New Employee
            </a>
        </div>
    </header>

    <!-- Notifikasi -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <main class="employee-table">
        <!-- Header Tabel -->
        <div class="table-header">
            <div class="header-col col-no">No</div>
            <div class="header-col col-employee">Karyawan</div>
            <div class="header-col col-details">Jabatan & Divisi</div>
            <div class="header-col col-status">Status</div>
            <div class="header-col col-actions">Aksi</div>
        </div>

        <!-- Body Tabel (Daftar Karyawan) -->
        <div class="table-body">
            @forelse ($employees as $employee)
                <div class="employee-row">
                    {{-- Tambahkan atribut data-label untuk mode responsif --}}
                    <div class="row-col col-no" data-label="No.">
                        {{ $loop->iteration + ($employees->currentPage() - 1) * $employees->perPage() }}
                    </div>
                    <div class="row-col col-employee" data-label="Karyawan">
                        <img src="{{ $employee->photo_url ?? 'https://placehold.co/45x45/9A3B3B/FFFFFF?text=' . strtoupper(substr($employee->full_name, 0, 1)) }}"
                            alt="Avatar Karyawan" class="employee-avatar">
                        <div class="employee-info">
                            <span class="employee-name">{{ $employee->full_name }}</span>
                            <span class="employee-id">NIK: {{ $employee->nik }}</span>
                        </div>
                    </div>
                    <div class="row-col col-details" data-label="Jabatan/Divisi">
                        <div class="employee-info">
                            <span class="employee-position">{{ $employee->position->name ?? 'Belum Diatur' }}</span>
                            <span class="employee-division">{{ $employee->division->name ?? 'Belum Diatur' }}</span>
                        </div>
                    </div>
                    <div class="row-col col-status" data-label="Status">
                        @if ($employee->status == 'Aktif')
                            <span class="status-badge status-active">{{ $employee->status }}</span>
                        @else
                            <span class="status-badge status-inactive">{{ $employee->status }}</span>
                        @endif
                    </div>
                    <div class="row-col col-actions" data-label="Aksi">
                        <a href="{{ route('employees.show', $employee) }}" class="action-btn view-btn" title="Lihat">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('employees.edit', $employee) }}" class="action-btn edit-btn" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn delete-btn" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-row">
                    Tidak ada data karyawan yang ditemukan.
                </div>
            @endforelse
        </div>
    </main>

    @if ($employees->hasPages())
        <footer class="page-footer">
            {{-- Menggunakan komponen paginasi default Laravel yang akan di-style oleh CSS --}}
            {{ $employees->links('vendor.pagination.custom') }}
        </footer>
    @endif

@endsection


