@extends('layouts.admin')

@section('title', 'Employee Edit Requests')
@section('header_icon', 'charm--git-request')
@section('content_header', 'Employee Data Change Requests')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}" />
    <style>
        /* Tambahan gaya status */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
            color: white;
            text-align: center;
            min-width: 90px;
        }
        .status-active { background-color: #28a745; } /* Hijau */
        .status-pending { background-color: #ffc107; color: #212529; } /* Kuning */
        .status-rejected { background-color: #dc3545; } /* Merah */
    </style>
@endpush

@section('content')
    <div class="card-body">
        {{-- 🔍 Search & Filter --}}
        <form action="{{ route('employee-edit-requests.index') }}" method="GET">
            <header class="page-controls">
                <div class="search-and-filter-container">
                    {{-- Search Box --}}
                    <div class="search-container">
                        <h2 class="search-title">Search Request</h2>
                        <input type="text" name="search" placeholder="Input Employee’s Name"
                            class="search-input" value="{{ request('search') }}">
                    </div>

                    {{-- Buttons --}}
                    <div class="main-actions">
                        <button type="submit" class="search-button">Search</button>

                        <button type="button" class="btn-filter" id="filter-toggle-btn">
                            <i class="fas fa-filter"></i> Filter
                        </button>

                        <a href="{{ route('employee-edit-requests.index') }}" class="btn-reset" id="filter-reset"
                            style="display: none;">Reset</a>
                    </div>

                    {{-- Collapsible Filter Section --}}
                    <div class="filter-section" id="filter-container" style="display: none;">
                        <div class="filter-grid">
                            <div class="filter-column">
                                <div class="filter-item">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div class="filter-column">
                                <div class="filter-item">
                                    <label for="sort">Sort By Date</label>
                                    <select name="sort" id="sort" class="form-control">
                                        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Newest First</option>
                                        <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        </form>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Table --}}
        <table class="employee-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Employee</th>
                    <th>Status</th>
                    <th>Submission Date</th>
                    <th>Approved By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $index => $req)
                    <tr>
                        <td class="col-no">{{ $index + 1 + ($requests->currentPage() - 1) * $requests->perPage() }}</td>
                        <td class="col-employee">
                            <div class="employee-wrapper">
                                <img src="{{ $req->employee->photo ? asset('storage/' . $req->employee->photo) : 'https://placehold.co/45x45/9A3B3B/FFFFFF?text=' . strtoupper(substr($req->employee->full_name ?? '-', 0, 1)) }}"
                                    alt="Avatar" class="employee-avatar">
                                <div class="employee-info">
                                    <div class="employee-name">{{ $req->employee->full_name ?? '-' }}</div>
                                    <div class="employee-id">ID: {{ $req->employee->id ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="col-status">
                            <div class="status-wrapper">
                                @if ($req->status === 'waiting')
                                    <div class="status-badge status-pending">Pending</div>
                                @elseif ($req->status === 'approved')
                                    <div class="status-badge status-active">Approved</div>
                                @elseif ($req->status === 'rejected')
                                    <div class="status-badge status-rejected">Rejected</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            {{ $req->requested_at ? \Carbon\Carbon::parse($req->requested_at)->format('d-m-Y H:i') : '-' }}
                        </td>
                        <td>
                            {{ $req->approvedBy->name ?? '-' }}
                        </td>
                        <td class="col-actions">
                            <div class="action-wrapper">
                                <a href="{{ route('employee-edit-requests.show', $req->id) }}" class="btn-detail">See Detail</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-row">No change requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if ($requests->hasPages())
            <footer class="page-footer">
                {{ $requests->withQueryString()->links('vendor.pagination.custom') }}
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
            const hasFilters = ['status', 'sort'].some(param =>
                urlParams.has(param) && urlParams.get(param) !== ''
            );

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
