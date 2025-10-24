@extends('layouts.admin')

@section('title', 'Employee Information')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
        .btn-deactive {
            border-radius: 5px;
            width: 110px;
            height: 37px;
            color: white;
            font-family: "Montserrat", sans-serif;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            border: none;
        }

        .btn-deactive {
            background-color: #ff4242;
            margin-left: 10px;
        }

        .btn-deactive:hover {
            background-color: #e63939;
            color: white;
        }

        @media (max-width: 768px) {
            .form-buttons-container {
                flex-direction: column-reverse;
                gap: 15px;
            }

            .btn-cancel,
            .btn-deactive {
                width: 100%;
                max-width: 100%;
            }

            .btn-deactive {
                margin-left: 0;
            }
        }
    </style>
@endpush

@section('content')
    <div class="form-content-container">
        <div class="card-body">
            <h4 class="mb-4 text-danger">
                <i class="fa-solid fa-user-slash me-2"></i>Deactivate Employee: {{ $employee->full_name }}
            </h4>

            {{-- Deactivation Form --}}
            <form id="deactivateForm" method="POST" action="{{ route('employees.deactivate', $employee) }}">
                @csrf
                @method('PUT')

                {{-- 📅 Last Working Date --}}
                <div class="form-group row align-items-center">
                    <label for="deactivation_date" class="col-md-2 col-form-label">
                        Last Working Date <span class="text-danger">*</span> :
                    </label>

                    <div class="col-md-3">
                        <div class="input-group date-input-group">
                            <input type="date" id="deactivation_date" name="deactivation_date"
                                class="form-control @error('deactivation_date') is-invalid @enderror"
                                value="{{ old('deactivation_date') }}" required>
                            <label for="deactivation_date" class="input-group-append">
                                <span class="input-group-text">
                                    <img src="{{ asset('img/calendar_icon.png') }}" alt="calendar">
                                </span>
                            </label>
                        </div>

                        @error('deactivation_date')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- 📝 Termination Reason --}}
                <div class="form-group row align-items-center">
                    <label for="termination_reason" class="col-md-2 col-form-label">
                        Termination Reason <span class="text-danger">*</span> :
                    </label>
                    <div class="col-md-3">
                        <select name="termination_reason" id="termination_reason"
                            class="form-control @error('termination_reason') is-invalid @enderror" required>
                            <option value="" disabled {{ old('termination_reason') ? '' : 'selected' }}>-- Select Reason --
                            </option>
                            <option value="Mengundurkan diri" {{ old('termination_reason') == 'Mengundurkan diri' ? 'selected' : '' }}>Resigned</option>
                            <option value="Pensiun" {{ old('termination_reason') == 'Pensiun' ? 'selected' : '' }}>Retired
                            </option>
                            <option value="Tidak lulus masa percobaan" {{ old('termination_reason') == 'Tidak lulus masa percobaan' ? 'selected' : '' }}>Failed probation</option>
                            <option value="Tidak cakap bekerja" {{ old('termination_reason') == 'Tidak cakap bekerja' ? 'selected' : '' }}>Incompetent to work</option>
                            <option value="Tidak mampu bekerja karena alasan kesehatan" {{ old('termination_reason') == 'Tidak mampu bekerja karena alasan kesehatan' ? 'selected' : '' }}>Unable to work due to health
                                reasons</option>
                            <option value="Meninggal dunia" {{ old('termination_reason') == 'Meninggal dunia' ? 'selected' : '' }}>Deceased</option>
                            <option value="Melakukan pelanggaran tata tertib dan disiplin" {{ old('termination_reason') == 'Melakukan pelanggaran tata tertib dan disiplin' ? 'selected' : '' }}>Violation of company rules and discipline</option>
                            <option value="Merugikan perusahaan" {{ old('termination_reason') == 'Merugikan perusahaan' ? 'selected' : '' }}>Caused company losses</option>
                            <option value="Terlibat tindakan pidana" {{ old('termination_reason') == 'Terlibat tindakan pidana' ? 'selected' : '' }}>Involved in a criminal act</option>
                        </select>
                        @error('termination_reason')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- 📘 Additional Notes --}}
                <div class="form-group row align-items-start">
                    <label for="termination_notes" class="col-md-2 col-form-label">
                        Additional Notes :
                    </label>
                    <div class="col-md-4">
                        <textarea id="termination_notes" name="termination_notes" rows="3"
                            class="form-control @error('termination_notes') is-invalid @enderror"
                            placeholder="Add any additional remarks if needed...">{{ old('termination_notes') }}</textarea>
                        @error('termination_notes')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Hidden submit button so JS handler can find and disable it --}}
                <button type="submit" id="deactivateFormSubmitBtn" style="display:none;"></button>

                {{-- 🔘 Action Buttons --}}
                <div class="form-buttons-container mt-4">
                    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-cancel">
                        Cancel
                    </a>

                    {{-- tombol ini hanya membuka modal konfirmasi --}}
                    <button type="button" class="btn btn-deactive"
                        onclick="showDeleteModal('deactivate-employee-{{ $employee->id }}')">
                        Deactivate
                    </button>
                </div>
            </form>

            {{-- Deactivation Confirmation Modal
            pass useFormId so modal will submit the main form when user confirms --}}
            <x-deactive-modal modalId="deactivate-employee-{{ $employee->id }}" :action="route('employees.deactivate', $employee)" method="POST" title="Confirm Employee Deactivation"
                message="Are you sure you want to deactivate this employee?" iconClass="tab-close-inactive"
                useFormId="deactivateForm" {{-- <-- new prop --}} />

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Disable submit when form is submitted
        document.getElementById('deactivateForm').addEventListener('submit', function (e) {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Saving...';
            }
        });
    </script>
@endpush