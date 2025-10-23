@extends('layouts.admin')

@section('title', 'Edit Division')
@section('header_icon', 'fluent--organization-24-regular-01')
@section('content_header', 'Edit Division')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
@endpush

@section('content-wrapper')
@include('organization.partials.tab-menu')
<section class="content">
<div class="container-fluid">
    <div class="form-content-container">
        <div class="card-body">

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form id="updateForm" action="{{ route('organization.division.update', $division->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-12">

                        {{-- Division Name --}}
                        <div class="form-group row align-items-center">
                            <label for="name" class="col-md-2 col-form-label">Division Name <span class="text-danger">*</span> :</label>
                            <div class="col-md-4">
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $division->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Buttons --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="form-buttons-container">
                            <button type="button" class="btn btn-delete"
                                onclick="showDeleteModal('division-{{ $division->id }}')">Delete</button>
                            <a href="{{ route('organization.division.index') }}" class="btn btn-cancel">Cancel</a>
                            <button type="submit" class="btn btn-submit">Submit</button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Delete Modal --}}
            <x-delete-modal 
                modalId="division-{{ $division->id }}" 
                :action="route('organization.division.destroy', $division->id)" 
                message="Are you sure you want to delete this division?" />
        </div>
    </div>
</div>
</section>
@endsection

@push('scripts')
<script>
    document.getElementById('updateForm').addEventListener('submit', function(e) {
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerText = 'Saving...';
        }
    });
</script>
@endpush
