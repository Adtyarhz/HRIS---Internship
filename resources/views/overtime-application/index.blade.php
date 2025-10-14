@extends('layouts.admin')

@section('title', 'Overtime Applications')

@push('styles')
<style>
    .header-with-icon {
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 5px;
    }

    .header-with-icon .custom-hamburger {
        margin-right: 6px;
        width: 35px;
        height: 35px;
        color: #000;
    }

    .overtime-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .overtime-header h2 {
        font-size: 24px;
        font-weight: bold;
        font-family: 'Noto Sans Georgian', sans-serif;
    }

    .btn-add {
        background-color: #9A3B3B;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .btn-add:hover {
        background-color: #7a2f2f;
        color: white;
        text-decoration: none;
    }

    .btn-detail {
        background-color: #4b7bec;
        color: white;
        border: none;
        padding: 6px 14px;
        border-radius: 6px;
        font-weight: bold;
        font-size: 14px;
        font-family: 'Manrope', sans-serif;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.3s;
        margin: 0 2px;
    }

    .btn-detail:hover {
        background-color: #3867d6;
        color: white;
        text-decoration: none;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: hidden; /* default: tidak scroll di desktop */
    }

    @media (max-width: 992px) {
        .table-wrapper {
            overflow-x: auto; /* scroll hanya aktif di mobile */
            -webkit-overflow-scrolling: touch;
        }

        .overtime-table {
            min-width: 800px; /* jaga lebar minimum supaya scroll muncul */
        }
    }

    .overtime-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Manrope', sans-serif;
    }

    .overtime-table thead th {
        background-color: #DFD9B6;
        color: #000;
        font-weight: 600;
        padding: 12px 10px;
        border: 1px solid #aaa;
        text-align: center;
        white-space: nowrap;
    }

    .overtime-table tbody td {
        background-color: #F3F1E0;
        padding: 12px 10px;
        border: 1px solid #aaa;
        vertical-align: middle;
        text-align: center;
    }

    .overtime-table .actions {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: bold;
    }

    .badge-warning { background-color: #f7b731; color: white; }
    .badge-success { background-color: #20bf6b; color: white; }
    .badge-danger  { background-color: #eb3b5a; color: white; }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }

    .pagination a,
    .pagination span {
        padding: 4px 8px;
        border: 1px solid #ccc;
        border-radius: 6px;
        text-decoration: none;
        color: #000;
        font-size: 14px;
        font-family: 'Manrope', sans-serif;
    }

    .pagination a:hover {
        background-color: #DFD9B6;
    }

    .pagination .disabled {
        color: #999;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content_header')
    <div class="header-with-icon d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" 
             width="24" height="24" viewBox="0 0 24 24" 
             class="mr-2" fill="currentColor">
            <path d="M12 20A8 8 0 1 0 12 4a8 8 0 0 0 0 16m0-18a10 10 0 1 1 0 20
                     a10 10 0 0 1 0-20m.5 5v5.25l4.5 2.67l-.75 1.23L11 11V7h1.5Z"/>
        </svg>
        Overtime Application
    </div>
@endsection

@section('content')
<div class="overtime-header">
    <h2>Overtime Applications</h2>

    @php
        $user = Auth::user();
        $employee = $user->employee ?? null;
        $divisionId = $employee->division_id ?? null;
        $hasManager = false;
        if ($divisionId) {
            $hasManager = \App\Models\Employee::where('division_id', $divisionId)
                ->whereHas('user', fn($q) => $q->where('role', 'manager'))
                ->exists();
        }
    @endphp

    @if(
        ($user->role === 'manager' && $hasManager) ||
        ($user->role === 'section_head' && !$hasManager) ||
        in_array($user->role, ['hc', 'superadmin'])
    )
        <a href="{{ route('overtime-applications.create') }}" class="btn-add">+ New Application</a>
    @endif
</div>

<div class="table-wrapper">
    <table class="overtime-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Requested By</th>
                <th>Date/Time</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $application)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $application->employee->full_name ?? '-' }}</td>
                    <td>{{ $application->requester->name ?? '-' }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($application->start_datetime)->format('d/m/Y H:i') }}
                        -
                        {{ \Carbon\Carbon::parse($application->end_datetime)->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        @if($application->status === 'Pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($application->status === 'Approved')
                            <span class="badge badge-success">Approved</span>
                        @else
                            <span class="badge badge-danger">Rejected</span>
                        @endif
                    </td>
                    <td>{!! nl2br(e($application->reason)) !!}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('overtime-applications.show', $application->id) }}" class="btn-detail">Details</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No overtime applications found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $applications->links('vendor.pagination.custom') }}
</div>
@endsection
