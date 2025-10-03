@extends('layouts.admin')

@section('title', 'Employee Information')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
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

@section('content')
    <div class="container-fluid">
        {{-- Tab Menu --}}
        @include('employees.partials.tab-menu', ['employee' => $employee])

        <div class="form-content-container">
            <div class="card-body">
                {{-- Error Message --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('employees.educationhistory.store', $employee) }}" method="POST">
                    @csrf

                    {{-- Include partial form --}}
                    @include('employees.educationhistory._form', ['education' => null])

                    {{-- Buttons --}}
                    <div class="form-buttons-container mt-4">
                        <a href="{{ route('employees.educationhistory.index', $employee) }}" class="btn btn-cancel">Cancel</a>
                        <button type="submit" class="btn btn-submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
