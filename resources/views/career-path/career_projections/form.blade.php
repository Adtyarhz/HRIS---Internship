@extends('layouts.admin')

@section('title', 'Career Path')
@section('header_icon', 'material-symbols--work-outline-01')
@section('content_header', 'Careers Administration')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Memuat CSS khusus untuk form ini --}}
    <link rel="stylesheet" href="{{ asset('css/career-path.css') }}">
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

                <form action="{{ route('employees.career_projection.storeOrUpdate', $employee) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <label class="page-title">
                                Career Projection for {{ $employee->full_name }}
                                <hr>
                            </label>
                            {{-- Projected Position --}}
                            <div class="form-group row align-items-center">
                                <label for="projected_position_id" class="col-md-2 col-form-label">Projected Position <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <select name="projected_position_id" id="projected_position_id"
                                        class="form-control @error('projected_position_id') is-invalid @enderror" required>
                                        <option value="">Select Position</option>
                                        @foreach ($positions as $id => $title)
                                            <option value="{{ $id }}"
                                                {{ old('projected_position_id', $careerProjection->projected_position_id ?? '') == $id ? 'selected' : '' }}>
                                                {{ $title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('projected_position_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Timeline --}}
                            <div class="form-group row align-items-center">
                                <label for="timeline" class="col-md-2 col-form-label">Timeline <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <select name="timeline" id="timeline"
                                        class="form-control @error('timeline') is-invalid @enderror" required>
                                        <option value="">Select Timeline</option>
                                        @foreach (['1 Tahun', '3 Tahun', '5 Tahun'] as $option)
                                            <option value="{{ $option }}"
                                                {{ old('timeline', $careerProjection->timeline ?? '') == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('timeline')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="form-group row align-items-center">
                                <label for="status" class="col-md-2 col-form-label">Status <span
                                        class="text-danger">*</span> :</label>
                                <div class="col-md-3">
                                    <select name="status" id="status"
                                        class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="">Select Status</option>
                                        @foreach (['Direncanakan', 'Disetujui', 'Tercapai', 'Dibatalkan'] as $option)
                                            <option value="{{ $option }}"
                                                {{ old('status', $careerProjection->status ?? '') == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Readiness Notes --}}
                            <div class="form-group row">
                                <label for="readiness_notes" class="col-md-2 col-form-label">Readiness Notes :</label>
                                <div class="col-md-4">
                                    <textarea name="readiness_notes" id="readiness_notes"
                                        class="form-control @error('readiness_notes') is-invalid @enderror" rows="6"
                                        placeholder="Description of Readiness Notes">{{ old('readiness_notes', $careerProjection->readiness_notes ?? '') }}</textarea>
                                    @error('readiness_notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-buttons-container">
                                <a href="{{ route('employees.showCareer', $employee) }}" class="btn btn-cancel">Cancel</a>
                                <button type="submit"
                                    class="btn btn-submit">{{ $careerProjection ? 'Update' : 'Save' }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
