@extends('layouts.admin')

@section('title', 'Riwayat Kesehatan Karyawan')
@section('header_icon', 'icon-park-outline--health')
@section('content_header', 'Riwayat Kesehatan')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Memuat CSS khusus untuk form ini --}}
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        {{-- 1. Panggil partial menu tab --}}
        @include('employees.partials.tab-menu', ['employee' => $employee])

        {{-- 2. Container untuk konten form --}}
        <div class="form-content-container">
            <div class="card-body">
                <form action="{{ route('health-records.storeOrUpdate', $employee->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12">

                            {{-- Height --}}
                            <div class="form-group row align-items-center">
                                <label for="height" class="col-md-2 col-form-label">Height <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-2">
                                    <div class="input-group">
                                        <input type="number" step="0.01"
                                            class="form-control @error('height') is-invalid @enderror" id="height"
                                            name="height" value="{{ old('height', $healthRecord->height ?? '') }}"
                                            placeholder="Input Your Height" required>
                                        <div class="input-group-append"><span class="input-group-text">cm</span></div>
                                    </div>
                                    @error('height')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Weight --}}
                            <div class="form-group row align-items-center">
                                <label for="weight" class="col-md-2 col-form-label">Weight <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-2">
                                    <div class="input-group">
                                        <input type="number" step="0.01"
                                            class="form-control @error('weight') is-invalid @enderror" id="weight"
                                            name="weight" value="{{ old('weight', $healthRecord->weight ?? '') }}"
                                            placeholder="Input Your Weight" required>
                                        <div class="input-group-append"><span class="input-group-text">kg</span></div>
                                    </div>
                                    @error('weight')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Price Last Checkup --}}
                            <div class="form-group row align-items-center">
                                <label for="price_last_checkup" class="col-md-2 col-form-label">Price Last Checkup <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <input type="number"
                                        class="form-control @error('price_last_checkup') is-invalid @enderror"
                                        id="price_last_checkup" name="price_last_checkup"
                                        value="{{ old('price_last_checkup', $healthRecord->price_last_checkup ?? '') }}"
                                        placeholder="Input Your Medical Price Last Checkup" required>
                                    @error('price_last_checkup')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Blood Type --}}
                            <div class="form-group row align-items-center">
                                <label for="blood_type" class="col-md-2 col-form-label">Blood Type <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <select class="form-control @error('blood_type') is-invalid @enderror" id="blood_type"
                                        name="blood_type" required>
                                        <option value="">Choose Your Blood Type</option>
                                        @foreach (['A', 'B', 'AB', 'O', 'Tidak Tahu'] as $type)
                                            <option value="{{ $type }}"
                                                {{ old('blood_type', $healthRecord->blood_type ?? '') == $type ? 'selected' : '' }}>
                                                {{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @error('blood_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Allergies --}}
                            <div class="form-group row">
                                <label for="known_allergies" class="col-md-2 col-form-label">Allergies <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-4">
                                    <textarea class="form-control @error('known_allergies') is-invalid @enderror" id="known_allergies"
                                        name="known_allergies" rows="6" placeholder="Description of Your Allergies" required>{{ old('known_allergies', $healthRecord->known_allergies ?? '') }}</textarea>
                                    @error('known_allergies')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Chronic Diseases --}}
                            <div class="form-group row">
                                <label for="chronic_diseases" class="col-md-2 col-form-label">Chronic Diseases :</label>
                                <div class="col-md-4">
                                    <textarea class="form-control @error('chronic_diseases') is-invalid @enderror" id="chronic_diseases"
                                        name="chronic_diseases" rows="6" placeholder="Description of Your Chronic Diseases">{{ old('chronic_diseases', $healthRecord->chronic_diseases ?? '') }}</textarea>
                                    @error('chronic_diseases')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Last Checkup --}}
                            <div class="form-group row align-items-center">
                                <label for="last_checkup_date" class="col-md-2 col-form-label">Last Checkup :</label>
                                <div class="col-md-3">
                                    <div class="input-group date-input-group">
                                        <input type="date"
                                            class="form-control @error('last_checkup_date') is-invalid @enderror"
                                            id="last_checkup_date" name="last_checkup_date"
                                            value="{{ old('last_checkup_date', $healthRecord ? optional($healthRecord->last_checkup_date)->format('Y-m-d') : '') }}">
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
                                <label for="checkup_loc" class="col-md-2 col-form-label">Location Checkup :</label>
                                <div class="col-md-4">
                                    <textarea class="form-control @error('checkup_loc') is-invalid @enderror" id="checkup_loc" name="checkup_loc" rows="4"
                                        placeholder="....">{{ old('checkup_loc', $healthRecord->checkup_loc ?? '') }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="form-group row">
                                <label for="notes" class="col-md-2 col-form-label">Notes :</label>
                                <div class="col-md-4">
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4"
                                        placeholder="Notes of Your Health Record">{{ old('notes', $healthRecord->notes ?? '') }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
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
@endsection
