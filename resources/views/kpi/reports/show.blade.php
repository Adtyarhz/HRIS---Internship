@extends('layouts.admin')

@section('title', 'Key Performance Index')
@section('header_icon', 'ri--bill-line-01')
@section('content_header', 'Key Performance Index')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
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

        .kpi-table-note {
            font-size: 13px;
            color: #555;
        }

        .btn-secondary {
            border-radius: 5px;
            width: 110px;
            height: 37px;
            color: white;
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            border: none;
        }

        .btn-secondary {
            margin-left: 10px;
        }
    </style>
@endpush

@section('content-wrapper')
    @include('kpi.partials.tab-menu')
    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">

                    {{-- Header Info --}}
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <span>Report for: <strong>{{ $kpiAssessment->employee->full_name }}</strong></span><br>
                            <span>Position: <strong>{{ $kpiAssessment->employee->position->title ?? '-' }}</strong></span><br>
                            <span>Division: <strong>{{ $kpiAssessment->employee->division->name ?? '-' }}</strong></span>
                        </div>
                        <div>
                            <span>Period: <strong>
                            @php
                                $name = $kpiAssessment->period->period_name;
                                $hasDate = preg_match('/\d{2}\s\w{3}\s\d{4}/', $name);
                            @endphp

                            @if($hasDate)
                                {{ $name }}
                            @else
                                {{ $name }} ({{ $kpiAssessment->period->start_date->format('d M Y') }} -
                                {{ $kpiAssessment->period->end_date->format('d M Y') }})
                            @endif
                            </strong></span><br>
                            <span>Status: <strong>{{ $kpiAssessment->status }}</strong></span>
                        </div>
                    </div>

                    {{-- Assessment Table --}}
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-custom text-center align-middle">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="align-middle">KPI Indicator</th>
                                    <th rowspan="2" class="align-middle">Weight</th>
                                    <th rowspan="2" class="align-middle">Target</th>
                                    <th colspan="2">Self-Assessment</th>
                                    <th colspan="2">Supervisor Assessment</th>
                                </tr>
                                <tr>
                                    <th>Achievement</th>
                                    <th>Score</th>
                                    <th>Achievement</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $totalWeight = 0;
                                    $totalScore = 0; 
                                @endphp
                                @foreach($kpiAssessment->assessmentItems as $item)
                                    @php
                                        $selfScore = $item->scores->firstWhere('participant.role', 'self');
                                        $supervisorScore = $item->scores->firstWhere('participant.role', 'direct_supervisor');
                                        $weight = $item->weight ?? 0;
                                        $finalScore = $supervisorScore->score ?? $selfScore->score ?? 0;
                                        $totalWeight += $weight;
                                        $totalScore += ($weight / 100) * $finalScore;
                                    @endphp
                                    <tr>
                                        <td class="text-start">
                                            <strong>{{ $item->indicator->indicator_name }}</strong><br>
                                            <small class="kpi-table-note">{{ $item->indicator->description }}</small>
                                        </td>
                                        <td>{{ $weight }}%</td>
                                        <td>{{ $item->target }} {{ $item->indicator->measurement_unit }}</td>
                                        <td>{{ $selfScore->achievement_input ?? '-' }}</td>
                                        <td>{{ $selfScore->score ?? '-' }}</td>
                                        <td>{{ $supervisorScore->achievement_input ?? '-' }}</td>
                                        <td>{{ $supervisorScore->score ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-end">
                                        Total Weighted Score: <strong>{{ number_format($totalScore, 2) }}</strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Notes --}}
                    <div class="form-group mt-3">
                        <label><strong>Supervisor Notes:</strong></label>
                        <div class="border p-2 bg-light">
                            {{ $kpiAssessment->participants->firstWhere('role', 'direct_supervisor')->notes ?? '-' }}
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-buttons-container">
                                <a href="{{ route('kpi-reports.index') }}" class="btn btn-cancel">Cancel</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
