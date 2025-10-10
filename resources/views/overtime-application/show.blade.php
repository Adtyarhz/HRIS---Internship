@extends('layouts.admin')

@section('title', 'Detail Overtime Application')

@push('styles')
<style>
    .detail-container {
        background-color: #F3F1E0;
        padding: 20px;
        border-radius: 8px;
        font-family: 'Manrope', sans-serif;
        margin-bottom: 20px;
    }

    .detail-container h2 {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 1rem;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
    }

    .detail-table th,
    .detail-table td {
        padding: 10px;
        border: 1px solid #ccc;
        vertical-align: top;
    }

    .btn-edit, .btn-approve, .btn-reject, .btn-cancel, .btn-delete {
        padding: 7px 15px;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        color: white;
        display: inline-block;
    }

    .btn-back, .btn-edit {
        margin-right: 10px;
    }

    .btn-approve {
        margin-right: 15px;
    }

    .btn-back { 
    background-color: #383535ff; 
    padding: 5px 17px;
    border-radius: 6px;
    font-size: 14px;        /* ✅ Tambahkan baris ini */
    font-weight: 400;       /* ubah 10 jadi 400 (karena 10 tidak valid untuk font-weight) */
    font-family: 'Manrope', sans-serif;
    text-decoration: none;
    color: white;
    display: inline-block;
    margin-bottom: 5px;
}

    .btn-back:hover { 
        background-color: #555; 
        color: white;              /* biar teks tetap putih */
        text-decoration: none;     /* hilangkan underline */
    }
    

    .btn-edit { background-color: #d2ad07ff; }
    .btn-edit:hover { 
        background-color: #879011ff; 
        color: white;              /* biar teks tetap putih */
        text-decoration: none;     /* hilangkan underline */}

    .btn-approve {
        background-color: #28a745;
        border: none;
    }
    .btn-approve:hover { background-color: #218838; }

    .btn-reject {
        background-color: #dc3545;
        border: none;
    }
    .btn-reject:hover { background-color: #c82333; }

    .btn-cancel {
        background-color: #6c757d;
    }
    .btn-cancel:hover { background-color: #5a6268; }

    .btn-delete {
        background-color: #b91c1c;
        border: none;
    }
    .btn-delete:hover { background-color: #991b1b; }

    .reject-form input {
        height: 42px;
        border-radius: 8px;
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
        Detail Overtime Application
    </div>
@endsection

@section('content')
     <a href="{{ route('overtime-applications.index') }}" class="action-button btn-back">
                    <i class="fas fa-arrow-left"></i> Back to List</a>
    {{-- =============== DETAIL =============== --}}
    <div class="detail-container">
        <h2>Overtime Application Detail</h2>
        <table class="detail-table">
            <tr>
                <th>Employee</th>
                <td>{{ $application->employee->full_name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Date & Time</th>
                <td>
                    {{ \Carbon\Carbon::parse($application->start_datetime)->format('d/m/Y H:i') }}
                    -
                    {{ \Carbon\Carbon::parse($application->end_datetime)->format('d/m/Y H:i') }}
                </td>
            </tr>
            <tr>
                <th>Reason</th>
                <td>{!! nl2br(e($application->reason)) !!}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $application->status }}</td>
            </tr>
        </table>
    </div>

    {{-- =============== TASKS =============== --}}
    <div class="detail-container">
        <h2>Overtime Tasks</h2>
        @if ($application->tasks->count() > 0)
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>Task Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($application->tasks as $task)
                        <tr>
                            <td>{{ $task->task_description }}</td>
                            <td>
                                @if ($task->is_completed)
                                    <span class="badge bg-success">Finished</span><br>
                                    <small>Selesai pada: {{ $task->completed_at?->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="badge bg-secondary">Not finished yet</span>
                                @endif
                            </td>
                            <td>
                                @if ($application->status === 'Approved' && $application->employee->user_id === Auth::id())
                                    <form action="{{ route('overtime-tasks.toggle', $task->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="btn btn-sm {{ $task->is_completed ? 'btn-warning' : 'btn-success' }}">
                                            {{ $task->is_completed ? 'Mark Yet' : 'Mark Complete' }}
                                        </button>
                                    </form>
                                @else
                                    <em>-</em>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p><i>Tidak ada task lembur.</i></p>
        @endif
    </div>

    {{-- =============== ACTION BUTTONS =============== --}}
    <div class="d-flex justify-content-between mb-4 align-items-center">
        {{-- Left --}}
        <div class="d-flex gap-3">
        @php
            $user = Auth::user();
            $employee = $user->employee ?? null;
            $divisionId = $employee->division_id ?? null;

            $hasManager = false;
            if ($divisionId) {
                $hasManager = \App\Models\Employee::where('division_id', $divisionId)
                    ->whereHas('user', function ($q) {
                        $q->where('role', 'manager');
                    })
                    ->exists();
            }
        @endphp

        {{-- ✅ Tombol EDIT hanya muncul jika status masih Pending --}}
        @if(
            $application->status == 'Pending' &&
            (
                ($user->role === 'manager' && $hasManager) ||
                ($user->role === 'section_head' && !$hasManager) ||
                in_array($user->role, ['hc', 'superadmin'])
            )
        )
            <a href="{{ route('overtime-applications.edit', $application->id) }}" class="btn-edit">Edit</a>
        @endif

        {{-- ✅ Tombol DELETE muncul jika:
             1. Status Pending (dan role valid)
             2. Status Approved/Rejected (hanya HC & Superadmin)
        --}}
        @if(
            (
                $application->status == 'Pending' &&
                (
                    ($user->role === 'manager' && $hasManager) ||
                    ($user->role === 'section_head' && !$hasManager) ||
                    in_array($user->role, ['hc', 'superadmin'])
                )
            )
            ||
            (
                in_array($application->status, ['Approved', 'Rejected']) &&
                in_array($user->role, ['hc', 'superadmin'])
            )
        )
            <form action="{{ route('overtime-applications.destroy', $application->id) }}" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete" onclick="return confirm('Delete this application?')">
                    Delete
                </button>
            </form>
        @endif
    </div>

        {{-- Right --}}
        @if ($application->status === 'Pending' && in_array(Auth::user()->role, ['hc', 'superadmin']))
            <div class="d-flex gap-3 align-items-center">
                {{-- Approve --}}
                <form action="{{ route('overtime.approve', $application->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-approve">Approve</button>
                </form>

                {{-- Reject (pakai modal) --}}
                <button type="button" class="btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    Reject
                </button>
            </div>
        @endif
    </div>

    {{-- =============== MODAL REJECT =============== --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('overtime.reject', $application->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reason for Rejection</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <textarea name="reason" class="form-control" rows="3" placeholder="Tuliskan alasan menolak..." required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-reject">Submit Reject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- =============== HISTORY =============== --}}
    @if (in_array(Auth::user()->role, ['hc', 'superadmin']) || Auth::id() === $application->requested_by)
        <div class="detail-container">
            <h2>History</h2>
            @if ($application->histories->count() > 0)
                <table class="detail-table text-center">
                    <tr>
                        <th>Date</th>
                        <th>Actions</th>
                        <th>Done by</th>
                        <th>Information</th>
                    </tr>
                    @foreach ($application->histories as $history)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $history->action_type }}</td>
                            <td>{{ $history->actor->name ?? '-' }}</td>
                            <td>{{ $history->description }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p><i>Belum ada riwayat aksi.</i></p>
            @endif
        </div>
    @endif
@endsection