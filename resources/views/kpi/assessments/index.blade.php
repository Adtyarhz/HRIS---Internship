@extends('layouts.admin')

@section('title', 'Performance Assessments (KPI)')
@section('header_icon', 'icon-park-outline--chart-histogram')
@section('content_header', 'Performance Assessments (KPI)')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
        /* Extra Styling untuk Tabel */
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

        .container-fluid {
            padding-bottom: 30px;
        }

        .add-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            max-width: 200px;
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

        .add-button:hover {
            background-color: #803030;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="form-content-container">
            <div class="card-body">
                @include('kpi.partials.tab-menu')

                {{-- Notifications --}}
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- My Assessments Card --}}
                <div class="assessment-section-title">My Assessment History</div>
                @if ($activePeriods->isNotEmpty())
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Performance Assessment Information</h5>
                        Your performance assessment session will be initiated by your direct supervisor. Please wait until
                        your supervisor starts and assigns the KPI template for the active period.
                    </div>
                @else
                    <div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> No Active Periods</h5>
                        There are currently no active assessment periods. Please contact the administrator for more
                        information.
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-custom text-center align-middle">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Supervisor</th>
                                <th>Status</th>
                                <th>Final Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myAssessments as $assessment)
                                <tr>
                                    <td>{{ $assessment->period->period_name }}
                                        ({{ $assessment->period->start_date->format('d-m-Y') }} -
                                        {{ $assessment->period->end_date->format('d-m-Y') }})
                                    </td>
                                    <td>{{ $assessment->supervisor->name ?? 'N/A' }}</td>
                                    <td>{{ $assessment->status }}</td>
                                    <td>{{ $assessment->final_score ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('kpi-assessments.show', $assessment->id) }}"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View/Assess
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No assessment history available for
                                        you.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Team Assessments Card (Only shown if user is a supervisor) --}}
    @if ($assessmentsAsSupervisor->isNotEmpty() || Auth::user()->employee->position->children->isNotEmpty())
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">

                    <div class="assessment-section-title d-flex justify-content-between align-items-center">My Team's Assessment History
                            <a href="{{ route('kpi-assessments.create') }}" class="add-button">
                                <i class="fas fa-plus"></i>Add New Assessment
                            </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-custom text-center align-middle">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Final Score</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assessmentsAsSupervisor as $assessment)
                                    <tr>
                                        <td>{{ $assessment->employee->full_name }} (NIK: {{ $assessment->employee->nik }})
                                        </td>
                                        <td>{{ $assessment->period->period_name }}
                                            ({{ $assessment->period->start_date->format('d-m-Y') }} -
                                            {{ $assessment->period->end_date->format('d-m-Y') }})
                                        </td>
                                        <td>{{ $assessment->status }}</td>
                                        <td>{{ $assessment->final_score ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('kpi-assessments.show', $assessment->id) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View/Assess
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    @endif
@endsection
