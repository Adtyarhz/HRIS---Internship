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

        .btn-cancel-assess, .btn-cancel-only, .btn-secondary {
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

        .btn-secondary {margin-right: 10px;}
        .btn-cancel-assess {background: #9a3b3b; margin-right: 10px;}
        .btn-cancel-only {background: #9a3b3b;}
        .btn-cancel-assess:hover, .btn-cancel-only:hover {background: #803030; color: white;}
    </style>
@endpush

@section('content')
    @include('kpi.partials.tab-menu')
    <div class="container-fluid">
        <div class="form-content-container">
            <div class="card-body">

                {{-- Header Info --}}
                <div class="d-flex justify-content-between mb-3">
                    <span>Assessment for: <strong>{{ $kpiAssessment->employee->full_name }}</strong></span>
                    <span>Period: <strong>{{ $kpiAssessment->period->period_name }}</strong></span>
                </div>

                @php
                    $user = Auth::user();
                    $isSelf = $user->employee && $user->employee->id === $kpiAssessment->employee_id;
                    $isSupervisor = $user->id === $kpiAssessment->primary_supervisor_id;

                    $canEditTarget = $isSupervisor && in_array($kpiAssessment->status, ['Penyesuaian Target']);
                    $canEditSelf = $isSelf && $kpiAssessment->status == 'Penilaian Diri';
                    $canEditSupervisor = $isSupervisor && $kpiAssessment->status == 'Penilaian Atasan Langsung';
                    $isEditable = $canEditTarget || $canEditSelf || $canEditSupervisor;
                @endphp

                {{-- Info Box --}}
                @if ($canEditTarget)
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Target and Weight Adjustment</h6>
                        <span>As a supervisor, please adjust the targets and weights. Once saved, the employee will be able to perform the self-assessment.</span>
                    </div>
                @elseif($kpiAssessment->status == 'Penyesuaian Target')
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Waiting for Supervisor Adjustment</h6>
                        <span>This assessment is waiting for target adjustment by the supervisor. You cannot perform self-assessment yet.</span>
                    </div>
                @elseif($canEditSelf)
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Self-Assessment</h6>
                        <span>Please fill in your achievements and scores. Use the <b>"Save Draft"</b> button to save temporarily or <b>"Submit"</b> to send your assessment to your supervisor.</span>
                    </div>
                @elseif($canEditSupervisor)
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Supervisor Assessment</h6>
                        <span>Please fill in the achievements and scores for your subordinate. Use the <b>"Save Draft"</b> button to save temporarily or <b>"Submit"</b> to finalize your assessment.</span>
                    </div>
                @endif

                <form action="{{ route('kpi-assessments.update', $kpiAssessment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

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
                                @foreach ($kpiAssessment->assessmentItems as $item)
                                    @php
                                        $selfScore = $item->scores->firstWhere('participant.role', 'self');
                                        $supervisorScore = $item->scores->firstWhere(
                                            'participant.role',
                                            'direct_supervisor',
                                        );
                                    @endphp
                                    <tr>
                                        {{-- KPI Indicator --}}
                                        <td class="text-start">
                                            <strong>{{ $item->indicator->indicator_name }}</strong><br>
                                            <small class="kpi-table-note">{{ $item->indicator->description }}</small>
                                        </td>

                                        {{-- Weight --}}
                                        <td>
                                            @if ($canEditTarget)
                                                <input type="number" step="0.01"
                                                    name="items[{{ $item->id }}][weight]" class="form-control"
                                                    value="{{ old('items.' . $item->id . '.weight', $item->weight) }}"
                                                    required>
                                            @else
                                                {{ $item->weight }}%
                                            @endif
                                        </td>

                                        {{-- Target --}}
                                        <td>
                                            @if ($canEditTarget)
                                                <input type="text" name="items[{{ $item->id }}][target]"
                                                    class="form-control"
                                                    value="{{ old('items.' . $item->id . '.target', $item->target) }}"
                                                    required>
                                            @else
                                                {{ $item->target }} {{ $item->indicator->measurement_unit }}
                                            @endif
                                        </td>

                                        {{-- Self --}}
                                        <td>
                                            @if ($canEditSelf)
                                                <input type="text" name="items[{{ $item->id }}][achievement_input]"
                                                    class="form-control"
                                                    value="{{ old('items.' . $item->id . '.achievement_input', $selfScore->achievement_input ?? '') }}"
                                                    required>
                                            @else
                                                {{ $selfScore->achievement_input ?? '-' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($canEditSelf)
                                                <input type="number" step="0.01"
                                                    name="items[{{ $item->id }}][score]" class="form-control"
                                                    value="{{ old('items.' . $item->id . '.score', $selfScore->score ?? '') }}"
                                                    required>
                                            @else
                                                {{ $selfScore->score ?? '-' }}
                                            @endif
                                        </td>

                                        {{-- Supervisor --}}
                                        <td>
                                            @if ($canEditSupervisor)
                                                <input type="text" name="items[{{ $item->id }}][achievement_input]"
                                                    class="form-control"
                                                    value="{{ old('items.' . $item->id . '.achievement_input', $supervisorScore->achievement_input ?? '') }}">
                                            @else
                                                {{ $supervisorScore->achievement_input ?? '-' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($canEditSupervisor)
                                                <input type="number" step="0.01"
                                                    name="items[{{ $item->id }}][score]" class="form-control"
                                                    value="{{ old('items.' . $item->id . '.score', $supervisorScore->score ?? '') }}"
                                                    required>
                                            @else
                                                {{ $supervisorScore->score ?? '-' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Notes --}}
                    <div class="form-group mt-3">
                        <label for="notes">Additional Notes:</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" {{ !$isEditable ? 'readonly' : '' }}>{{ old('notes') }}</textarea>
                    </div>

                    {{-- Buttons --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-buttons-container">
                                {{-- <a href="{{ route('kpi-assessments.index') }}" class="btn btn-cancel-assess">Cancel</a> --}}
                                @if ($canEditTarget)
                                    <a href="{{ route('kpi-assessments.index') }}" class="btn btn-cancel-assess">Cancel</a>
                                    <button type="submit" class="btn btn-submit">Save</button>
                                @elseif($canEditSelf || $canEditSupervisor)
                                <a href="{{ route('kpi-assessments.index') }}" class="btn btn-cancel-assess">Cancel</a>
                                    <button type="submit" name="action" value="save_draft" class="btn btn-secondary">Save Draft</button>
                                    <button type="submit" name="action" value="submit" class="btn btn-submit">Submit</button>
                                @else
                                    <a href="{{ route('kpi-assessments.index') }}" class="btn btn-cancel-only">Cancel</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
