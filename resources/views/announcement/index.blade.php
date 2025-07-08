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
        margin-right: 6px; /* Jarak antara ikon dan teks */
        width: 35px; /* Diperbesar untuk sesuai dengan font-size teks 24px */
        height: 35px; /* Diperbesar untuk sesuai dengan font-size teks 24px */
        color: #000; /* Warna ikon */
    }

    .announcement-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
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
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
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

    .btn-filter {
        background-color: black;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
    }

    .btn-detail {
        background-color: black;
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
    }

    .announcement-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Manrope', sans-serif;
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

    /* Styling untuk paginasi */
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
        <svg class="custom-hamburger" width="24" height="24" fill="currentColor" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
        </svg>
        Announcement Management
    </div>
@endsection

@section('content')

    @include('announcement.alert')
    <div class="announcement-header">
        <h2>Announcement</h2>
        <a href="{{ route('announcement.create') }}" class="btn-add">+ Add Announcement</a>
    </div>

    <form method="GET" action="{{ route('announcement.index') }}" class="filter-form">
        <input type="text" name="search" placeholder="Search Announcement Title" value="{{ request('search') }}">
        <select name="type">
            <option value="">Announcement Type</option>
            <option value="Umum" {{ request('announcement_type') == 'Umum' ? 'selected' : '' }}>Umum</option>
            <option value="Divisi" {{ request('announcement_type') == 'Divisi' ? 'selected' : '' }}>Divisi</option>
            <option value="Urgent" {{ request('announcement_type') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
            <option value="Informasi" {{ request('announcement_type') == 'Informasi' ? 'selected' : '' }}>Informasi</option>
            <option value="Polling" {{ request('announcement_type') == 'Polling' ? 'selected' : '' }}>Polling</option>
        </select>
        <input type="text" name="label" placeholder="Search by Label(HR, IT, ext..." value="{{ request('label') }}">
        <button type="submit" class="btn-filter">Filter</button>
    </form>

    <table class="announcement-table">
        <thead>
            <tr>
                <th>Announcement Title</th>
                <th>Announcement Type</th>
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
                        @if ($announcement->attachment_file)
                            <a href="{{ asset('storage/announcement' . $announcement->attachment_file) }}" class="btn-view" target="_blank">View PDF Doc</a>
                        @else
                            N/A
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

    <!-- Tambahkan tautan paginasi sederhana (Previous dan Next) -->
    <div class="pagination">
        {{ $announcements->links('pagination::custom') }}
    </div>
@endsection