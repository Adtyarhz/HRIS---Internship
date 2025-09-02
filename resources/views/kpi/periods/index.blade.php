@extends('layouts.admin')

@section('title', 'KPI Period Management')
@section('header_icon', 'icon-park-outline--calendar')
@section('content_header', 'KPI Period Management')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .btn-info, .btn-delete-period {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            max-width: 120px;
            height: 2.5rem;
            color: #fff;
            font-family: "Noto Sans Georgian", sans-serif;
            font-size: 15px;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            border: none;
        }

        .btn-info:hover {background-color: #15b3d2; }
        .btn-info:hover {background-color: #098ba5; }
        .btn-delete-period { background-color: #FF4242; }
        .btn-delete-period:hover { background-color: #e63939; color: white; }
        .add-button:hover { background-color: #803030; color: #fff; }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .gg--trash-period, .material-symbols--edit {
            display: inline-block;
            width: 18px;
            height: 18px;
            background-repeat: no-repeat;
            background-size: 100% 100%;
        }

        .gg--trash-period {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cg fill='%23fff'%3E%3Cpath fill-rule='evenodd' d='M17 5V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V7h1a1 1 0 1 0 0-2zm-2-1H9v1h6zm2 3H7v11a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z' clip-rule='evenodd'/%3E%3Cpath d='M9 9h2v8H9zm4 0h2v8h-2z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .material-symbols--edit {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23fff' d='M3 21v-4.25L16.2 3.575q.3-.275.663-.425t.762-.15t.775.15t.65.45L20.425 5q.3.275.438.65T21 6.4q0 .4-.137.763t-.438.662L7.25 21zM17.6 7.8L19 6.4L17.6 5l-1.4 1.4z'/%3E%3C/svg%3E");
        }
        
    </style>
@endpush

@section('content')
    {{-- Tab Menu --}}
    @include('kpi.partials.tab-menu')

    <div class="container-fluid">
        <div class="form-content-container">
            <div class="card-body">

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

                {{-- Section Title + Add Button --}}
                <div class="assessment-section-title d-flex justify-content-between align-items-center">
                    KPI Periods List
                    <a href="{{ route('kpi-periods.create') }}" class="add-button">
                        <i class="fas fa-plus"></i>Add New Period
                    </a>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-custom text-center align-middle">
                        <thead>
                            <tr>
                                <th>Period Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kpiPeriods as $period)
                                <tr>
                                    <td>{{ $period->period_name }}</td>
                                    <td>{{ $period->start_date->format('d M Y') }}</td>
                                    <td>{{ $period->end_date->format('d M Y') }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('kpi-periods.edit', $period->id) }}" class="btn-info"
                                                title="Edit Period">
                                                <span class="material-symbols--edit"></span>Edit
                                            </a>
                                            <button type="button" class="btn-delete-period"
                                                onclick="showDeleteModal('kpi-period-{{ $period->id }}')">
                                                <span class="gg--trash-period"></span>Delete
                                            </button>

                                            {{-- Delete Modal --}}
                                            <x-delete-modal modalId="kpi-period-{{ $period->id }}" :action="route('kpi-periods.destroy', [$period->id])"
                                                message="Are you sure you want to delete this period?" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No KPI periods available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $kpiPeriods->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection
