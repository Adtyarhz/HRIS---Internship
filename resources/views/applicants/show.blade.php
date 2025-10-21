@extends('layouts.admin')

@section('title', 'Recruitment Applicant')
@section('content_header', 'Recruitment Applicant')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    .detail-wrapper {
        background-color: #FAFBEF;
        padding: 60px 30px 30px 30px;
        border-radius: 10px;
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        position: relative;
        font-family: 'Manrope', sans-serif;
        border: 1px solid #ddd;
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .detail-wrapper {
            padding: 20px;
        }
    }

    .detail-row {
        margin-bottom: 15px;
    }

    .detail-label {
        font-weight: bold;
        display: block;
        margin-bottom: 4px;
        color: #000;
    }

    .detail-value {
        color: #333;
        word-wrap: break-word;
    }

    .btn-edit {
        position: absolute;
        top: 20px;
        right: 30px;
        background-color: #C4A652;
        color: #000;
        border: 1px solid #ccc;
        padding: 6px 14px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-edit i {
        font-size: 14px;
    }

    .btn-edit:hover {
        background-color: #e1d9b6;
    }

    .btn-back,
    .btn-delete {
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: bold;
        border: none;
        font-size: 15px;
    }

    .btn-back {
        background-color: #1C6DD0;
        color: #fff;
    }

    .btn-back:hover {
        background-color: #e1d9b6;
    }

    .btn-delete {
        background-color: #9A3B3B;
        color: #fff;
    }

    .btn-delete:hover {
        background-color: #e1d9b6;
    }

    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 40px;
    }

    .file-link {
        background-color: transparent;
        color: #007bff;
        font-size: 14px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        word-break: break-all;
    }

    .file-link i {
        color: #007bff;
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
                d="M2048 1280v768H1024v-768h256v-256h512v256zm-640 0h256v-128h-256zm512 384h-128v128h-128v-128h-256v128h-128v-128h-128v256h768zm0-256h-768v128h768zm-355-512q-54-61-128-94t-157-34q-80 0-149 30t-122 82t-83 123t-30 149q0 92-41 173t-116 136q45 23 84 53t73 68v338q0-79-30-149t-82-122t-123-83t-149-30q-80 0-149 30t-122 82t-83 123t-30 149H0q0-73 20-141t57-129t90-108t118-81q-74-54-115-135t-42-174q0-79 30-149t82-122t122-83t150-30q92 0 173 41t136 116q38-75 97-134t135-98q-74-54-115-135t-42-174q0-79 30-149t82-122t122-83t150-30q79 0 149 30t122 82t83 123t30 149q0 92-41 173t-116 136q68 34 123 85t93 118zM512 1408q53 0 99-20t82-55t55-81t20-100q0-53-20-99t-55-82t-81-55t-100-20q-53 0-99 20t-82 55t-55 81t-20 100q0 53 20 99t55 82t81 55t100 20m512-1024q0 53 20 99t55 82t81 55t100 20q53 0 99-20t82-55t55-81t20-100q0-53-20-99t-55-82t-81-55t-100-20q-53 0-99 20t-82 55t-55 81t-20 100"/>
        </svg>
        <h1 class="header-title mb-0">Recruitment Applicant</h1>
    </div>
@endsection

@section('content-wrapper')
    @include('recruitment.tabs')

    <section class="content">
        <div class="container-fluid">
            <d class="form-content-container">
<div class="detail-wrapper">

   {{-- ================== TOMBOL EDIT / CONVERT ================== --}}
@php
    $hasRejected = $applicant->recruitmentProgresses()->where('offering_status', 'rejected')->exists();
    $offeringAccepted = $applicant->recruitmentProgresses()
        ->where('stage', 'offering_letter')
        ->where('offering_status', 'accepted')
        ->latest('created_at')
        ->first();
    $role = auth()->user()->role;
@endphp

@if($hasRejected)
    {{-- Jangan tampilkan tombol apapun --}}
@elseif($offeringAccepted)
    {{-- Kalau ada offering accepted --}}
    @if(($role === 'superadmin' || $role === 'hc') && !$isConverted)
        <a href="{{ route('employees.convert', $applicant->id) }}" class="btn-edit">
            <i class="fa-solid fa-user-check"></i> Convert to Employee
        </a>
    @endif
@else
    {{-- Kalau belum ada offering accepted --}}
    <a href="{{ route('applicants.edit', $applicant) }}" class="btn-edit">
        <i class="fa-solid fa-id-card"></i> Edit Applicant Data
    </a>
@endif


    {{-- ================== DETAIL APPLICANT ================== --}}
    @foreach ([
        'Applicant Fullname' => $applicant->full_name,
        'Applicant Position' => $applicant->position?->title,
        'Email' => $applicant->email,
        'Phone' => $applicant->phone,
        'Address' => $applicant->address,
        'Last Education' => $applicant->last_education,
        'Institution Name' => $applicant->origin,
        'GPA / Score' => $applicant->gpa_score,
        'Division' => $applicant->division?->name,
    ] as $label => $value)
        <div class="detail-row">
            <span class="detail-label">{{ $label }}</span>
            <span class="detail-value">{{ $value ?: '-' }}</span>
        </div>
    @endforeach

    {{-- Resume --}}
    @if ($applicant->resume_file)
        <div class="detail-row">
            <span class="detail-label">Resume File</span>
            <a href="{{ asset('storage/' . $applicant->resume_file) }}" target="_blank" class="file-link">
                <i class="fa-solid fa-file-lines"></i>
                {{ Str::afterLast($applicant->resume_file, '_') }}
            </a>
        </div>
    @endif

    {{-- ================== ACTION BUTTONS ================== --}}
    <div class="action-buttons">
        <a href="{{ route('applicants.index') }}" class="btn-back">Back</a>
        <button type="button" class="btn-delete" onclick="showDeleteModal()">Delete</button>
    </div>

    {{-- ================== MODAL DELETE ================== --}}
    <div id="deleteModal" style="display:none; position:fixed; top:20%; left:50%; transform:translate(-50%, -50%); z-index:1000; width:100%;">
        <div style="margin:0 auto; background:white; border-radius:12px; padding:24px 32px; width:90%; max-width:520px; box-shadow:0 4px 20px rgba(63,63,63,0.2);">
            <div style="display:flex; align-items:center; gap:16px;">
                <div style="background:#FFEA9F; border-radius:8px; width:48px; height:48px; display:flex; justify-content:center; align-items:center;">
                   <span class="iconify" data-icon="fa6-solid:trash-can" style="font-size:20px; color:#9A3B3B"></span>
                </div>
                <div style="font-size:18px; font-family:Inter, sans-serif; font-weight:600; color:black;">
                    Are you sure you want to delete this applicant?
                </div>
            </div>

            <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap; margin-top:24px;">
                <button onclick="hideDeleteModal()" style="width:160px; height:48px; background:#9A3B3B; color:white; font-size:16px; font-family:Inter, sans-serif; font-weight:500; border:none; border-radius:8px; outline:1px rgba(0,0,0,0.2) solid;">Cancel</button>

                <form action="{{ route('applicants.destroy', $applicant) }}" method="POST" style="margin:0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="width:160px; height:48px; background:#F9FCE6; color:black; font-size:16px; font-family:Inter, sans-serif; font-weight:500; border:none; border-radius:8px; outline:1px rgba(0,0,0,0.2) solid;">Yes</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modalOverlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background-color:rgba(0,0,0,0.4); z-index:999;"></div>
</div>

@push('scripts')
<script>
    function showDeleteModal() {
        document.getElementById('deleteModal').style.display = 'block';
        document.getElementById('modalOverlay').style.display = 'block';
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    }

    document.addEventListener('click', function(event) {
        const overlay = document.getElementById('modalOverlay');
        if (event.target === overlay) {
            hideDeleteModal();
        }
    });
</script>
@endpush
@endsection
