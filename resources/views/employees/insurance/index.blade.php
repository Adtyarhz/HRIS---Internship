@extends('layouts.admin')

@section('title', 'Insurance')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}" />
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

        .file-link {
            background-color: #FEFEF9;
            color: #225E7F;
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
            color: #000;
        }

        .file-link:hover {
            text-decoration: underline;
            background-color: #f4f4f4;
        }
    </style>
@endpush

@section('content')
    {{-- ✅ Tab-menu --}}
    @include('employees.partials.tab-menu', ['employee' => $employee])

    <div class="position-relative mb-3">
        <h4 class="mb-0">Employee Insurance :</h4>
        {{-- ✅ Flash message --}}
        @include('employees.partials.alert')
    </div>

    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('employees.insurance.create', $employee) }}" class="btn btn-dark-red">
            <i class="fas fa-plus"></i> Add Insurance
        </a>
    </div>

    @if($insurances->isEmpty())
        <div class="alert alert-info">Insurance data has not been filled in.</div>
    @else
        <div class="table-responsive-custom">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Insurance Number</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($insurances as $key => $insurance)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <a href="{{ route('employees.insurance.edit', [$employee, $insurance]) }}">
                                    {{ $insurance->insurance_number }}
                                </a>
                            </td>
                            <td>{{ $insurance->insurance_type }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($insurance->start_date)->format('d/m/Y') }} -
                                {{ $insurance->expiry_date ? \Carbon\Carbon::parse($insurance->expiry_date)->format('d/m/Y') : '-' }}
                            </td>
                            <td>{{ $insurance->status }}</td>
                            <td>
                                @if ($insurance->insurance_file)
                                    <a href="{{ asset('storage/' . $insurance->insurance_file) }}" target="_blank" class="file-link">
                                        <i class="fa-regular fa-file"></i>
                                        {{ Str::afterLast($insurance->insurance_file, '_') }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <a href="{{ route('employees.index') }}" class="btn btn-dark-red">Cancel</a>
        </div>
    @endif
@endsection
