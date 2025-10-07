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
                margin-left: 0px;
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

                    <form action="{{ route('employees.family-dependents.store', $employee->id) }}" method="POST">
                        @csrf


                        @include('employees.family-dependents._form', ['familyDependent' => null])

                        <div class="form-buttons-container mt-4">
                            <a href="{{ route('employees.family-dependents.index', $employee->id) }}"
                                class="btn btn-cancel">Cancel</a>
                            <button type="submit" class="btn btn-submit">Submit</button>

                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection