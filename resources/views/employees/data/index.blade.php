@extends('layouts.admin')

@section('title', 'Data Karyawan')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}" />
@endpush

@section('content')
    <div class="card page-card">
        <div class="card-body">
            {{-- Search & Filter Form --}}
            <form action="{{ route('employees.index') }}" method="GET">
                <header class="page-controls">
                    <div class="search-and-filter-container">
                        <div class="search-container">
                            <h2 class="search-title">Search Employee</h2>
                            <input type="text" name="search" placeholder="Input Employee’s Name or NIK" class="search-input" value="{{ request('search') }}">
                        </div>
                        <div class="main-actions">
                            <button type="submit" class="search-button">Cari</button>
                            <button type="button" class="btn-filter" id="filter-toggle-btn">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                    <div class="actions-container">
                        <a href="{{ route('employees.create') }}" class="add-employee-button">
                            <span class="add-icon">+</span>
                            Add New Employee
                        </a>
                    </div>
                </header>

                {{-- Collapsible Filter Section --}}
                <div class="filter-section" id="filter-container" style="display: none;">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="division_id">Division</label>
                            <select name="division_id" id="division_id" class="form-control">
                                <option value="">All Divisions</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="position_id">Position</label>
                            <select name="position_id" id="position_id" class="form-control">
                                <option value="">All Positions</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" {{ request('position_id') == $position->id ? 'selected' : '' }}>
                                        {{ $position->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="employee_type">Employee Type</label>
                            <select name="employee_type" id="employee_type" class="form-control">
                                <option value="">All Types</option>
                                <option value="Kontrak" {{ request('employee_type') == 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                                <option value="Magang" {{ request('employee_type') == 'Magang' ? 'selected' : '' }}>Magang</option>
                                <option value="Masa Percobaan" {{ request('employee_type') == 'Masa Percobaan' ? 'selected' : '' }}>Masa Percobaan</option>
                                <option value="Fulltime" {{ request('employee_type') == 'Fulltime' ? 'selected' : '' }}>Fulltime</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Tidak Aktif" {{ request('status') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="office">Office</label>
                            <select name="office" id="office" class="form-control">
                                <option value="">All Offices</option>
                                <option value="Kantor Pusat" {{ request('office') == 'Kantor Pusat' ? 'selected' : '' }}>Kantor Pusat</option>
                                <option value="Kantor Cabang" {{ request('office') == 'Kantor Cabang' ? 'selected' : '' }}>Kantor Cabang</option>
                            </select>
                        </div>
                        <div class="col-md-4 filter-buttons">
                            <a href="{{ route('employees.index') }}" class="btn-reset">Reset</a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Notifications -->
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <main class="employee-table">
                <div class="table-header">
                    <div class="header-col col-no">No</div>
                    <div class="header-col col-employee">Karyawan</div>
                    <div class="header-col col-details">Jabatan & Divisi</div>
                    <div class="header-col col-status">Status</div>
                    <div class="header-col col-actions">Aksi</div>
                </div>
                <div class="table-body">
                    @forelse ($employees as $employee)
                        <div class="employee-row">
                            <div class="row-col col-no" data-label="No.">{{ $loop->iteration + ($employees->currentPage() - 1) * $employees->perPage() }}</div>
                            <div class="row-col col-employee" data-label="Karyawan">
                                <img src="{{ $employee->photo ? asset('storage/photo/' . $employee->photo) : 'https://placehold.co/45x45/9A3B3B/FFFFFF?text=' . strtoupper(substr($employee->full_name, 0, 1)) }}" alt="Avatar Karyawan" class="employee-avatar">
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
                                <a href="{{ route('employees.show', $employee) }}" title="Lihat">Lihat Karyawan</a>
                                <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete-btn" title="Hapus">
                                        <span class="gg--trash"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-row">Tidak ada data karyawan yang ditemukan.</div>
                    @endforelse
                </div>
            </main>

            @if ($employees->hasPages())
                <footer class="page-footer">
                    {{ $employees->withQueryString()->links('vendor.pagination.custom') }}
                </footer>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterToggleBtn = document.getElementById('filter-toggle-btn');
        const filterContainer = document.getElementById('filter-container');

        const urlParams = new URLSearchParams(window.location.search);
        const hasFilters = ['division_id', 'position_id', 'employee_type', 'status', 'office'].some(param => urlParams.has(param) && urlParams.get(param) !== '');
        
        if (hasFilters) {
            filterContainer.style.display = 'block';
        }

        filterToggleBtn.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent form submission
            if (filterContainer.style.display === 'none') {
                filterContainer.style.display = 'block';
            } else {
                filterContainer.style.display = 'none';
            }
        });
    });
</script>
@endpush
