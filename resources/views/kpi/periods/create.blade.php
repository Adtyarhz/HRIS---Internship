@extends('layouts.admin')

@section('title', 'Key Performance Index')
@section('header_icon', 'ri--bill-line-01')
@section('content_header', 'Key Performance Index')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
@endpush

@section('content')
    @include('kpi.partials.tab-menu')
    <div class="container-fluid">
        <div class="form-content-container">
            <div class="card-body">
                <form action="{{ route('kpi-periods.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <!-- Period Name -->
                            <div class="form-group row align-items-center">
                                <label for="period_name" class="col-md-2 col-form-label">Period Name <span
                                        class="text-danger">*</span>:</label>
                                <div class="col-md-4">
                                    <input type="text" name="period_name" id="period_name"
                                        class="form-control @error('period_name') is-invalid @enderror"
                                        value="{{ old('period_name') }}"
                                        placeholder="Enter period name, e.g., Special Project Q3 2025" required>
                                    @error('period_name')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Enter a descriptive name for the custom period.</small>
                                </div>
                            </div>

                            <!-- Start Date -->
                            <div class="form-group row align-items-center">
                                <label for="start_date" class="col-md-2 col-form-label">Start Date <span
                                        class="text-danger">*</span>:</label>
                                <div class="col-md-2">
                                    <div class="input-group date-input-group">
                                        <input type="date" class="form-control"
                                            id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                        <label for="start_date" class="input-group-append">
                                            <span class="input-group-text">
                                                <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                                            </span>
                                        </label>
                                    </div>
                                    @error('start_date')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- End Date -->
                            <div class="form-group row align-items-center">
                                <label for="end_date" class="col-md-2 col-form-label">End Date <span
                                        class="text-danger">*</span>:</label>
                                <div class="col-md-2">
                                    <div class="input-group date-input-group">
                                        <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                            id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                        <label for="end_date" class="input-group-append">
                                            <span class="input-group-text">
                                                <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                                            </span>
                                        </label>
                                    </div>
                                    @error('end_date')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="form-group row align-items-center">
                                <label for="status" class="col-md-2 col-form-label">Status <span
                                        class="text-danger">*</span>:</label>
                                <div class="col-md-3">
                                    <select name="status" id="status"
                                        class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="" disabled selected>-- Select Status --</option>
                                        <option value="Aktif" {{ old('status') == 'Aktif' ? 'selected' : '' }}>Active</option>
                                        <option value="Ditutup" {{ old('status') == 'Ditutup' ? 'selected' : '' }}>Closed</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-buttons-container">
                                <a href="{{ route('kpi-periods.index') }}" class="btn btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection