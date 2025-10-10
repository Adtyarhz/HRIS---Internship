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
                        action="{{ route('employees.educationhistory.update', [$employee->id, $educationHistory->id]) }}"
                        method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-12">
                                @include('employees.educationhistory._form', ['education' => $educationHistory])
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">
                                    <button type="button" class="btn btn-delete"
                                        onclick="showDeleteModal('education-history-{{ $educationHistory->id }}')">
                                        Delete
                                    </button>
                                    <a href="{{ route('employees.educationhistory.index', $employee->id) }}"
                                        class="btn btn-cancel">Cancel</a>
                                    <button type="submit" class="btn btn-submit" form="updateForm">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Delete Modal --}}
                    <x-delete-modal modalId="education-history-{{ $educationHistory->id }}"
                        :action="route('employees.educationhistory.destroy', [$employee->id, $educationHistory->id])"
                        message="Are you sure you want to delete this Education History?" />
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.getElementById('updateForm').addEventListener('submit', function (e) {
            console.log('Form submitted with method: PUT');
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Saving...';
            }
        });
    </script>
@endpush