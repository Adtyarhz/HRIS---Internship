@extends('layouts.admin')

@section('title', 'Add Division')
@section('header_icon', 'fluent--organization-24-regular-01')
@section('content_header', 'Add New Division')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
@endpush

@section('content-wrapper')
    @include('organization.partials.tab-menu')
    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">
                    <form action="{{ route('organization.division.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-12">

                                {{-- Division Name --}}
                                <div class="form-group row align-items-center">
                                    <label for="name" class="col-md-2 col-form-label">Division Name <span
                                            class="text-danger">*</span> :</label>
                                    <div class="col-md-4">
                                        <input type="text" id="name" name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}" placeholder="Enter Division Name" required>
                                        @error('name')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">
                                    <a href="{{ route('organization.division.index') }}" class="btn btn-cancel">Cancel</a>
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