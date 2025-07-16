@extends('layouts.admin')

@section('title', 'Career Path')
@section('header_icon', 'material-symbols--work-outline-01')
@section('content_header', 'Careers Administration')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/universal-table.css') }}">
    <style>
        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
        }

        table tbody {
            background-color: #F4F1E0;
        }

        table thead {
            background-color: #DFD9B6;
        }

        /* ✅ Tambahan untuk membuat tabel responsif */
        .table-responsive-custom {
            width: 100%;
            overflow-x: auto;
        }

        .table {
            min-width: 1200px;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            white-space: nowrap;
        }

        @media screen and (max-width: 768px) {
            .table {
                font-size: 12px;
            }
        }

        .mdi--pencil {
            display: inline-block;
            width: 24px;
            height: 24px;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23293CB3' d='M20.71 7.04c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.37-.39-1.02-.39-1.41 0l-1.84 1.83l3.75 3.75M3 17.25V21h3.75L17.81 9.93l-3.75-3.75z'/%3E%3C/svg%3E");
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="content-container">
            <div class="card-body">
                <div class="action-header">
                    <h4 class="employee-career-history">Career Histories: </h4>
                </div>

                <div class="action-buttons">
                    <a href="{{ route('employees.career_histories.create', $employee) }}" class="btn btn-add">
                        <i class="fas fa-plus"></i> Add Career Histories
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if ($careerHistories->isEmpty())
                    <div class="no-data">
                        <p>No career history available.</p>
                    </div>
                @else
                    <div class="table-responsive-custom">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Position & Division</th>
                                    <th>Type</th>
                                    <th>Start Date - End Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($careerHistories as $index => $careerHistory)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="col-details" data-label="Jabatan/Divisi">
                                            <div class="jabatan-divisi">
                                                <div class="employee-position">
                                                    <b>{{ $employee->position->title ?? 'Belum Diatur' }}</b></div>
                                                <div class="employee-division">
                                                    {{ $employee->division->name ?? 'Belum Datur' }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $careerHistory->type }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($careerHistory->start_date)->format('d/m/Y') }} -
                                            {{ $careerHistory->end_date ? \Carbon\Carbon::parse($careerHistory->end_date)->format('d/m/Y') : 'Present' }}
                                        </td>
                                        <td>
                                            <a
                                                href="{{ route('employees.career_histories.edit', [$employee, $careerHistory]) }}">
                                                <span class="mdi--pencil"></span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="action-buttons-cancel">
                    <a href="{{ route('employees.showCareer', $employee) }}" class="btn btn-cancel">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
