@extends('layouts.admin')

@section('title', 'Career Path')
@section('header_icon', 'material-symbols--work-outline-01')
@section('content_header', 'Careers Administration')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">

    <style>
        .material-symbols--work-history-outline-sharp {
            display: inline-block;
            width: 24px;
            height: 24px;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23fff' d='M4 19V8zv-.375zm-2 2V6h6V2h8v4h6v6.275q-.45-.325-.962-.562T20 11.3V8H4v11h7.075q.075.525.225 1.025t.375.975zm8-15h4V4h-4zm8 17q-2.075 0-3.537-1.463T13 18t1.463-3.537T18 13t3.538 1.463T23 18t-1.463 3.538T18 23m.5-5.2V15h-1v3.2l2.15 2.15l.7-.7z'/%3E%3C/svg%3E");
        }

        .material-symbols--work-outline {
            display: inline-block;
            width: 24px;
            height: 24px;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23000' d='M4 21q-.825 0-1.412-.587T2 19V8q0-.825.588-1.412T4 6h4V4q0-.825.588-1.412T10 2h4q.825 0 1.413.588T16 4v2h4q.825 0 1.413.588T22 8v11q0 .825-.587 1.413T20 21zm0-2h16V8H4zm6-13h4V4h-4zM4 19V8z'/%3E%3C/svg%3E");
        }

        .action-button-career {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            font-family: 'Montserrat', sans-serif;
            border: none;
            transition: opacity 0.2s;
            margin-left: auto;
        }

        .action-button-career:hover {
            opacity: 0.9;
            color: none;
        }

        .card-actions {
            display: flex;
            gap: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="employee-detail-page">
        <!-- Custom Page Header -->
        <div class="page-header-container">
            <h1 class="page-title">
                Employee Career
            </h1>
            <div class="page-header-actions">
                <a href="{{ route('career.index') }}" class="action-button btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Career Path
                </a>
            </div>
        </div>

        <div class="detail-container">
            <div class="detail-column left-column">
                <!-- Employee Detail Card -->
                <div class="detail-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-briefcase"></i> Employee Detail</h3>
                    </div>
                    <div class="card-content">
                        <div class="data-item">
                            <span class="data-label">Employee Name</span>
                            <span class="data-value">{{ $employee->full_name }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Employee Division</span>
                            <span class="data-value">{{ $employee->division->name ?? 'Belum Datur' }}</span>
                        </div>
                        <div class="data-item">
                            <span class="data-label">Employee Position</span>
                            <span class="data-value">{{ $employee->position->title ?? 'Belum Diatur' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Career Projection Card -->
                <div class="detail-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-line"></i> Career Projection</h3>
                        <div class="card-actions">
                            <a href="{{ route('employees.career_projection.form', $employee) }}"
                                class="action-button-career btn-career-projection">
                                <span class="material-symbols--work-outline"></span>
                                {{ $careerProjection ? 'Edit Career Projection' : 'Add Career Projection' }}
                            </a>
                        </div>
                    </div>
                    <div class="card-content">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if (!$careerProjection)
                            <p>No career projection available for this employee.</p>
                        @else
                            <div class="data-item">
                                <span class="data-label">Projected Position</span>
                                <span class="data-value">{{ $careerProjection->projectedPosition->title ?? 'N/A' }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">Timeline</span>
                                <span class="data-value">{{ $careerProjection->timeline }}</span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">Status</span>
                                <span class="data-value">
                                    @if ($careerProjection->status == 'Approved')
                                        <span class="status-badge status-active">Approved</span>
                                    @else
                                        @if ($careerProjection->status)
                                            {{ $careerProjection->status }}
                                        @else
                                            -
                                        @endif
                                    @endif
                                </span>
                            </div>
                            <div class="data-item">
                                <span class="data-label">Readiness Notes</span>
                                <span class="data-value">{{ $careerProjection->readiness_notes ?? '-' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Career Histories Card -->
                <div class="detail-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history"></i> Career Histories</h3>
                        <div class="card-actions">
                            <a href="{{ route('employees.career_histories.index', $employee) }}"
                                class="action-button-career btn-career-history">
                                <span class="material-symbols--work-history-outline-sharp"></span> See Career History
                            </a>
                        </div>
                    </div>
                    <div class="card-content">
                        @if ($careerHistories->isEmpty())
                            <p>No career history available.</p>
                        @else
                            @foreach ($careerHistories as $careerHistory)
                                <div class="data-item">
                                    <span class="data-label">Position</span>
                                    <span class="data-value">{{ $careerHistory->position->title ?? 'N/A' }}</span>
                                </div>
                                <div class="data-item">
                                    <span class="data-label">Division</span>
                                    <span class="data-value">{{ $careerHistory->division->name ?? 'N/A' }}</span>
                                </div>
                                <div class="data-item">
                                    <span class="data-label">Employee Type</span>
                                    <span class="data-value">{{ $careerHistory->employee_type }}</span>
                                </div>
                                <div class="data-item">
                                    <span class="data-label">Start Date</span>
                                    <span class="data-value">{{ $careerHistory->start_date->toDateString() }}</span>
                                </div>
                                <div class="data-item">
                                    <span class="data-label">End Date</span>
                                    <span
                                        class="data-value">{{ $careerHistory->end_date ? $careerHistory->end_date->toDateString() : '-' }}</span>
                                </div>
                                <div class="data-item">
                                    <span class="data-label">Type</span>
                                    <span class="data-value">{{ $careerHistory->type }}</span>
                                </div>
                                <div class="data-item">
                                    <span class="data-label">Notes</span>
                                    <span class="data-value">{{ $careerHistory->notes ?? '-' }}</span>
                                </div>
                                <hr>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const collapseEl = document.getElementById('careerHistoriesCollapse');
            collapseEl.addEventListener('show.bs.collapse', function() {
                var icon = this.previousElementSibling.querySelector('.collapse-icon');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            });
            collapseEl.addEventListener('hide.bs.collapse', function() {
                var icon = this.previousElementSibling.querySelector('.collapse-icon');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            });
        });
    </script>
@endpush
