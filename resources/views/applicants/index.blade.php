@extends('layouts.admin')

@section('title', 'Applicant Management')

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

    .filter-form input[type="text"] {
    width: 25%;
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    font-family: 'Manrope', sans-serif;
}

    .btn-filter {
        background-color: #9A3B3B;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
    }

    .btn-filter:hover {
        background-color: #7a2f2f;
    }

    .link-applicant {
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
        font-family: 'Manrope', sans-serif;
    }

    .link-applicant:hover {
        text-decoration: underline;
    }

    .applicant-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Manrope', sans-serif;
    }

    .applicant-table thead th {
        background-color: #DFD9B6;
        color: #000;
        font-weight: 600;
        padding: 12px 10px;
        border: 1px solid #aaa;
        text-align: center;
    }

    .applicant-table tbody td {
        background-color: #F3F1E0;
        padding: 12px 10px;
        border: 1px solid #aaa;
        vertical-align: middle;
        text-align: center;
    }

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
        <i class="fas fa-users"></i> Recruitment Applicant
    </div>
@endsection

@section('content')
    @include('applicants.alert')

    <div class="announcement-header">
        <h2>Applicants</h2>
    </div>

    <form method="GET" action="{{ route('applicants.index') }}" class="filter-form">
        <input type="text" name="search" placeholder="Search Applicant Fullname" value="{{ request('search') }}">
        <button type="submit" class="btn-filter">Search</button>
        <a href="{{ route('applicants.create') }}" class="btn-add" style="margin-left: auto;">+ Add New Applicant</a>
    </form>

    <table class="applicant-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Applicant Fullname</th>
                <th>Applied Position</th>
                <th>Recruitment Progress</th>
                <th>Interview Schedule</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($applicants as $index => $applicant)
                <tr>
                    <td>{{ $loop->iteration + ($applicants->currentPage() - 1) * $applicants->perPage() }}</td>
                    <td>
                        <a href="{{ route('applicants.show', $applicant->id) }}" class="link-applicant">
                            {{ $applicant->full_name }}
                        </a>
                    </td>
                    <td>{{ $applicant->applied_position }}</td>
                    <td>
                        <a href="{{ route('recruitment-progress.show', $applicant) }}" class="link-applicant">See Recruitment Progress</a>
                    </td>
                    <td><a href="#">See Interview Schedule</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No applicants found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $applicants->links('pagination::custom') }}
    </div>
@endsection
