@extends('layouts.admin')

@section('title', 'Data Karyawan')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}" />
@endpush

@section('content')
    <div class="card-body">
        {{-- Search & Filter Form --}}
        <form action="{{ route('employees.index') }}" method="GET">
            <header class="page-controls">
                <div class="search-and-filter-container">
                    <div class="search-container">
                        <h2 class="search-title">Search Employee</h2>
                        <input type="text" name="search" placeholder="Input Employee’s Name or NIP" class="search-input"
                            value="{{ request('search') }}">
                    </div>
                    <div class="main-actions">
                        <button type="submit" class="search-button">Search</button>
                        <button type="button" class="btn-filter" id="filter-toggle-btn">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn-reset" id="filter-reset" style="display: none;">
                            Reset
                        </a>
                        @if(auth()->user()->role === 'superadmin')
                        <a href="{{ route('employees.create') }}" class="add-employee-button">
                            <span class="add-icon">+</span>
                            Add New Employee
                        </a>
                        @endif
                    </div>

                    {{-- Collapsible Filter Section --}}
                    <div class="filter-section" id="filter-container" style="display: none;">
                        <div class="filter-grid">
                            <div class="filter-column">
                                <div class="filter-item">
                                    <label for="division_id">Division</label>
                                    <select name="division_id" id="division_id" class="form-control">
                                        <option value="">All Divisions</option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}"
                                                {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-item">
                                    <label for="employee_type">Employment Type</label>
                                    <select name="employee_type" id="employee_type" class="form-control">
                                        <option value="">All Types</option>
                                        <option value="Kontrak" {{ request('employee_type') == 'Kontrak' ? 'selected' : '' }}>
                                            Kontrak</option>
                                        <option value="Magang" {{ request('employee_type') == 'Magang' ? 'selected' : '' }}>Magang
                                        </option>
                                        <option value="Masa Percobaan"
                                            {{ request('employee_type') == 'Masa Percobaan' ? 'selected' : '' }}>Masa Percobaan
                                        </option>
                                        <option value="Fulltime" {{ request('employee_type') == 'Fulltime' ? 'selected' : '' }}>
                                            Fulltime</option>
                                    </select>
                                </div>
                            </div>
                            <div class="filter-column">
                                <div class="filter-item">
                                    <label for="position_id">Position</label>
                                    <select name="position_id" id="position_id" class="form-control">
                                        <option value="">All Positions</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}"
                                                {{ request('position_id') == $position->id ? 'selected' : '' }}>
                                                {{ $position->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-item">
                                    <label for="office">Office</label>
                                    <select name="office" id="office" class="form-control">
                                        <option value="">All Offices</option>
                                        <option value="Kantor Pusat" {{ request('office') == 'Kantor Pusat' ? 'selected' : '' }}>
                                            Kantor Pusat</option>
                                        <option value="Kantor Cabang" {{ request('office') == 'Kantor Cabang' ? 'selected' : '' }}>
                                            Kantor Cabang</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        </form>

        <!-- Notifications -->
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="employee-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Employee Name</th>
                    <th>Position & Division</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $index => $employee)
                    <tr>
                        <td class="col-no" data-label="No.">
                            {{ $index + 1 + ($employees->currentPage() - 1) * $employees->perPage() }}</td>
                        <td class="col-employee" data-label="Karyawan">
                            <div class="employee-wrapper">
                                <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : 'https://placehold.co/45x45/9A3B3B/FFFFFF?text=' . strtoupper(substr($employee->full_name, 0, 1)) }}"
                                    alt="Avatar Karyawan" class="employee-avatar">
                                <div class="employee-info">
                                    <div class="employee-name">{{ $employee->full_name }}</div>
                                    <div class="employee-id">NIP: {{ $employee->nip }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="col-details" data-label="Jabatan/Divisi">
                            <div class="jabatan-divisi">
                                <div class="employee-position">{{ $employee->position->title ?? 'Belum Diatur' }}</div>
                                <div class="employee-division">{{ $employee->division->name ?? 'Belum Datur' }}</div>
                            </div>
                        </td>
                        <td class="col-status" data-label="Status">
                            <div class="status-wrapper">
                                <div
                                    class="status-badge {{ $employee->status == 'Aktif' ? 'status-active' : 'status-inactive' }}">
                                    {{ $employee->status == 'Aktif' ? 'Active' : 'Inactive' }}
                                </div>
                            </div>
                        </td>
                        <td class="col-actions" data-label="Aksi">
                            <div class="action-wrapper">
                                <a href="{{ route('employees.show', $employee) }}" class="btn-detail">See Employee</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-row">Tidak ada data karyawan yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($employees->hasPages())
            <footer class="page-footer">
                {{ $employees->withQueryString()->links('vendor.pagination.custom') }}
            </footer>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterToggleBtn = document.getElementById('filter-toggle-btn');
            const filterContainer = document.getElementById('filter-container');
            const resetButton = document.getElementById('filter-reset');

            const urlParams = new URLSearchParams(window.location.search);
            const hasFilters = ['division_id', 'position_id', 'employee_type', 'office'].some(param =>
                urlParams.has(param) && urlParams.get(param) !== '');

            if (hasFilters) {
                filterContainer.style.display = 'block';
                resetButton.style.display = 'flex';
            }

            filterToggleBtn.addEventListener('click', function(event) {
                event.preventDefault();
                
                if (filterContainer.style.display === 'none' || filterContainer.style.display === '') {
                    filterContainer.style.display = 'block';
                    resetButton.style.display = 'flex';
                } else {
                    filterContainer.style.display = 'none';
                    resetButton.style.display = 'none';
                }
            });
        });
    </script>
@endpush