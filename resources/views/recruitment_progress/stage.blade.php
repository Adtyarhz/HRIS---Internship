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

    .step.accepted.active .circle { background-color: #198038; }
    .step.accepted:not(.active) .circle { background-color: #63c98c; }

    .step.in_progress.active .circle { background-color: #263657; }
    .step.in_progress:not(.active) .circle { background-color: #AABFEB; }

    .step.rejected .circle { background-color: #e74c3c; }

    .step.pending .circle { background-color: #cfd0d2; }

    .step.available .circle {
        background-color: #e0d465;
        color: #fff;
    }

    .step.available.active .circle { background-color: #b9961b; }

    .step.available .label {
        color: #007bff;
        font-weight: 600;
    }

    .step.disabled { pointer-events: none; opacity: 0.9; }

    .stage-content {
        background-color: #fdfdf5;
        padding: 30px;
        border-radius: 10px;
        border: 1px solid #ccc;
        max-width: 900px;
        margin: auto;
        position: relative;
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
    }

    .stage-grid .label { font-weight: bold; }
    .stage-grid .value { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

    .file-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #007bff;
        text-decoration: none;
        flex-wrap: wrap;
        max-width: 100%;
        word-break: break-word;
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

@section('content-wrapper')
@include('recruitment.tabs', ['applicant' => $applicant])

<section class="content">
    <div class="container-fluid">
        <div class="form-content-container">
            <div class="card-body">
                <h1 class="header-title mb-4">Recruitment Progress of {{ $applicant->full_name }}</h1>

                {{-- Stepper --}}
                <div class="stepper">
                    @foreach ($stages as $index => $step)
                        @php
                            $progressData = $applicant->recruitmentProgresses->firstWhere('stage', $step);
                            $status = $progressData->offering_status ?? null;
                            $class = match($status) {
                                'accepted' => $step === $stage ? 'accepted active' : 'accepted',
                                'rejected' => 'rejected',
                                'in_progress' => $step === $stage ? 'in_progress active' : 'in_progress',
                                default => 'pending'
                            };
                            $canClick = $index === 0 || optional($applicant->recruitmentProgresses->firstWhere('stage', $stages[$index - 1]))->offering_status === 'accepted';
                            if ($canClick && !$progressData) $class = $step === $stage ? 'available active' : 'available';
                            $disabled = !$canClick || ($applicant->recruitmentProgresses->contains('offering_status', 'rejected') && $step !== $stage);
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

                {{-- Determine access --}}
                @php
                    use Illuminate\Support\Facades\Auth;
                    $permissions = [
                        'general_knowledge_test' => ['superadmin', 'hc'],
                        'computer_skills_test' => ['superadmin', 'hc'],
                        'hc_interview' => ['superadmin', 'hc'],
                        'user_assessment' => ['superadmin', 'manager', 'section_head'],
                        'bod_interview' => ['superadmin', 'direksi'],
                        'offering_letter' => ['superadmin', 'hc'],
                    ];
                    $user = Auth::user();
                    $canEdit = in_array($user->role, $permissions[$stage] ?? []);
                    if ($stage === 'user_assessment' && in_array($user->role, ['manager', 'section_head'])) {
                        $canEdit = $applicant->interviewSchedules()->where('interviewer_id', $user->id)->exists();
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
                     $generalKnowledgeTest = $applicant->recruitmentProgresses->firstWhere('stage', 'general_knowledge_test');
                @endphp
                <div class="label">Contract Type:</div>
                <div class="value">{{  $generalKnowledgeTest->contract_type ?? '-' }}</div>

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
                    <a href="{{ route('applicants.index') }}" class="back-btn">Back</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
