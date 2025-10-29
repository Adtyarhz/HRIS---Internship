@extends('layouts.admin')

@section('title', 'Announcement Management')

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

    .announcement-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .announcement-header h2 {
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

    /* === FILTER FORM === */
    .filter-form {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .filter-form input,
    .filter-form select {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        font-family: 'Manrope', sans-serif;
    }

    .filter-form input {
        width: 220px;
    }

    .filter-form select {
        width: 180px;
    }

    .btn-filter {
        background-color: #b44343ff;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-filter:hover {
        background-color: #7a2f2f;
        color: white;
    }

    .btn-detail {
        background-color: #b44343ff;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: bold;
        font-size: 14px;
        font-family: 'Manrope', sans-serif;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn-detail:hover {
        background-color: #333;
        color: white;
    }

    /* === TABLE === */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 10px;
    }

    .announcement-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Manrope', sans-serif;
        min-width: 700px;
    }

    .announcement-table thead th {
        background-color: #DFD9B6;
        color: #000;
        font-weight: 600;
        padding: 12px 10px;
        border: 1px solid #aaa;
        text-align: center;
    }

    .announcement-table tbody td {
        background-color: #F3F1E0;
        padding: 12px 10px;
        border: 1px solid #aaa;
        vertical-align: middle;
        text-align: center;
    }

    .announcement-table .actions {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* === PAGINATION === */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
    }

    .pagination a,
    .pagination span {
        padding: 6px 10px;
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

    /* === RESPONSIVE MODE === */
    @media (max-width: 991px) {
        .filter-form {
            flex-wrap: wrap;
        }
        .filter-form input,
        .filter-form select,
        .btn-filter {
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .announcement-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .announcement-header h2 {
            font-size: 20px;
        }

        .btn-add {
            width: 100%;
            text-align: center;
        }

        .announcement-table {
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .announcement-header h2 {
            font-size: 18px;
        }

        .btn-detail,
        .btn-filter {
            font-size: 13px;
            padding: 6px 10px;
        }

        .pagination a,
        .pagination span {
            font-size: 12px;
            padding: 4px 8px;
        }
    }
</style>
@endpush

@section('content_header')
    <div class="header-with-icon">
        <svg class="custom-hamburger" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd"
                d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
        </svg>
        Announcement Management
    </div>
@endsection

@section('content')
    <div class="announcement-header">
        <h2>Announcement</h2>
        <a href="{{ route('announcement.create') }}" class="btn-add">+ Add Announcement</a>
    </div>

    <form method="GET" action="{{ route('announcement.index') }}" class="filter-form">
        <input type="text" name="search" placeholder="Search Announcement Title" value="{{ request('search') }}">
        <select name="type">
            <option value="">Announcement Type</option>
            <option value="Umum" {{ request('announcement_type') == 'Umum' ? 'selected' : '' }}>Umum</option>
            <option value="Urgent" {{ request('announcement_type') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
            <option value="Informasi" {{ request('announcement_type') == 'Informasi' ? 'selected' : '' }}>Informasi</option>
            <option value="Polling" {{ request('announcement_type') == 'Polling' ? 'selected' : '' }}>Polling</option>
        </select>
        <input type="text" name="label" placeholder="Search by Label (Divisi, etc...)" value="{{ request('label') }}">
        <button type="submit" class="btn-filter">Filter</button>
    </form>

    <div class="table-responsive">
        <table class="announcement-table">
            <thead>
                <tr>
                    <th>Announcement Title</th>
                    <th>Type</th>
                    <th>Label</th>
                    <th>Attachment</th>
                    <th>Upload Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($announcements as $announcement)
                    <tr>
                        <td>{{ $announcement->title }}</td>
                        <td>{{ $announcement->announcement_type }}</td>
                        <td>{{ $announcement->label }}</td>
                        <td>
                            @if (!empty($announcement->attachment_file))
                                <a href="{{ asset('storage/' . $announcement->attachment_file) }}" 
                                   class="btn-detail" target="_blank" title="Click here to view the attachment">
                                    <i class="fas fa-file-pdf"></i> View File
                                </a>
                            @else
                                <span style="color:#999; font-style:italic;">No File</span>
                            @endif
                        </td>
                        <td>{{ $announcement->created_at->format('d F Y H:i') }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('announcement.show', $announcement->id) }}" class="btn-detail">Detail</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No announcements found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $announcements->links('pagination::custom') }}
    </div>
@endsection
