@extends('layouts.admin')

@section('title', 'Employee Information')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/universal-table.css') }}">
    <style>
        .universal-table th,
        .universal-table td {
            vertical-align: middle;
            text-align: center;
        }

        .universal-table thead {
            background-color: #DFD9B6;
        }

        .universal-table tbody {
            background-color: #F4F1E0;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .universal-table {
            min-width: 1200px;
            border-collapse: collapse;
        }

        .universal-table th,
        .universal-table td {
            white-space: nowrap;
        }

        @media screen and (max-width: 768px) {
            .universal-table {
                font-size: 12px;
            }
        }

        .file-link {
            background-color: #FEFEF9;
            color: #2b2da6;
            font-size: 12px;
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .file-link i {
            color: #000; /* hitam untuk ikon */
        }

        .file-link:hover {
            text-decoration: underline;
            background-color: #f4f4f4;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        @include('employees.partials.tab-menu', ['employee' => $employee])

        <div class="content-container">
            <div class="card-body">
                <div class="action-header">
                    <h4 class="employee-training-record">Family Dependent:</h4>
                </div>

                <div class="action-buttons">
                    <a href="{{ route('employees.family-dependents.create', $employee->id) }}"
                       class="btn btn-add">
                        <i class="fas fa-plus"></i> Add  Employee Family
                    </a>
                </div>
                @if ($dependents->isNotEmpty())
                    <div class="table-responsive">
                        <table class="universal-table">
                            <thead>
                                <tr>
                                    <th class="no-column">No.</th>
                                    <th class="training-name-column">Family Name</th>
                                    <th class="provider-column">Relationship</th>
                                    <th class="date-column">Phone</th>
                                    <th class="date-column">Address</th>
                                    <th class="certificate-number-column">City, Province</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dependents as $familyDependent)
                                    <tr>
                                        <td class="no-column">{{ $loop->iteration }}</td>
                                        <td class="training-name-column">
                                            <a href="{{ route('employees.family-dependents.edit', [$employee->id, $familyDependent->id]) }}"
                                               class="universal-link">
                                                {{ $familyDependent->contact_name }}
                                            </a>
                                        </td>
                                        <td class="provider-column">{{ $familyDependent->relationship }}</td>
                                        <td class="date-column">{{ $familyDependent->phone_number }}</td>
                                        <td class="date-column">{{ $familyDependent->address }}</td>
                                        <td class="certificate-number-column">{{ $familyDependent->city }}, {{ $familyDependent->province }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="no-data">
                        <p>Belum ada data keluarga untuk karyawan ini.</p>
                    </div>
                @endif
                <div class="action-buttons-cancel">
                    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-cancel">Cancel</a>
                </div>
            </div>
        </div>
    </div>
@endsection
