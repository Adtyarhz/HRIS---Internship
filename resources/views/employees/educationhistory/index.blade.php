@extends('layouts.admin')

@section('title', 'Employee Information')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
        .section-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
        }

        .table-custom th,
        .table-custom td {
            text-align: center;
            vertical-align: middle;
            font-size: 13px;
            white-space: nowrap;
        }

        .table-custom th {
            background-color: #DFD9B6;
            font-weight: 600;
        }

        .table-responsive {
            width: 100%;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .container-fluid {
            padding-bottom: 30px;
        }

        .add-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            max-width: 220px;
            height: 2.5rem;
            background-color: #9a3b3b;
            color: #fff;
            font-family: "Noto Sans Georgian", sans-serif;
            font-size: 15px;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            margin-left: auto;
        }

        .btn-info:hover {
            background-color: #098ba5;
        }

        .add-button:hover {
            background-color: #803030;
            color: #fff;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .material-symbols--edit {
            display: inline-block;
            width: 18px;
            height: 18px;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23fff' d='M3 21v-4.25L16.2 3.575q.3-.275.663-.425t.762-.15t.775.15t.65.45L20.425 5q.3.275.438.65T21 6.4q0 .4-.137.763t-.438.662L7.25 21zM17.6 7.8L19 6.4L17.6 5l-1.4 1.4z'/%3E%3C/svg%3E");
        }

        /* ===== Responsiveness ===== */
        @media (max-width: 992px) {
            .section-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .add-button {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
            }
        }

        /* Mobile: table → card */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: hidden;
            }

            .table-custom,
            .table-custom thead,
            .table-custom tbody,
            .table-custom th,
            .table-custom td,
            .table-custom tr {
                display: block;
                width: 100%;
            }

            .table-custom thead {
                display: none;
            }

            .table-custom tr {
                margin-bottom: 1rem;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 0.75rem;
                background-color: #fff;
            }

            .table-custom td {
                text-align: left !important;
                white-space: normal;
                padding: 6px 8px;
                font-size: 13px;
                border: none;
            }

            .table-custom td::before {
                content: attr(data-label);
                font-weight: 600;
                display: block;
                margin-bottom: 2px;
                color: #333;
            }

            .action-buttons {
                justify-content: flex-start;
                margin-top: 8px;
            }

            .btn-info {
                max-width: 100%;
                font-size: 12px;
                height: 2.2rem;
                padding: 0 10px;
            }

            .btn-cancel {
                width: 100%;
                max-width: 100%;
            }

            .form-buttons-container {
                display: flex;
                justify-content: flex-end;
                margin-top: 2px;
                padding-top: 0px;
            }
        }
    </style>
@endpush

@section('content-wrapper')
    @include('employees.partials.tab-menu', ['employee' => $employee])
    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">

                    {{-- Section Title + Add Button --}}
                    <div class="section-title d-flex justify-content-between align-items-center flex-wrap">
                        Education History
                        <a href="{{ route('employees.educationhistory.create', $employee) }}" class="add-button">
                            <i class="fas fa-plus"></i> Add Education History
                        </a>
                    </div>

                    {{-- Table / Card --}}
                    @if($educationHistories->isEmpty())
                        <div class="text-center text-muted py-4">
                            No education history recorded.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-custom text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Level</th>
                                        <th>Institution</th>
                                        <th>Major</th>
                                        <th>Start - End Year</th>
                                        <th>GPA / Score</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($educationHistories as $index => $education)
                                        <tr>
                                            <td data-label="No.">{{ $index + 1 }}</td>
                                            <td data-label="Level">{{ $education->education_level }}</td>
                                            <td data-label="Institution">{{ $education->institution_name }}</td>
                                            <td data-label="Major">{{ $education->major ?? '-' }}</td>
                                            <td data-label="Start - End Year">{{ $education->start_year }} -
                                                {{ $education->end_year }}</td>
                                            <td data-label="GPA / Score">{{ $education->gpa_or_score ?? '-' }}</td>
                                            <td data-label="Actions">
                                                <div class="action-buttons">
                                                    <a href="{{ route('employees.educationhistory.edit', [$employee, $education]) }}"
                                                        class="btn-info" title="Edit Work Experience">
                                                        <span class="material-symbols--edit"></span>Edit
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Cancel button --}}
                    <div class="form-buttons-container mt-3">
                        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-cancel">Cancel</a>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection