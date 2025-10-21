@extends('layouts.admin')

@section('title', 'Edit Recruitment Progress')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    .stepper-container {
        position: relative;
        margin-bottom: 40px;
        padding: 20px 0;
    }
    .detail-wrapper {
        background-color: #FAFBEF;
        padding: 30px;
        border-radius: 10px;
        width: 100%;
        max-width: 900px;
        border: 1px solid #ddd;
        font-family: 'Manrope', sans-serif;
        box-sizing: border-box;
        position: relative;
        margin: auto;
    }
    .detail-row {
        margin-bottom: 15px;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
    }
    .detail-label {
        width: 30%;
        font-weight: bold;
        color: #000;
        font-size: 13px;
        margin-top: 8px;
    }
    .input-wrapper {
        width: 70%;
    }
    .form-control {
        width: 100%;
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 8px 10px;
        font-size: 13px;
        line-height: 1.5;
        font-family: 'Manrope', sans-serif;
    }
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }
    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 25px;
    }
    .btn-cancel {
        background-color: #8B1E1E;
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: 6px;
    }
    .btn-submit {
        background-color: #1B4965;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
    }
    .file-link {
        display: inline-block;
        margin-top: 5px;
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
        font-size: 13px;
    }
    .file-link:hover {
        text-decoration: underline;
    }
</style>
@endpush

@section('content_header')
    <div class="header-with-icon d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 2048 2048" class="mr-2">
            <path fill="currentColor"
                d="..."/>
        </svg>
        <h1 class="header-title mb-0">Recruitment Applicant</h1>
    </div>
@endsection

@section('content')
<div class="container" style="max-width: 950px; margin: auto;">
    <div class="stepper-container">
        <h4>Edit Stage: {{ ucwords(str_replace('_', ' ', $stage)) }}</h4>
    </div>

    <form action="{{ route('recruitment.stage.update', $applicant) }}" method="POST" enctype="multipart/form-data" class="detail-wrapper">
        @csrf
        @method('PUT')
        <input type="hidden" name="stage" value="{{ $stage }}">

        <div class="detail-row">
            <div class="detail-label">Recruitment Status</div>
            <div class="input-wrapper">
                <select name="offering_status" class="form-control" id="offering_status">
                    <option value="">-- Select Status --</option>
                    <option value="accepted" {{ old('offering_status', $progress->offering_status) === 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="rejected" {{ old('offering_status', $progress->offering_status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="in_progress" {{ old('offering_status', $progress->offering_status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                </select>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Status Date</div>
            <div class="input-wrapper">
               <input type="date" name="status_date" class="form-control" id="status_date" value="{{ old('status_date', $progress->status_date ? \Carbon\Carbon::parse($progress->status_date)->format('Y-m-d') : '') }}">
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Notes</div>
            <div class="input-wrapper">
                <textarea name="notes" class="form-control">{{ old('notes', $progress->notes) }}</textarea>
            </div>
        </div>

        {{-- Rejected Reason (Only if rejected) --}}
        <div class="detail-row" id="rejected_reason_wrapper" style="display: none;">
            <div class="detail-label">Rejected Reason</div>
            <div class="input-wrapper">
                <input type="text" name="rejected_reason" class="form-control" value="{{ old('rejected_reason', $progress->rejected_reason) }}">
            </div>
        </div>

        {{-- Contract Type --}}
        <div class="detail-row">
            <div class="detail-label">Contract Type</div>
            <div class="input-wrapper">
                @if ($stage === 'cv_screening')
                    <select name="contract_type" class="form-control">
                        <option value="">-- Select Contract Type --</option>
                        <option value="PKWT" {{ old('contract_type', $progress->contract_type) === 'PKWT' ? 'selected' : '' }}>PKWT</option>
                        <option value="PKWTT" {{ old('contract_type', $progress->contract_type) === 'PKWTT' ? 'selected' : '' }}>PKWTT</option>
                        <option value="Probation" {{ old('contract_type', $progress->contract_type) === 'Probation' ? 'selected' : '' }}>Probation</option>
                        <option value="Intern" {{ old('contract_type', $progress->contract_type) === 'Intern' ? 'selected' : '' }}>Intern</option>
                    </select>
                @else
                    <input type="text" class="form-control" value="{{ $contractType ?? '-' }}" disabled>
                @endif
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Test Result</div>
            <div class="input-wrapper">
                <textarea name="test_result" class="form-control">{{ old('test_result', $progress->test_result) }}</textarea>
            </div>
        </div>

        {{-- Hide Result File for offering_letter --}}
        @if ($stage !== 'offering_letter')
        <div class="detail-row">
            <div class="detail-label">Result File</div>
            <div class="input-wrapper">
                <input type="file" name="result_file" class="form-control">
                @if ($progress->result_file)
                    <a href="{{ asset('storage/' . $progress->result_file) }}" target="_blank" class="file-link">
                        <i class="fas fa-file-lines"></i> {{ basename($progress->result_file) }}
                    </a>
                @endif
            </div>
        </div>
        @endif

        <div class="detail-row">
            <div class="detail-label">Score</div>
            <div class="input-wrapper">
                <input type="text" name="score" class="form-control" value="{{ old('score', $progress->score) }}">
            </div>
        </div>

        {{-- Slik Recap only for HC Interview --}}
        @if ($stage === 'hc_interview')
        <div class="detail-row">
            <div class="detail-label">Slik Recap</div>
            <div class="input-wrapper">
                <input type="text" name="slik_recap" class="form-control" value="{{ old('slik_recap', $progress->slik_recap) }}">
            </div>
        </div>
        @endif

        <div class="action-buttons">
            <a href="{{ route('recruitment.stage.show', [$applicant->id, $stage]) }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">Submit</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const statusSelect = document.querySelector('select[name="offering_status"]');
        const rejectedReasonWrapper = document.getElementById('rejected_reason_wrapper');
        const statusDateInput = document.getElementById('status_date');

        // Simpan nilai awal
        const initialStatus = statusSelect.value;
        const initialDate = statusDateInput.value;

        function toggleRejectedReason() {
            rejectedReasonWrapper.style.display = (statusSelect.value === 'rejected') ? 'flex' : 'none';
        }

        function handleStatusChange() {
            toggleRejectedReason();

            if (statusSelect.value !== initialStatus) {
                statusDateInput.value = '';
            } else {
                statusDateInput.value = initialDate;
            }
        }

        statusSelect.addEventListener('change', handleStatusChange);
        toggleRejectedReason(); // jalankan sekali di awal
    });
</script>
@endpush