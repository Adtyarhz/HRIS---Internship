@extends('layouts.admin')

@section('title', 'Edit Periode KPI')
@section('header_icon', 'icon-park-outline--edit')
@section('content_header', 'Edit Periode KPI')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="form-content-container">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('kpi-periods.update', $kpiPeriod->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-12">
                            <!-- Period Name -->
                            <div class="form-group row align-items-center">
                                <label for="period_name" class="col-md-2 col-form-label">Period Name <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <input type="text" name="period_name" id="period_name"
                                        class="form-control @error('period_name') is-invalid @enderror"
                                        value="{{ old('period_name', $kpiPeriod->period_name) }}" required
                                        placeholder="Enter Period Name">
                                    @error('period_name')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Start Date -->
                            <div class="form-group row align-items-center">
                                <label for="start_date" class="col-md-2 col-form-label">Start Date <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <div class="input-group date-input-group">
                                        <input type="date" name="start_date" id="start_date"
                                            class="form-control @error('start_date') is-invalid @enderror"
                                            value="{{ old('start_date', $kpiPeriod->start_date->format('Y-m-d')) }}"
                                            required>
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
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <div class="input-group date-input-group">
                                        <input type="date" name="end_date" id="end_date"
                                            class="form-control @error('end_date') is-invalid @enderror"
                                            value="{{ old('end_date', $kpiPeriod->end_date->format('Y-m-d')) }}" required>
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
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <select name="status" id="status"
                                        class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="Aktif"
                                            {{ old('status', $kpiPeriod->status) == 'Aktif' ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="Ditutup"
                                            {{ old('status', $kpiPeriod->status) == 'Ditutup' ? 'selected' : '' }}>Ditutup
                                        </option>
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
                                <button type="button" class="btn btn-delete"
                                    onclick="showDeleteModal('kpi-periods-{{ $kpiPeriod->id }}')">Delete</button>
                                <a href="{{ route('kpi-periods.index') }}" class="btn btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Komponen Modal Delete -->
                <x-delete-modal modalId="kpi-periods-{{ $kpiPeriod->id }}" :action="route('kpi-periods.destroy', [$kpiPeriod->id])"
                    message="Are you sure to delete this Period?" />

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Nonaktifkan tombol submit saat pengiriman dan log data
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            console.log('Form submitted with method: PUT');
            console.log('Form data:', new FormData(this));
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Menyimpan...';
            }
        });
    </script>
@endpush
