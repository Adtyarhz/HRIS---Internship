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
    }

    .btn-detail,
    .btn-edit,
    .btn-delete {
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

    .btn-detail {
        background-color: #4b7bec;
        color: white;
    }
    .btn-detail:hover {
        background-color: #3867d6;
    }

    .btn-edit {
        background-color: #f7b731;
        color: white;
    }
    .btn-edit:hover {
        background-color: #e1a500;
    }

    .btn-delete {
        background-color: #eb3b5a;
        color: white;
    }
    .btn-delete:hover {
        background-color: #c23616;
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
    .badge-danger { background-color: #eb3b5a; color: white; }

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
    <div class="header-with-icon">
        <svg class="custom-hamburger" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"
            xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
                d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
        </svg>
        Overtime Applications
    </div>
@endsection

@section('content')
    <div class="overtime-header">
    <h2>Overtime Applications</h2>

    @php
        $user = Auth::user();
        $employee = $user->employee ?? null;
        $divisionId = $employee->division_id ?? null;

        // cek apakah ada manager di divisi
        $hasManager = false;
        if ($divisionId) {
            $hasManager = \App\Models\Employee::where('division_id', $divisionId)
                ->whereHas('user', function ($q) {
                    $q->where('role', 'manager');
                })
                ->exists();
        }
    @endphp

    {{-- tampilkan button sesuai rules --}}
    @if(
        ($user->role === 'manager' && $hasManager) ||
        ($user->role === 'section_head' && !$hasManager) ||
        in_array($user->role, ['hc', 'superadmin'])
    )
        <a href="{{ route('overtime-applications.create') }}" class="btn-add">+ New Application</a>
    @endif
</div>

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
                        <a href="{{ route('overtime-applications.show', $application->id) }}" class="btn-detail">Detail</a>

                        @if(
                            $application->status == 'Pending' &&
                            (
                                ($user->role === 'manager' && $hasManager) ||
                                ($user->role === 'section_head' && !$hasManager) ||
                                in_array($user->role, ['hc', 'superadmin'])
                            )
                        )
                            <a href="{{ route('overtime-applications.edit', $application->id) }}" class="btn-edit">Edit</a>
                            <form action="{{ route('overtime-applications.destroy', $application->id) }}" method="POST"
                                  style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete" onclick="return confirm('Delete this application?')">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No overtime applications found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $applications->links('vendor.pagination.custom') }}
    </div>
@endsection
