@extends('layouts.admin')

@section('title', 'Key Performance Index')
@section('header_icon', 'ri--bill-line-01')
@section('content_header', 'Key Performance Index')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
        .assessment-section-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .table-custom th {
            background-color: #DFD9B6;
            font-weight: 600;
            font-size: 13px;
            text-align: center;
        }

        .table-custom td {
            vertical-align: middle;
            font-size: 13px;
            text-align: center;
        }

        .badge-status {
            font-size: 12px;
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 8px;
            text-transform: capitalize;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-danger-status {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid transparent;
            text-decoration: none;
        }

        .btn-action.btn-info {
            background-color: #17a2b8;
            color: white;
            border-color: #17a2b8;
        }

        .btn-action.btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }

        .form-control.datepicker {
            background-color: #fff !important;
            cursor: pointer;
        }

        .input-group-text {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            cursor: pointer;
        }

        .input-group .form-control.datepicker {
            border-right: 0;
        }

        .input-group .input-group-text {
            border-left: 0;
        }

        .btn-success,
        .btn-secondary,
        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 2.5rem;
            color: #fff;
            font-family: "Noto Sans Georgian", sans-serif;
            font-size: 15px;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            border: none;
        }

        .btn-success {
            max-width: 130px;
            min-width: 130px;
        }

        .btn-secondary,
        .btn-primary {
            max-width: 120px;
            min-width: 120px;
        }

        .w-100,
        .btn-primary {
            margin-right: 15px;
        }

        .container-fluid-1 {
            margin-bottom: 30px;
        }

        /* =================================== */
        /* ==         PAGINATION            == */
        /* =================================== */
        .page-footer {
            display: flex;
            justify-content: center;
            padding-top: 0.75rem;
        }

        .pagination {
            display: flex;
            align-items: center;
            list-style: none;
            background-color: #f3efe2;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            padding: 0 0.25rem;
            height: 2.5rem;
        }

        .pagination li {
            margin: 0 0.25rem;
        }

        .pagination li a,
        .pagination li span {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            color: var(--text-dark);
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            border: none;
            background: transparent;
            cursor: pointer;
        }

        .pagination li.active {
            background-color: #fffdeb;
            border: 1px solid rgba(0, 0, 0, 0.2);
            height: 2.5rem;
            line-height: 2.5rem;
            margin: 0;
            padding: 0 0.25rem;
        }

        .pagination li.active span {
            font-weight: 600;
        }

        .pagination li.disabled span {
            color: #6c757d;
            cursor: default;
        }
    </style>
@endpush

@section('content-wrapper')
    @include('kpi.partials.tab-menu')
    <section class="content">
        <div class="container-fluid">

            {{-- Filter Section --}}
            <div class="container-fluid-1">
                <div class="form-content-container">
                    <div class="card-body">
                        <form action="{{ route('kpi-reports.index') }}" method="GET">
                            <div class="assessment-section-title">Report Filters</div>

                            <div class="row">
                                {{-- Division --}}
                                @if(Auth::user()->role === 'hc' || Auth::user()->role === 'superadmin')
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="division_id" class="col-form-label">Division:</label>
                                            <select name="division_id" id="division_id" class="form-control">
                                                <option value="">All Divisions</option>
                                                @foreach($divisions as $division)
                                                    <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                                        {{ $division->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                {{-- Position --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="position_id" class="col-form-label">Position:</label>
                                        <select name="position_id" id="position_id" class="form-control">
                                            <option value="">All Positions</option>
                                            @foreach($positions as $position)
                                                <option value="{{ $position->id }}" {{ request('position_id') == $position->id ? 'selected' : '' }}>
                                                    {{ $position->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Employee Name --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="search" class="col-form-label">Employee Name:</label>
                                        <input type="text" name="search" id="search" class="form-control"
                                            value="{{ request('search') }}" placeholder="Search name...">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- Period & Dates --}}
                                <div class="col-md-12 d-flex align-items-end">
                                    <div class="w-100">
                                        <label for="period_id" class="col-form-label">Period:</label>
                                        <select name="period_id" id="period_id" class="form-control">
                                            <option value="">All Periods</option>
                                            @foreach($periods['special'] as $sp)
                                                <option value="{{ $sp['id'] }}" {{ request('period_id') == $sp['id'] ? 'selected' : '' }}>
                                                    {{ $sp['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-100">
                                        <label for="start_date" class="col-form-label">From Date:</label>
                                        <div class="input-group date-input-group">
                                            <input type="date" id="start_date" name="start_date" class="form-control"
                                                value="{{ request('start_date') }}">
                                            <label for="start_date" class="input-group-append">
                                                <span class="input-group-text">
                                                    <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="w-100">
                                        <label for="end_date" class="col-form-label">To Date:</label>
                                        <div class="input-group date-input-group">
                                            <input type="date" id="end_date" name="end_date" class="form-control"
                                                value="{{ request('end_date') }}">
                                            <label for="end_date" class="input-group-append">
                                                <span class="input-group-text">
                                                    <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="ms-3 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        <a href="{{ route('kpi-reports.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-undo"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <div class="container-fluid-1">
                <div class="form-content-container">
                    <div class="card-body">

                        {{-- Results --}}
                        @php
                            $hasFilter = request()->filled('division_id') || request()->filled('position_id')
                                || request()->filled('search') || request()->filled('period_id')
                                || (request()->filled('start_date') && request()->filled('end_date'));
                        @endphp

                        <div class="row align-items-center mb-3">
                            <div class="col">
                                <div class="assessment-section-title">Performance Assessment Results</div>
                            </div>
                            <div class="col-auto">
                                <a href="{{ $hasFilter && !$assessments->isEmpty() ? route('kpi-reports.export', request()->all()) : '#' }}"
                                class="btn btn-success {{ (!$hasFilter || $assessments->isEmpty()) ? 'disabled' : '' }}"
                                @if(!$hasFilter || $assessments->isEmpty()) aria-disabled="true" @endif>
                                    <i class="fa fa-file-excel"></i> Export Excel
                                </a>
                            </div>
                        </div>

                        @if(!$hasFilter)
                            <div class="alert alert-info text-center">
                                Please use the filter to display performance assessment reports.
                            </div>
                        @elseif($assessments->isEmpty())
                            <div class="alert alert-warning text-center">
                                No assessment data found matching the selected criteria.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-custom">
                                    <thead>
                                        <tr>
                                            <th>Employee Name</th>
                                            <th>Position</th>
                                            <th>Division</th>
                                            <th>Period</th>
                                            <th>Status</th>
                                            <th>Final Score</th>
                                            <th>Supervisor</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assessments as $assessment)
                                            <tr>
                                                <td>{{ $assessment->employee->full_name }}</td>
                                                <td>{{ $assessment->employee->position->title ?? 'N/A' }}</td>
                                                <td>{{ $assessment->employee->division->name ?? 'N/A' }}</td>
                                                <td>
                                                    @php
                                                        $name = $assessment->period->period_name;
                                                        $hasDate = preg_match('/\d{2}\s\w{3}\s\d{4}/', $name);
                                                    @endphp

                                                    @if($hasDate)
                                                        {{ $name }}
                                                    @else
                                                        {{ $name }} ({{ $assessment->period->start_date->format('d M Y') }} -
                                                        {{ $assessment->period->end_date->format('d M Y') }})
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = match($assessment->status) {
                                                            'Selesai' => 'badge-success',
                                                            'Penilaian Diri', 
                                                            'Penilaian Atasan Langsung',
                                                            'Penilaian Atasan Tidak Langsung' => 'badge-warning',
                                                            'Penyesuaian Target' => 'badge-info',
                                                            'Draft' => 'badge-secondary',
                                                            'Kadaluarsa' => 'badge-danger-status',
                                                            default => 'badge-secondary',
                                                        };
                                                    @endphp
                                                    <span class="badge-status {{ $statusClass }}">
                                                        {{ $assessment->status }}
                                                    </span>
                                                </td>
                                                <td><strong>{{ $assessment->final_score ? number_format($assessment->final_score, 2) : '-' }}</strong></td>
                                                <td>{{ $assessment->supervisor->name ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="{{ route('kpi-reports.show', $assessment->id) }}"
                                                        class="btn-action btn-info" title="View Details">
                                                            <i class="fas fa-eye"></i> Details
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($assessments->hasPages())
                                <footer class="page-footer">
                                    {{ $assessments->withQueryString()->links('vendor.pagination.custom') }}
                                </footer>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const commonOptions = {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d F Y",
                locale: "id",
                allowInput: true,
            };

            const endDatePicker = flatpickr("#end_date", {
                ...commonOptions,
                noCalendar: !document.getElementById('start_date').value,
            });

            const startDatePicker = flatpickr("#start_date", {
                ...commonOptions,
                onChange: function (selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        endDatePicker.set("noCalendar", false);
                        endDatePicker.set("minDate", selectedDates[0]);

                        const currentEndDate = endDatePicker.selectedDates[0];
                        if (currentEndDate && currentEndDate < selectedDates[0]) {
                            endDatePicker.clear();
                        }
                    } else {
                        endDatePicker.set("noCalendar", true);
                        endDatePicker.clear();
                        endDatePicker.set("minDate", null);
                    }
                }
            });

            if (!startDatePicker.selectedDates.length > 0) {
                endDatePicker.set("noCalendar", true);
            }
        });
    </script>
@endpush
