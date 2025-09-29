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

    .btn-back, .btn-edit, .btn-approve, .btn-reject, .btn-cancel {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        color: white;
        display: inline-block;
    }
    .btn-back, .btn-edit {
        margin-right: 10px; /* kasih jarak tambahan */
    }
    .btn-approve {
        margin-right: 15px;
    }
    .btn-back { background-color: #000; }
    .btn-back:hover { background-color: #555; }

    .btn-edit { background-color: #9A3B3B; }
    .btn-edit:hover { background-color: #7a2f2f; }

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

    /* supaya input sejajar tombol */
    .reject-form input {
        height: 42px;
        border-radius: 8px;
    }
</style>
@endpush

@section('content_header')
    <div class="header-with-icon">
        Detail Overtime Application
    </div>
@endsection

@section('content')
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
                        <th>Deskripsi Task</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($application->tasks as $task)
                        <tr>
                            <td>{{ $task->task_description }}</td>
                            <td>
                                @if ($task->is_completed)
                                    <span class="badge bg-success">Selesai</span><br>
                                    <small>Selesai pada: {{ $task->completed_at?->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="badge bg-secondary">Belum Selesai</span>
                                @endif
                            </td>
                            <td>
                                @if ($application->status === 'Approved' && $application->employee->user_id === Auth::id())
                                    <form action="{{ route('overtime-tasks.toggle', $task->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="btn btn-sm {{ $task->is_completed ? 'btn-warning' : 'btn-success' }}">
                                            {{ $task->is_completed ? 'Tandai Belum' : 'Tandai Selesai' }}
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
        <a href="{{ route('overtime-applications.index') }}" class="btn-back">Back</a>

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
                    <h5 class="modal-title" id="rejectModalLabel">Alasan Penolakan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea name="reason" class="form-control" rows="3" placeholder="Tuliskan alasan menolak..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
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
                        <th>Tanggal</th>
                        <th>Aksi</th>
                        <th>Oleh</th>
                        <th>Keterangan</th>
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
