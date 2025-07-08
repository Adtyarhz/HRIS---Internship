@extends('layouts.admin')

@section('title', 'Employee Information')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        {{-- 1. Panggil partial menu tab --}}
        @include('employees.partials.tab-menu', ['employee' => $employee])

        {{-- 2. Container untuk konten form --}}
        <div class="form-content-container">
            <div class="card-body">

                <form action="{{ route('employees.family-dependents.store', $employee->id) }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-12">

                            @include('employees.family-dependents._form', ['familyDependent' => null])

                            {{-- Tombol Aksi --}}
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="form-buttons-container">
                                        <a href="{{ route('employees.family-dependents.index', $employee->id) }}"
                                            class="btn btn-cancel">Cancel</a>
                                        <button type="submit" class="btn btn-submit">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
