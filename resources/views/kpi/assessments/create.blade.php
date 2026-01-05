@extends('layouts.admin')

@section('title', 'Key Performance Index')
@section('header_icon', 'ri--bill-line-01')
@section('content_header', 'Key Performance Index')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
        /* Extra Styling untuk Tabel */
        .assessment-section-title {
            font-size: 15px;
            font-weight: 600;
            margin-top: 25px;
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

        .table-custom tbody tr:hover {
            background-color: #F4F1E0;
            cursor: pointer;
        }

        .form-group label {
            font-weight: 600;
            font-size: 13px;
        }

        /* Membesarkan dan memusatkan checkbox */
        .form-check-input {
            transform: scale(1.5);
            cursor: pointer;
            display: block;
            margin: 0 auto;
        }
    </style>
@endpush

@section('content-wrapper')
    @include('kpi.partials.tab-menu')
    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">

                    {{-- Form --}}
                    <form action="{{ route('kpi-assessments.store') }}" method="POST">
                        @csrf

                        {{-- Select Period --}}
                        <div class="form-group row align-items-center">
                            <label for="kpi_period_id" class="col-md-2 col-form-label">Assessment Period <span class="text-danger">*</span>:</label>
                            <div class="col-md-4">
                                <select name="kpi_period_id" id="kpi_period_id" class="form-control @error('kpi_period_id') is-invalid @enderror" required>
                                    <option value="">-- Select Period --</option>
                                    @foreach($periods as $period)
                                        <option value="{{ $period->id }}" {{ old('kpi_period_id') == $period->id ? 'selected' : '' }}>
                                            {{ $period->period_name }} ({{ $period->start_date }} - {{ $period->end_date }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('kpi_period_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Subordinates & Template --}}
                        <div class="assessment-section-title">Choose Subordinates and KPI Template</div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-custom text-center align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Select</th>
                                        <th>Employee Name</th>
                                        <th>Position</th>
                                        <th>KPI Template</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subordinates as $subordinate)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_employees[]" value="{{ $subordinate->id }}"
                                                    class="form-check-input">
                                            </td>
                                            <td>{{ $subordinate->full_name }}</td>
                                            <td>{{ $subordinate->position->title }}</td>
                                            <td>
                                                <select name="employees[{{ $subordinate->id }}]"
                                                    class="form-control @error('employees.' . $subordinate->id) is-invalid @enderror">
                                                    <option value="">-- Select Template --</option>
                                                    @if(isset($templatesByPosition[$subordinate->position_id]))
                                                        @foreach($templatesByPosition[$subordinate->position_id] as $template)
                                                            <option value="{{ $template->id }}"
                                                                {{ old('employees.' . $subordinate->id) == $template->id ? 'selected' : '' }}>
                                                                {{ $template->template_name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                @error('employees.' . $subordinate->id)
                                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No subordinates available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Buttons --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">
                                    <a href="{{ route('kpi-assessments.index') }}" class="btn btn-cancel">Cancel</a>
                                    <button type="submit" class="btn btn-submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Checkbox & Row Click Logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.querySelectorAll('.table-custom tbody tr');

            rows.forEach(row => {
                row.addEventListener('click', function (e) {
                    // Prevent toggling if clicking on checkbox or select
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
                        return;
                    }

                    const checkbox = row.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;

                    const templateSelect = row.querySelector('select[name^="employees"]');
                    templateSelect.required = checkbox.checked;
                });
            });

            const checkboxes = document.querySelectorAll('input[name="selected_employees[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const row = this.closest('tr');
                    const templateSelect = row.querySelector('select[name^="employees"]');
                    templateSelect.required = this.checked;
                });
            });
        });
    </script>
@endpush