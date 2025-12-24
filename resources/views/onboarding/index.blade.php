@extends('layouts.admin')

@section('title', 'Onboarding Management')

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

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .page-header h2 {
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

    /* TABLE */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        border-radius: 10px;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
        font-family: 'Manrope', sans-serif;
    }

    .custom-table thead th {
        background-color: #DFD9B6;
        color: #000;
        font-weight: 600;
        padding: 12px;
        border: 1px solid #aaa;
        text-align: center;
    }

    .custom-table tbody td {
        background-color: #F3F1E0;
        padding: 12px;
        border: 1px solid #aaa;
        text-align: center;
        vertical-align: middle;
    }

    .actions {
        display: flex;
        justify-content: center;
        gap: 8px;
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .btn-view {
        background-color: #b44343ff;
        color: white;
    }
    .btn-view:hover {
        background-color: #333;
        color: white;
    }

    .btn-edit {
        background-color: #e0a800;
        color: #000;
    }

    .btn-delete {
        background-color: #dc3545;
        color: white;
    }

    /* PAGINATION */
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
</style>
@endpush

@section('content_header')
<div class="header-with-icon">
    <svg class="custom-hamburger" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
        <path fill-rule="evenodd"
            d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
    </svg>
    Onboarding Management
</div>
@endsection

@section('content')
<div class="page-header">
    <h2>Onboarding Documents</h2>
    <a href="{{ route('onboarding.create') }}" class="btn-add">+ Add Document</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-responsive">
    <table class="custom-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Division</th>
                <th>Status</th>
                <th width="200">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $doc)
                <tr>
                    <td>{{ $doc->title }}</td>
                    <td>{{ $doc->division?->name ?? 'Umum (Semua Divisi)' }}</td>
                    <td>
                        @if($doc->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Non-Active</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('onboarding.show', $doc->id) }}" class="btn-action btn-view">
                                Details
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">There are no onboarding documents yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $documents->links('pagination::custom') }}
</div>
@endsection
