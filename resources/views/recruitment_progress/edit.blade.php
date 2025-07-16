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
        <!-- Ikon Recruitment -->
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 2048 2048" class="mr-2">
            <path fill="currentColor"
                d="M2048 1280v768H1024v-768h256v-256h512v256zm-640 0h256v-128h-256zm512 384h-128v128h-128v-128h-256v128h-128v-128h-128v256h768zm0-256h-768v128h768zm-355-512q-54-61-128-94t-157-34q-80 0-149 30t-122 82t-83 123t-30 149q0 92-41 173t-116 136q45 23 84 53t73 68v338q0-79-30-149t-82-122t-123-83t-149-30q-80 0-149 30t-122 82t-83 123t-30 149H0q0-73 20-141t57-129t90-108t118-81q-74-54-115-135t-42-174q0-79 30-149t82-122t122-83t150-30q92 0 173 41t136 116q38-75 97-134t135-98q-74-54-115-135t-42-174q0-79 30-149t82-122t122-83t150-30q79 0 149 30t122 82t83 123t30 149q0 92-41 173t-116 136q68 34 123 85t93 118zM512 1408q53 0 99-20t82-55t55-81t20-100q0-53-20-99t-55-82t-81-55t-100-20q-53 0-99 20t-82 55t-55 81t-20 100q0 53 20 99t55 82t81 55t100 20m512-1024q0 53 20 99t55 82t81 55t100 20q53 0 99-20t82-55t55-81t20-100q0-53-20-99t-55-82t-81-55t-100-20q-53 0-99 20t-82 55t-55 81t-20 100"/>
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
            <div class="detail-label">Offering Status</div>
            <div class="input-wrapper">
                <select name="offering_status" class="form-control">
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
                <input type="date" name="status_date" class="form-control" value="{{ old('status_date', $progress->status_date) }}">
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Notes</div>
            <div class="input-wrapper">
                <textarea name="notes" class="form-control">{{ old('notes', $progress->notes) }}</textarea>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Rejected Reason</div>
            <div class="input-wrapper">
                <input type="text" name="rejected_reason" class="form-control" value="{{ old('rejected_reason', $progress->rejected_reason) }}">
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Contract Type</div>
            <div class="input-wrapper">
                <select name="contract_type" class="form-control">
                    <option value="">-- Select Contract Type --</option>
                    <option value="Contract" {{ old('contract_type', $progress->contract_type) === 'Contract' ? 'selected' : '' }}>Contract</option>
                    <option value="Internship" {{ old('contract_type', $progress->contract_type) === 'Internship' ? 'selected' : '' }}>Internship</option>
                    <option value="Probation" {{ old('contract_type', $progress->contract_type) === 'Probation' ? 'selected' : '' }}>Probation</option>
                    <option value="Full-time" {{ old('contract_type', $progress->contract_type) === 'Full-time' ? 'selected' : '' }}>Full-time</option>
                </select>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Test Result</div>
            <div class="input-wrapper">
                <textarea name="test_result" class="form-control">{{ old('test_result', $progress->test_result) }}</textarea>
            </div>
        </div>

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

        <div class="detail-row">
            <div class="detail-label">Score</div>
            <div class="input-wrapper">
                <input type="text" name="score" class="form-control" value="{{ old('score', $progress->score) }}">
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Slik Recap</div>
            <div class="input-wrapper">
                <input type="text" name="slik_recap" class="form-control" value="{{ old('slik_recap', $progress->slik_recap) }}">
            </div>
        </div>

        <div class="action-buttons">
            <a href="{{ route('recruitment.stage.show', [$applicant->id, $stage]) }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">Submit</button>
        </div>
    </form>
</div>
@endsection
