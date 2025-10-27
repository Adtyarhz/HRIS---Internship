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

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
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

        .search-container {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 10px;
        }

        .search-input {
            width: 25%;
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Manrope', sans-serif;
        }

        .search-button {
            background-color: #9A3B3B;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .search-button:hover {
            background-color: #7a2f2f;
        }

        .filter-section {
            background-color: #F3F1E0;
            border-radius: 8px;
            padding: 15px 20px;
            margin-top: 10px;
            display: none;
        }

        .filter-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            width: 200px;
        }

        .filter-item label {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .filter-item select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .btn-filter {
            background-color: #7d7b7bff;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-reset {
            background-color: #f4f4f4a0;
            color: #343030ff;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: none;
            align-items: center;
        }

         .btn-export {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        font-weight: 600;
        border: none;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.25s ease-in-out;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
    }

    .btn-export:hover {
        background: linear-gradient(135deg, #27ae60, #219150);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        color: #fff;
    }

    .btn-export i {
        font-size: 1.2rem;
    }

    .btn-export span {
        font-size: 0.95rem;
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
            text-align: center;
        }

        .link-applicant {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .link-applicant:hover {
            text-decoration: underline;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a, .pagination span {
            padding: 4px 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            text-decoration: none;
            color: #000;
            font-size: 14px;
        }

        .pagination a:hover {
            background-color: #DFD9B6;
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
    @section('content-wrapper')
    @include('recruitment.tabs')

    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">
                    <div class="announcement-header d-flex justify-content-between align-items-center">
                        <h2>Applicants</h2>
                    </div>

                    {{-- 🔍 Search & Filter --}}
                    <form action="{{ route('applicants.index') }}" method="GET">
                        <div class="search-container">
                            <input type="text" name="search" placeholder="Search Applicant Fullname" class="search-input" value="{{ request('search') }}">
                            <button type="submit" class="search-button">Search</button>

                            <button type="button" class="btn-filter" id="filter-toggle-btn">
                                <i class="fas fa-filter"></i> Filter
                            </button>

                            {{-- 🔽 Tombol Export CSV --}}
                            <a href="{{ route('applicants.export.csv', ['division_id' => request('division_id')]) }}"
                            class="btn-export"
                            title="Export Recruitment Report (CSV)">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                            <span>Report</span>
                            </a>
                            <a href="{{ route('applicants.index') }}" class="btn-reset" id="filter-reset">Reset</a>

                            @if(auth()->user()->role === 'superadmin')
                                <a href="{{ route('applicants.create') }}" class="btn-add" style="margin-left:auto;">+ Add New Applicant</a>
                            @endif
                        </div>

                        {{-- Collapsible Filter --}}
                        <div class="filter-section" id="filter-container">
                            <div class="filter-grid">
                            <div class="filter-item">
                                    <label for="division_id">Division</label>
                                    <select name="division_id" id="division_id" onchange="this.form.submit()">
                                        <option value="">All Divisions</option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                                {{ $division->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="filter-item">
                                    <label for="stage">Recruitment Stage</label>
                                    <select name="stage" id="stage" onchange="this.form.submit()">
                                        <option value="">-- Filter by Current Stage --</option>
                                        <option value="general_knowledge_test" {{ request('stage') == 'general_knowledge_test' ? 'selected' : '' }}>General Knowledge Test</option>
                                        <option value="computer_skills_test" {{ request('stage') == 'computer_skills_test' ? 'selected' : '' }}>Computer Skills Test</option>
                                        <option value="user_assessment" {{ request('stage') == 'user_assessment' ? 'selected' : '' }}>User Assessment</option>
                                        <option value="hc_interview" {{ request('stage') == 'hc_interview' ? 'selected' : '' }}>HC Interview</option>
                                        <option value="bod_interview" {{ request('stage') == 'bod_interview' ? 'selected' : '' }}>BOD Interview</option>
                                        <option value="offering_letter" {{ request('stage') == 'offering_letter' ? 'selected' : '' }}>Offering Letter</option>
                                    </select>
                                </div>

                                <div class="filter-item">
                                    <label for="sort">Sort by Date</label>
                                    <select name="sort" id="sort" onchange="this.form.submit()">
                                        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Newest First</option>
                                        <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Table --}}
                    <table class="applicant-table mt-3">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Applicant Fullname</th>
                                <th>Applied Position</th>
                                <th>Division</th>
                                <th>Recruitment Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($applicants as $index => $applicant)
                                <tr>
                                    <td>{{ $loop->iteration + ($applicants->currentPage() - 1) * $applicants->perPage() }}</td>
                                    <td><a href="{{ route('applicants.show', $applicant->id) }}" class="link-applicant">{{ $applicant->full_name }}</a></td>
                                    <td>{{ $applicant->position?->title ?? '-' }}</td>
                                    <td>{{ $applicant->division?->name ?? '-' }}</td>
                                    <td><a href="{{ route('recruitment-progress.show', $applicant) }}" class="link-applicant">See Recruitment Progress</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No applicants found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="pagination">
                        {{ $applicants->withQueryString()->links('pagination::custom') }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    @endsection

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterToggleBtn = document.getElementById('filter-toggle-btn');
            const filterContainer = document.getElementById('filter-container');
            const resetButton = document.getElementById('filter-reset');

            const urlParams = new URLSearchParams(window.location.search);
            const hasFilters = ['division_id', 'stage', 'sort'].some(param =>
                urlParams.has(param) && urlParams.get(param) !== ''
            );

            if (hasFilters) {
                filterContainer.style.display = 'block';
                resetButton.style.display = 'flex';
            }

            filterToggleBtn.addEventListener('click', function(event) {
                event.preventDefault();
                if (filterContainer.style.display === 'none' || filterContainer.style.display === '') {
                    filterContainer.style.display = 'block';
                    resetButton.style.display = 'flex';
                } else {
                    filterContainer.style.display = 'none';
                    resetButton.style.display = 'none';
                }
            });
        });
    </script>
    @endpush
