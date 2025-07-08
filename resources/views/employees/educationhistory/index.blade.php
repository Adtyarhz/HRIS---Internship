@extends('layouts.admin')

@section('title', 'Education History')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        table tbody {
            background-color: #F4F1E0;
        }
        table thead {
            background-color: #DFD9B6;
        }
        .btn-dark-red {
            background-color: #662727;
            color: white;
            border: none;
        }
        .btn-dark-red:hover {
            background-color: #4e1f1f;
        }
        .table-responsive-custom {
            width: 100%;
            overflow-x: auto;
        }
        .table {
            min-width: 1200px;
            border-collapse: collapse;
        }
        .table th, .table td {
            white-space: nowrap;
        }
        @media screen and (max-width: 768px) {
            .table {
                font-size: 12px;
            }
        }
        .link-edit {
            color: #696DF0;
            font-weight: normal;
            text-decoration: none;
        }
        .link-edit:hover {
            text-decoration: underline;
        }
    </style>
@endpush

@section('content')
    @include('employees.partials.tab-menu', ['employee' => $employee])

    <div class="position-relative mb-3">
        <h4 class="mb-0">Employee Education History:</h4>
        @include('employees.partials.alert')
    </div>

    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('employees.educationhistory.create', $employee) }}" class="btn btn-dark-red">
            <i class="fas fa-plus"></i> Add Education History
        </a>
    </div>

    @if($educationHistories->isEmpty())
        <div class="alert alert-info">No education history recorded.</div>
    @else
        <div class="table-responsive-custom">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Level</th>
                        <th>Institution</th>
                        <th>Major</th>
                        <th>Start - End Year</th>
                        <th>GPA / Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($educationHistories as $index => $education)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('employees.educationhistory.edit', [$employee, $education]) }}" class="link-edit">
                                    {{ $education->education_level }}
                                </a>
                            </td>
                            <td>{{ $education->institution_name }}</td>
                            <td>{{ $education->major ?? '-' }}</td>
                            <td>{{ $education->start_year }} - {{ $education->end_year }}</td>
                            <td>{{ $education->gpa_or_score ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="d-flex justify-content-end mt-3">
        <a href="{{ route('employees.index') }}" class="btn btn-dark-red">
            Cancel
        </a>
    </div>
@endsection
