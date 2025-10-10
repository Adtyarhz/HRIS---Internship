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

@section('content-wrapper')
    @include('employees.partials.tab-menu', ['employee' => $employee])
    <section class="content">
        <div class="container-fluid">
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

                    <form action="{{ route('employees.work-experience.store', $employee) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        {{-- ⛳ Ini WAJIB: kirim nilai null ke partial --}}
                        @include('employees.data.work-experience._form', ['workExperience' => null])

                        {{-- ✅ Tombol di kanan bawah --}}
                        <div class="form-buttons-container mt-4">
                            <a href="{{ route('employees.work-experience.index', $employee) }}"
                                class="btn btn-cancel">Cancel</a>
                            <button type="submit" class="btn btn-submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection