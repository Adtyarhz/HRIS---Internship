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

@section('content-wrapper')
    @include('employees.partials.tab-menu', ['employee' => $employee])
    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">

                    <form id="updateForm"
                        action="{{ route('employees.family-dependents.update', [$employee->id, $familyDependent->id]) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-12">
                                @include('employees.family-dependents._form', ['familyDependent' => $familyDependent])
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">
                                    <button type="button" class="btn btn-delete"
                                        onclick="showDeleteModal('family-dependent-{{ $familyDependent->id }}')">
                                        Delete
                                    </button>
                                    <a href="{{ route('employees.family-dependents.index', $employee->id) }}"
                                        class="btn btn-cancel">Cancel</a>
                                    <button type="submit" class="btn btn-submit" form="updateForm">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Delete Modal --}}
                    <x-delete-modal modalId="family-dependent-{{ $familyDependent->id }}"
                        :action="route('employees.family-dependents.destroy', [$employee->id, $familyDependent->id])"
                        message="Are you sure to delete this Family Dependent?" />
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.getElementById('updateForm').addEventListener('submit', function (e) {
            console.log('Form submitted with method: PUT');
            console.log('Form data:', new FormData(this));
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Saving...';
            }
        });
    </script>
@endpush