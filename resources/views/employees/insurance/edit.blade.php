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
            .btn-cancel,
            .btn-delete {
                width: 100%;
                max-width: 100%;
            }

            .btn-submit {
                margin-left: 0px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- 1. Tab menu --}}
        @include('employees.partials.tab-menu', ['employee' => $employee])

        {{-- 2. Container form --}}
        <div class="form-content-container">
            <div class="card-body">

                <form id="updateForm" action="{{ route('employees.insurance.update', [$employee, $insurance]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @include('employees.insurance._form', ['insurance' => $insurance])

                    {{-- Tombol Aksi --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-buttons-container">
                                <button type="button" class="btn btn-delete"
                                    onclick="showDeleteModal('insurance-{{ $insurance->id }}')">Delete</button>
                                <a href="{{ route('employees.insurance.index', $employee) }}" class="btn btn-cancel">Cancel</a>
                                <button type="submit" form="updateForm" class="btn btn-submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Modal Delete -->
    <x-delete-modal 
        modalId="insurance-{{ $insurance->id }}" 
        :action="route('employees.insurance.destroy', [$employee, $insurance])" 
        message="Are you sure to delete this Insurance?" 
    />
@endsection
