@extends('layouts.admin')

@section('title', 'Employee Edit Requests')

@section('content_header')
    <div class="header-with-icon d-flex align-items-center gap-2">
        <iconify-icon icon="charm:git-request" width="24" height="24"></iconify-icon>
        <span>Employee Request</span>
    </div>
@endsection

@section('content')
<div class="container">
    <h1>List of Employee Data Change Requests</h1>

    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered table-striped custom-table text-center">
        <thead class="table-secondary">
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
            @forelse($requests as $key => $req)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $req->employee->full_name ?? '-' }}</td>
                    <td>
                        @if($req->status == 'waiting')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($req->status == 'approved')
                            <span class="badge bg-success">Approved</span>
                        @else
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </td>
                    <td>{{ $req->requested_at ? \Carbon\Carbon::parse($req->requested_at)->format('d-m-Y H:i') : '-' }}</td>
                    <td>{{ $req->approvedBy->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('employee-edit-requests.show', $req->id) }}" class="btn btn-sm btn-primary">Details</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No change requests yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('styles')
<style>
    .custom-table tbody tr {
        background-color: #f9f9f9; /* light gray */
    }
    .custom-table tbody tr:nth-child(even) {
        background-color: #f1f1f1; /* slightly darker */
    }
</style>
@endpush
