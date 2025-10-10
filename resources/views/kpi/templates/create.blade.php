@extends('layouts.admin')

@section('title', 'Key Performance Index')
@section('header_icon', 'ri--bill-line-01')
@section('content_header', 'Key Performance Index')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
@endpush

@section('content-wrapper')
    @include('kpi.partials.tab-menu')
    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">
                    <form action="{{ route('kpi-templates.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12">

                                <!-- Template Name -->
                                <div class="form-group row align-items-center">
                                    <label for="template_name" class="col-md-2 col-form-label">Template Name <span
                                            class="text-danger">*</span> :</label>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control @error('template_name') is-invalid @enderror"
                                            id="template_name" name="template_name" value="{{ old('template_name') }}"
                                            required placeholder="Enter Template Name">
                                        @error('template_name')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Position -->
                                <div class="form-group row align-items-center">
                                    <label for="position_id" class="col-md-2 col-form-label">Position <span
                                            class="text-danger">*</span> :</label>
                                    <div class="col-md-3">
                                        <select name="position_id" id="position_id"
                                            class="form-control @error('position_id') is-invalid @enderror" required>
                                            <option value="">-- Select Position --</option>
                                            @foreach($positions as $position)
                                                <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>{{ $position->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('position_id')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">
                                    <a href="{{ route('kpi-templates.index') }}" class="btn btn-cancel">Cancel</a>
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