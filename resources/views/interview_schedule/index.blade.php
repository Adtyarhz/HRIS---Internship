@extends('layouts.admin')

@section('title', 'Interview Schedule Management')

@push('styles')
<style>
    .header-with-icon {
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 5px;
    }

    .header-with-icon .custom-hamburger {
        margin-right: 6px;
        width: 35px;
        height: 35px;
        color: #000;
    }

    .schedule-header {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem; /* lebih kecil */
    }

    .schedule-header h2 {
        font-size: 22px;
        font-weight: bold;
        font-family: 'Noto Sans Georgian', sans-serif;
        margin: 0;
    }

    .btn-add {
        background-color: #9A3B3B;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.3s;
        display: inline-block;
    }

    .btn-add:hover {
        background-color: #7a2f2f;
    }

    .interview-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Manrope', sans-serif;
        margin-bottom: 1rem;
    }

    .interview-table thead th {
        background-color: #DFD9B6;
        color: #000;
        font-weight: 600;
        padding: 10px;
        border: 1px solid #aaa;
        text-align: center;
    }

    .interview-table tbody td {
        background-color: #F3F1E0;
        padding: 10px;
        border: 1px solid #aaa;
        vertical-align: middle;
        text-align: center;
    }

    .btn-detail {
        background-color: #b44343ff;
        color: white;
        border: none;
        padding: 7px 14px;
        border-radius: 6px;
        font-weight: bold;
        font-size: 14px;
        font-family: 'Manrope', sans-serif;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn-detail:hover {
        background-color: #333;
        color: white;              /* biar teks tetap putih */
        text-decoration: none;     /* hilangkan underline */
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
    }

    .pagination a,
    .pagination span {
        padding: 4px 8px;
        border: 1px solid #ccc;
        border-radius: 6px;
        text-decoration: none;
        color: #000;
        font-size: 14px;
        font-family: 'Manrope', sans-serif;
    }

    .pagination a:hover {
        background-color: #DFD9B6;
    }

    .pagination .disabled {
        color: #999;
        cursor: not-allowed;
    }

    .btn-back {
        display: inline-block;
        margin-top: 12px;
        background-color: #3498db;
        color: white;
        padding: 8px 24px;
        border-radius: 6px;
        font-weight: 600;
        float: right;
        text-decoration: none;
    }

    .btn-back:hover {
        background-color: #5a6268;
        color: white;
        text-decoration: none;
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
@include('interview_schedule.alert')
<div class="schedule-header">
    <h2>Interview Schedules of {{ $applicant->full_name }}</h2>
</div>

@php
    $userRole = Auth::user()->role;
@endphp

@if($canAddInterview)
<div style="text-align: right; margin-bottom: 1rem;">
    <a href="{{ route('interview-schedule.create', $applicant->id) }}" class="btn-add">+ Add Interview Schedule</a>
</div>
@endif

<table class="interview-table">
    <thead>
        <tr>
            <th>No</th>
            <th>Type</th>
            <th>Date</th>
            <th>Interviewer</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($schedules as $i => $schedule)
            <tr>
                <td>{{ $i + $schedules->firstItem() }}</td>
                <td>{{ $schedule->interview_type }}</td>
                <td>{{ $schedule->interview_date }}</td>
                <td>{{ $schedule->interviewer }}</td>
                <td>
                    <a href="{{ route('interview-schedule.show', [$applicant->id, $schedule->id]) }}" class="btn-detail">Detail</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No interview schedules found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="pagination">
    {{ $schedules->links('pagination::custom') }}
</div>

<a href="{{ route('applicants.index') }}" class="btn-back">Back</a>
@endsection