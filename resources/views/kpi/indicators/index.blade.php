@extends('layouts.admin')

@section('title', 'Key Performance Index')
@section('header_icon', 'ri--bill-line-01')
@section('content_header', 'Key Performance Index')

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

        .badge {
            font-size: 12px;
            font-weight: 500;
            padding-top: 7px;
            padding-bottom: 7px;
            padding-left: 7px;
            padding-right: 7px;
            border-radius: 8px;
        }

        .add-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            max-width: 240px;
            height: 2.5rem;
            background-color: #9a3b3b;
            color: #fff;
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
        }

        .gg--trash-indicator,
        .material-symbols--edit {
            display: inline-block;
            width: 18px;
            height: 18px;
            background-repeat: no-repeat;
            background-size: 100% 100%;
        }

        .gg--trash-indicator {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cg fill='%23fff'%3E%3Cpath fill-rule='evenodd' d='M17 5V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V7h1a1 1 0 1 0 0-2zm-2-1H9v1h6zm2 3H7v11a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z' clip-rule='evenodd'/%3E%3Cpath d='M9 9h2v8H9zm4 0h2v8h-2z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .material-symbols--edit {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23fff' d='M3 21v-4.25L16.2 3.575q.3-.275.663-.425t.762-.15t.775.15t.65.45L20.425 5q.3.275.438.65T21 6.4q0 .4-.137.763t-.438.662L7.25 21zM17.6 7.8L19 6.4L17.6 5l-1.4 1.4z'/%3E%3C/svg%3E");
        }
    </style>
@endpush

@section('content-wrapper')
    @include('kpi.partials.tab-menu')
    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">

                    {{-- Section Title + Add Button --}}
                    <div class="assessment-section-title d-flex justify-content-between align-items-center">
                        KPI Indicator Library
                        <a href="{{ route('kpi-indicators.create') }}" class="add-button">
                            <i class="fas fa-plus"></i>Add New Indicator
                        </a>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-custom text-center align-middle">
                            <thead>
                                <tr>
                                    <th>Indicator Name</th>
                                    <th>Measurement Unit</th>
                                    <th>Assessment Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kpiIndicators as $indicator)
                                    <tr>
                                        <td>
                                            <strong>{{ $indicator->indicator_name }}</strong><br>
                                            <small class="text-muted">{{ $indicator->description }}</small>
                                        </td>
                                        <td>{{ $indicator->measurement_unit }}</td>
                                        <td>
                                            @if ($indicator->higher_is_better)
                                                <span class="badge badge-success">Higher Value is Better</span>
                                            @else
                                                <span class="badge badge-danger">Lower Value is Better</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('kpi-indicators.edit', $indicator->id) }}" class="btn-info"
                                                    title="Edit Indicator">
                                                    <span class="material-symbols--edit"></span>Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No KPI indicators available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $kpiIndicators->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection