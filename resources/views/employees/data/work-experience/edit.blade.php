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
        {{-- Tab Menu --}}
        @include('employees.partials.tab-menu', ['employee' => $employee])

        <div class="form-content-container">
            <div class="card-body">

                {{-- Form Update --}}
                <form id="updateForm"
                      action="{{ route('employees.work-experience.update', [$employee, $workExperience]) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @include('employees.data.work-experience._form', ['workExperience' => $workExperience])

                    {{-- Action Buttons --}}
                    <div class="form-buttons-container mt-4">
                        <button type="button" class="btn btn-delete"
                                onclick="showDeleteModal('work-experience-{{ $workExperience->id }}')">
                            Delete
                        </button>
                        <a href="{{ route('employees.work-experience.index', $employee) }}" class="btn btn-cancel">
                            Cancel
                        </a>
                        <button type="submit" form="updateForm" class="btn btn-submit">
                            Submit
                        </button>
                    </div>
                </form>

                {{-- Modal Delete --}}
                <x-delete-modal
                    modalId="work-experience-{{ $workExperience->id }}"
                    :action="route('employees.work-experience.destroy', [$employee, $workExperience])"
                    message="Are you sure to delete this Work Experience?" 
                />
            </div>
        </div>
    </div>
@endsection
