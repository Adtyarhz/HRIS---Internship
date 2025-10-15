@extends('layouts.admin')

@section('title', 'Employee Information')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
        @media (max-width: 768px) {
            .form-buttons-container {
                flex-direction: column-reverse;
                gap: 15px;
            }

            .btn-submit,
            .btn-cancel {
                width: 100%;
                max-width: 100%;
            }

            .btn-submit {
                margin-left: 0;
            }
        }
    </style>
@endpush

@section('content-wrapper')
    @include('employees.partials.tab-menu', ['employee' => $employee])

    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">
                    <form action="{{ route('health-records.storeOrUpdate', $employee->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12">

                                {{-- Height --}}
                                <div class="form-group row align-items-center">
                                    <label for="height" class="col-md-2 col-form-label">
                                        Height <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-2">
                                        <div class="input-group">
                                            <input
                                                type="number"
                                                step="0.01"
                                                class="form-control @error('height') is-invalid @enderror"
                                                id="height"
                                                name="height"
                                                value="{{ old('height', $healthRecord->height ?? '') }}"
                                                placeholder="Input Your Height"
                                                required
                                            >
                                            <div class="input-group-append">
                                                <span class="input-group-text">cm</span>
                                            </div>
                                        </div>
                                        @error('height')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Weight --}}
                                <div class="form-group row align-items-center">
                                    <label for="weight" class="col-md-2 col-form-label">
                                        Weight <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-2">
                                        <div class="input-group">
                                            <input
                                                type="number"
                                                step="0.01"
                                                class="form-control @error('weight') is-invalid @enderror"
                                                id="weight"
                                                name="weight"
                                                value="{{ old('weight', $healthRecord->weight ?? '') }}"
                                                placeholder="Input Your Weight"
                                                required
                                            >
                                            <div class="input-group-append">
                                                <span class="input-group-text">kg</span>
                                            </div>
                                        </div>
                                        @error('weight')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Price Last Checkup --}}
                                <div class="form-group row align-items-center">
                                    <label for="price_last_checkup" class="col-md-2 col-form-label">
                                        Price Last Checkup <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <input
                                                type="text"
                                                id="price_last_checkup_display"
                                                class="form-control"
                                                placeholder="e.g., 300000,00"
                                                value="{{ old('price_last_checkup', $healthRecord?->price_last_checkup)
                                                    ? number_format(old('price_last_checkup', $healthRecord?->price_last_checkup), 2, ',', '.')
                                                    : '' }}"
                                                required
                                            >
                                            <input
                                                type="hidden"
                                                name="price_last_checkup"
                                                id="price_last_checkup"
                                                value="{{ old('price_last_checkup', $healthRecord?->price_last_checkup) }}"
                                            >
                                            <div class="input-group-append">
                                                <span class="input-group-text">IDR</span>
                                            </div>
                                        </div>
                                        @error('price_last_checkup')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Blood Type --}}
                                <div class="form-group row align-items-center">
                                    <label for="blood_type" class="col-md-2 col-form-label">
                                        Blood Type <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-3">
                                        <select
                                            class="form-control @error('blood_type') is-invalid @enderror"
                                            id="blood_type"
                                            name="blood_type"
                                            required
                                        >
                                            <option value="">Choose Your Blood Type</option>
                                            @foreach (['A', 'B', 'AB', 'O', 'Tidak Tahu'] as $type)
                                                <option
                                                    value="{{ $type }}"
                                                    {{ old('blood_type', $healthRecord->blood_type ?? '') == $type ? 'selected' : '' }}
                                                >
                                                    {{ $type }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('blood_type')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Allergies --}}
                                <div class="form-group row">
                                    <label for="known_allergies" class="col-md-2 col-form-label">
                                        Allergies <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-4">
                                        <textarea
                                            class="form-control @error('known_allergies') is-invalid @enderror"
                                            id="known_allergies"
                                            name="known_allergies"
                                            rows="4"
                                            placeholder="Description of Your Allergies"
                                            required
                                        >{{ old('known_allergies', $healthRecord->known_allergies ?? '') }}</textarea>
                                        @error('known_allergies')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Chronic Diseases --}}
                                <div class="form-group row">
                                    <label for="chronic_diseases" class="col-md-2 col-form-label">
                                        Chronic Diseases <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-4">
                                        <textarea
                                            class="form-control @error('chronic_diseases') is-invalid @enderror"
                                            id="chronic_diseases"
                                            name="chronic_diseases"
                                            rows="4"
                                            placeholder="Description of Your Chronic Diseases"
                                        required>{{ old('chronic_diseases', $healthRecord->chronic_diseases ?? '') }}</textarea>
                                        @error('chronic_diseases')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Last Checkup --}}
                                <div class="form-group row align-items-center">
                                    <label for="last_checkup_date" class="col-md-2 col-form-label">
                                        Last Checkup <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-2">
                                        <div class="input-group date-input-group">
                                            <input
                                                type="date"
                                                class="form-control @error('last_checkup_date') is-invalid @enderror"
                                                id="last_checkup_date"
                                                name="last_checkup_date"
                                                value="{{ old('last_checkup_date', isset($healthRecord) && $healthRecord->last_checkup_date ? \Carbon\Carbon::parse($healthRecord->last_checkup_date)->format('Y-m-d') : '') }}"
                                            required>
                                            <label for="last_checkup_date" class="input-group-append">
                                                <span class="input-group-text">
                                                    <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                                                </span>
                                            </label>
                                        </div>
                                        @error('last_checkup_date')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Location Checkup --}}
                                <div class="form-group row">
                                    <label for="checkup_loc" class="col-md-2 col-form-label">
                                        Location Checkup <span class="text-danger">*</span> :
                                    </label>
                                    <div class="col-md-4">
                                        <textarea
                                            class="form-control @error('checkup_loc') is-invalid @enderror"
                                            id="checkup_loc"
                                            name="checkup_loc"
                                            rows="3"
                                            placeholder="e.g., University of Indonesia Hospital - Jl. Prof. Dr. Bahder Djohan, Pondok Cina, Beji District, Depok City, West Java"
                                        required>{{ old('checkup_loc', $healthRecord->checkup_loc ?? '') }}</textarea>
                                        @error('checkup_loc')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div class="form-group row">
                                    <label for="notes" class="col-md-2 col-form-label">
                                        Notes :
                                    </label>
                                    <div class="col-md-4">
                                        <textarea
                                            class="form-control @error('notes') is-invalid @enderror"
                                            id="notes"
                                            name="notes"
                                            rows="4"
                                            placeholder="Notes of Your Health Record"
                                        >{{ old('notes', $healthRecord->notes ?? '') }}</textarea>
                                        @error('notes')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">
                                    <a href="{{ route('employees.index') }}" class="btn btn-cancel">Cancel</a>
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
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
    <script>
        document.getElementById('updateForm').addEventListener('submit', function (e) {
            console.log('Form submitted with method: PUT');
            console.log('Form data:', new FormData(this));
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Saving...';
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formattedInput = document.getElementById('price_last_checkup_display');
            const rawInput = document.getElementById('price_last_checkup');

            if (!formattedInput || !rawInput) return;

            const cleave = new Cleave(formattedInput, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.',
                numeralDecimalScale: 2,
                rawValueTrimPrefix: true,
            });

            formattedInput.addEventListener('input', function() {
                rawInput.value = cleave.getRawValue();
            });

            rawInput.value = cleave.getRawValue();
        });
    </script>
@endpush