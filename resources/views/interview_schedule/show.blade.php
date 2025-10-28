@extends('layouts.admin')

@section('title', 'Interview Schedule Detail')

@push('styles')
<style>
.detail-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 32px;
    background: #FEFEF9;
}

.detail-title {
    font-weight: 700;
    font-size: 22px;
    margin-bottom: 16px;
}

.detail-box {
    background: #FAFBEF;
    border-radius: 10px;
    outline: 1px rgba(0, 0, 0, 0.2) solid;
    outline-offset: -1px;
    padding: 32px;
}

.detail-row {
    display: flex;
    align-items: flex-start;
    flex-wrap: wrap;
    margin-bottom: 12px;
}

.detail-label {
    width: 30%;
    font-weight: 600;
    font-size: 14px;
    margin-top: 6px;
}

.detail-value {
    width: 70%;
    font-family: 'Manrope', sans-serif;
    color: black;
    font-size: 14px;
}

.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 32px;
}

.action-buttons .btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    border: none;
}

.btn-warning { background-color: #FEC107; color: black; }
.btn-danger { background-color: #9A3B3B; color: white; }
.btn-secondary { background-color: #3498db; color: white; }
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

@section('content')
<div class="detail-container">
    <h3 class="detail-title">Interview Schedule Detail</h3>

    <div class="detail-box">

        {{-- Applicant --}}
        <div class="detail-row">
            <div class="detail-label">Applicant</div>
            <div class="detail-value">{{ $schedule->applicant?->full_name ?? 'Applicant #' . $schedule->applicant_id }}</div>
        </div>

        {{-- Interview Type --}}
        <div class="detail-row">
            <div class="detail-label">Interview Type</div>
            <div class="detail-value">{{ $schedule->interview_type ?? '-' }}</div>
        </div>

        {{-- Date & Time --}}
        <div class="detail-row">
            <div class="detail-label">Date & Time</div>
            <div class="detail-value">
                {{ \Carbon\Carbon::parse($schedule->interview_date)->format('d M Y, H:i') }}
            </div>
        </div>

        {{-- Interviewer --}}
        <div class="detail-row">
            <div class="detail-label">Interviewer</div>
            <div class="detail-value">
                {{ $schedule->interviewer?->employee?->full_name ?? $schedule->interviewer?->name ?? '-' }}
                @if($schedule->interviewer?->role)
                    ({{ ucfirst($schedule->interviewer->role) }})
                @endif
            </div>
        </div>

        {{-- Location Type --}}
        <div class="detail-row">
            <div class="detail-label">Location Type</div>
            <div class="detail-value">{{ ucfirst($schedule->location ?? '-') }}</div>
        </div>

        {{-- Meeting Link / Location --}}
        @if($schedule->location === 'online')
            <div class="detail-row">
                <div class="detail-label">Meeting Link</div>
                <div class="detail-value">
                    @if($schedule->meeting_link)
                        <a href="{{ $schedule->meeting_link }}" target="_blank" title="Click the URL to open the link">{{ $schedule->meeting_link }}</a>
                    @else
                        -
                    @endif
                </div>
            </div>
        @endif

        {{-- Description --}}
        <div class="detail-row">
            <div class="detail-label">Description</div>
            <div class="detail-value">{{ $schedule->result ?? '-' }}</div>
        </div>

        {{-- Action Buttons --}}
        <div class="action-buttons">
            @if(in_array(Auth::user()->role, ['superadmin', 'hc']))
                <a href="{{ route('interview-schedule.edit', [$applicant->id, $schedule->id]) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('interview-schedule.destroy', [$applicant->id, $schedule->id]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button onclick="return confirm('Are you sure?')" class="btn btn-danger">Delete</button>
                </form>
            @endif
            <a href="{{ route('interview-schedule.index', $applicant->id) }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
