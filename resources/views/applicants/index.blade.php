@extends('layouts.admin')

@section('title', 'Applicant Management')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    .filter-btn {
        background-color: #DFD9B6;
        transition: background-color 0.2s ease;
        border-radius: 6px;
    }

    .filter-btn:hover,
    .filter-btn:focus {
        background-color: #fff;
    }

    .filter-btn.show {
        background-color: #fff !important;
    }
</style>
@endpush

@section('content_header')
    <div class="header-with-icon d-flex align-items-center">
        <!-- Ikon Recruitment -->
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 2048 2048" class="mr-2">
            <path fill="currentColor"
                d="M2048 1280v768H1024v-768h256v-256h512v256zm-640 0h256v-128h-256zm512 384h-128v128h-128v-128h-256v128h-128v-128h-128v256h768zm0-256h-768v128h768zm-355-512q-54-61-128-94t-157-34q-80 0-149 30t-122 82t-83 123t-30 149q0 92-41 173t-116 136q45 23 84 53t73 68v338q0-79-30-149t-82-122t-123-83t-149-30q-80 0-149 30t-122 82t-83 123t-30 149H0q0-73 20-141t57-129t90-108t118-81q-74-54-115-135t-42-174q0-79 30-149t82-122t122-83t150-30q92 0 173 41t136 116q38-75 97-134t135-98q-74-54-115-135t-42-174q0-79 30-149t82-122t122-83t150-30q79 0 149 30t122 82t83 123t30 149q0 92-41 173t-116 136q68 34 123 85t93 118zM512 1408q53 0 99-20t82-55t55-81t20-100q0-53-20-99t-55-82t-81-55t-100-20q-53 0-99 20t-82 55t-55 81t-20 100q0 53 20 99t55 82t81 55t100 20m512-1024q0 53 20 99t55 82t81 55t100 20q53 0 99-20t82-55t55-81t20-100q0-53-20-99t-55-82t-81-55t-100-20q-53 0-99 20t-82 55t-55 81t-20 100"/>
        </svg>
        <h1 class="header-title mb-0">Recruitment Applicant</h1>
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
        @if(auth()->user()->role === 'superadmin')
        <a href="{{ route('applicants.create') }}" class="btn-add" style="margin-left: auto;">+ Add New Applicant</a>
        @endif
    </form>

    <table class="applicant-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Applicant Fullname</th>
                <th>
    <div class="dropdown d-inline-block">
        <span>Applied Position</span>
        <button class="btn btn-sm border-0 dropdown-toggle filter-btn" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-filter" style="font-size: 1rem; color: #000;"></i>
        </button>
        <ul class="dropdown-menu" aria-labelledby="sortDropdown">
            <li>
                <a class="dropdown-item" href="{{ route('applicants.index', array_merge(request()->all(), ['sort' => 'id', 'direction' => 'asc'])) }}">
                    <i class="bi bi-hash"></i> ID (Asc)
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('applicants.index', array_merge(request()->all(), ['sort' => 'id', 'direction' => 'desc'])) }}">
                    <i class="bi bi-hash"></i> ID (Desc)
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('applicants.index', array_merge(request()->all(), ['sort' => 'applied_position', 'direction' => 'asc'])) }}">
                    <i class="bi bi-sort-alpha-down"></i> A to Z
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('applicants.index', array_merge(request()->all(), ['sort' => 'applied_position', 'direction' => 'desc'])) }}">
                    <i class="bi bi-sort-alpha-up"></i> Z to A
                </a>
            </li>
        </ul>
    </div>
</th>
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
                    <td><a href="{{ route('interview-schedule.index', $applicant) }}">See Interview Schedule</a></td>
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
