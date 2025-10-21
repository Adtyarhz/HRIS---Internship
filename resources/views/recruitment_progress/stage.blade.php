@extends('layouts.admin')

@section('title', 'Recruitment Applicant')

@push('styles')
<style>
    .stepper {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        max-width: 950px;
        margin-inline: auto;
    }

    .step {
        text-align: center;
        flex: 1;
        position: relative;
        cursor: default;
    }

    .step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 50%;
        right: -50%;
        height: 4px;
        width: 100%;
        background-color: #ccc;
        transform: translateY(-50%);
        z-index: 0;
    }

    .step .circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin: auto;
        line-height: 40px;
        color: white;
        font-weight: bold;
        z-index: 1;
        position: relative;
    }

    .step.accepted.active .circle {
        background-color: #198038;
    }
    .step.accepted:not(.active) .circle {
        background-color: #63c98cff;
    }

    .step.in_progress.active .circle {
        background-color: #263657;
    }
    .step.in_progress:not(.active) .circle {
        background-color: #AABFEB;
    }
    
    .step.rejected .circle {
        background-color: #e74c3c;
    }

    .step.pending .circle {
        background-color: #0043CE;
    }

    .step .label {
        margin-top: 10px;
        font-size: 12px;
    }
    .step.pending .circle {
        background-color: #cfd0d2ff; /* gray */
    }

    /* --- Tahap yang sudah bisa diisi tapi belum ada data --- */
/* Default emas cerah */
.step.available .circle {
    background-color: #e0d465ff;
    color: #fff;
}

/* Kalau available dan sedang dibuka → emas gelap */
.step.available.active .circle {
    background-color: #b9961bff;
}


.step.available .label {
    color: #007bff;
    font-weight: 600;
}

    .step.disabled {
        pointer-events: none;
        opacity: 0.9;
    }

    .stage-content {
        position: relative;
        background-color: #fdfdf5;
        padding: 30px;
        border-radius: 10px;
        border: 1px solid #ccc;
        max-width: 900px;
        margin: auto;
    }

    .stage-content h5 {
        font-weight: bold;
    }

    .edit-btn {
        margin-top: 15px;
        float: right;
        background-color: #C4A652;
        color: black;
        border: none;
        border-radius: 6px;
        padding: 5px 8px;
        font-family: 'Manrope', sans-serif;
    }

    .edit-button-wrapper {
        position: absolute;
        top: 2px;
        right: 15px;
    }

    .back-btn {
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 8px 30px;
        font-family: 'Manrope', sans-serif;
    }

    .back-container {
        max-width: 900px;
        margin: 20px auto 0 auto;
        display: flex;
        justify-content: flex-end;
        padding-right: 15px;
    }

    .stage-grid {
        display: grid;
        grid-template-columns: 200px 1fr;
        row-gap: 12px;
        column-gap: 20px;
        margin-top: 20px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 12px;
        font-size: 13px;
        color: white;
        border-radius: 6px;
        height: 28px;
        min-width: 80px;
        text-align: center;
    }

    .stage-grid .label {
        font-weight: bold;
        align-self: center;
    }

    .stage-grid .value {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: normal;
        word-break: break-word;
        flex-wrap: wrap;
    }

    .file-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #007bff;
        text-decoration: none;
        flex-wrap: wrap;
        max-width: 100%;
        word-break: break-word;
        line-height: 1.4;
    }

    .schedule-header {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .schedule-header h2 {
        font-size: 22px;
        font-weight: bold;
        font-family: 'Noto Sans Georgian', sans-serif;
        margin: 0;
    }
</style>
@endpush

@section('content_header')
    <div class="header-with-icon d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 2048 2048" class="mr-2">
            <path fill="currentColor"
                d="M2048 1280v768H1024v-768h256v-256h512v256zm-640 0h256v-128h-256zm512 384h-128v128h-128v-128h-256v128h-128v-128h-128v256h768zm0-256h-768v128h768zm-355-512q-54-61-128-94t-157-34q-80 0-149 30t-122 82t-83 123t-30 149q0 92-41 173t-116 136q45 23 84 53t73 68v338q0-79-30-149t-82-122t-123-83t-149-30q-80 0-149 30t-122 82t-83 123t-30 149H0q0-73 20-141t57-129t90-108t118-81q-74-54-115-135t-42-174q0-79 30-149t82-122t122-83t150-30q92 0 173 41t136 116q38-75 97-134t135-98q-74-54-115-135t-42-174q0-79 30-149t82-122t122-83t150-30q79 0 149 30t122 82t83 123t30 149q0 92-41 173t-116 136q68 34 123 85t93 118zM512 1408q53 0 99-20t82-55t55-81t20-100q0-53-20-99t-55-82t-81-55t-100-20q-53 0-99 20t-82 55t-55 81t-20 100q0 53 20 99t55 82t81 55t100 20m512-1024q0 53 20 99t55 82t81 55t100 20q53 0 99-20t82-55t55-81t20-100q0-53-20-99t-55-82t-81-55t-100-20q-53 0-99 20t-82 55t-55 81t-20 100"/>
        </svg>
        <h1 class="header-title mb-0">Recruitment Applicant</h1>
    </div>
@endsection
@section('content-wrapper')
   @include('recruitment.tabs', ['applicant' => $applicant])
<section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">
                    {{-- Section Title --}}
                   <div class="header-with-icon mb-4 d-flex align-items-center justify-content-between">
                    <h1 class="header-title mb-0">Recruitment Progress of {{ $applicant->full_name }}</h1>
</div>
<div class="container">
    <!-- Stepper -->
    <div class="stepper">
        @foreach ($stages as $index => $step)
            @php
                $progressData = $applicant->recruitmentProgresses->firstWhere('stage', $step);
                $status = $progressData->offering_status ?? null;

                $class = '';
                if ($status === 'accepted') {
                    $class = $step === $stage ? 'accepted active' : 'accepted';
                } elseif ($status === 'rejected') {
                    $class = 'rejected';
                } elseif ($status === 'in_progress') {
                    $class = $step === $stage ? 'in_progress active' : 'in_progress';
                } else {
                    $class = 'pending';
                }

                $canClick = true;
                if ($index > 0) {
                    $prev = $applicant->recruitmentProgresses->firstWhere('stage', $stages[$index - 1]);
                    $canClick = $prev && $prev->offering_status === 'accepted';
                }   
                 //Jika tahap bisa diakses tapi belum ada data progress → tandai "available"
                    if ($canClick && !$progressData) {
                    $class = $step === $stage ? 'available active' : 'available';
                }

                $disabled = !$canClick || ($applicant->recruitmentProgresses->firstWhere('offering_status', 'rejected') && $step !== $stage);
            @endphp

            <div class="step {{ $class }} {{ $disabled ? 'disabled' : '' }}">
                @if (!$disabled)
                    <a href="{{ route('recruitment.stage.show', [$applicant->id, $step]) }}" style="text-decoration:none">
                        <div class="circle">{{ $index + 1 }}</div>
                        <div class="label">{{ ucwords(str_replace('_', ' ', $step)) }}</div>
                    </a>
                @else
                    <div class="circle">{{ $index + 1 }}</div>
                    <div class="label">{{ ucwords(str_replace('_', ' ', $step)) }}</div>
                @endif
            </div>
        @endforeach
    </div>

@php
    use Illuminate\Support\Facades\Auth;

    $permissions = [
        'cv_screening' => ['superadmin', 'hc'],
        'general_knowledge_test' => ['superadmin', 'hc'],
        'user_assessment' => ['superadmin', 'manager', 'section_head', 'hc'],
        'hc_interview' => ['superadmin', 'hc'],
        'bod_interview' => ['superadmin','direksi', 'hc'],
        'offering_letter' => ['superadmin', 'hc'],
    ];

    $user = Auth::user();
    $canEdit = false;

    if (isset($permissions[$stage]) && in_array($user->role, $permissions[$stage])) {
        // Khusus untuk tahap user_assessment
        if ($stage === 'user_assessment' && in_array($user->role, ['manager', 'section_head'])) {
            $isInterviewer = $applicant->interviewSchedules()
                ->where('interviewer_id', $user->id)
                ->exists();

            $canEdit = $isInterviewer;
        } else {
            // Tahap selain user_assessment, izinkan langsung
            $canEdit = true;
        }
    }
@endphp

<!-- Stage Detail -->
<div class="stage-content">
    {{-- Tombol Action --}}
    @if ($canEdit)
        @if ($progress)
            @if ($stage === 'offering_letter')
                @if ($progress->offering_status === 'accepted')
                    <div class="edit-button-wrapper">
                        <a href="{{ route('applicants.show', $applicant->id) }}" class="edit-btn">
                            <i class="fas fa-id-card"></i> Go to Applicant Data
                        </a>
                    </div>
                @elseif ($progress->offering_status === 'in_progress')
                    <div class="edit-button-wrapper">
                        <a href="{{ route('recruitment.stage.edit', [$applicant->id, $stage]) }}" class="edit-btn">
                            <i class="fas fa-id-card"></i> Edit Recruitment Data
                        </a>
                    </div>
                @endif
            @else
                @if ($progress->offering_status !== 'rejected')
                    <div class="edit-button-wrapper">
                        <a href="{{ route('recruitment.stage.edit', [$applicant->id, $stage]) }}" class="edit-btn">
                            <i class="fas fa-id-card"></i> Edit Recruitment Data
                        </a>
                    </div>
                @endif
            @endif
        @else
            {{-- Belum ada progress --}}
            <div class="edit-button-wrapper">
                <a href="{{ route('recruitment.stage.edit', [$applicant->id, $stage]) }}" class="edit-btn">
                    <i class="fas fa-id-card"></i> Fill Recruitment Data
                </a>
            </div>
        @endif
    @endif

        {{-- Detail Stage --}}
        @if ($progress)
            <div class="stage-grid">
                <div class="label">Recruitment Status:</div>
                <div class="value">
                    <span class="status-badge" style="background-color: 
                        {{ $progress->offering_status == 'accepted' ? '#2ecc71' : 
                           ($progress->offering_status == 'rejected' ? '#e74c3c' : '#3498db') }}">
                        {{ $progress->offering_status }}
                    </span>
                </div>

                <div class="label">Status Date:</div>
                <div class="value">
                    {{ $progress->status_date ? \Carbon\Carbon::parse($progress->status_date)->format('d/m/Y') : '-' }}
                </div>

                <div class="label">Notes:</div>
                <div class="value notes-value">{!! nl2br(e($progress->notes ?? '-')) !!}</div>

                @if ($progress->offering_status == 'rejected')
                    <div class="label">Rejected Reason:</div>
                    <div class="value">{{ $progress->rejected_reason }}</div>
                @endif

                @php
                    $cvScreening = $applicant->recruitmentProgresses->firstWhere('stage', 'cv_screening');
                @endphp
                <div class="label">Contract Type:</div>
                <div class="value">{{ $cvScreening->contract_type ?? '-' }}</div>

                <div class="label">Test Result:</div>
                <div class="value">{{ $progress->test_result ?? '-' }}</div>

                <div class="label">Score:</div>
                <div class="value">{{ $progress->score ?? '-' }}</div>

                @if ($stage === 'hc_interview')
                    <div class="label">SLIK Recap:</div>
                    <div class="value">{{ $progress->slik_recap ?? '-' }}</div>
                @endif

                {{-- Hide Result File for offering_letter --}}
@if ($progress->result_file && $stage !== 'offering_letter')
    <div class="label">Result File:</div>
    <div class="value">
        <a href="{{ asset('storage/' . $progress->result_file) }}" target="_blank" class="file-link">
            <i class="fas fa-file-alt"></i>
            {{ basename($progress->result_file) }}
        </a>
    </div>
@endif

            </div>
        @else
            <p class="text-center text-muted">No data available for this stage.</p>
        @endif
    </div>

    <div class="back-container">
        <a href="{{ route('applicants.index') }}" class="back-btn">
            Back
        </a>
    </div>

</div>
@endsection
