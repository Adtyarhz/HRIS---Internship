@extends('layouts.admin')

@section('title', 'Edit Applicant')
@section('content_header', 'Applicant Information')

@push('styles')
<style>
    .form-wrapper {
        background-color: #FDFBEF;
        padding: 30px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
    }

    .btn-cancel {
        background-color: #9A3B3B;
        color: #fff;
    }

    .btn-submit {
        background-color: #367FA9;
        color: #fff;
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
<div class="form-wrapper">
    <form action="{{ route('applicants.update', $applicant) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('applicants._form', ['applicant' => $applicant, 'divisions' => $divisions])

        <div class="form-actions">
            <a href="{{ route('applicants.show',  $applicant) }}" class="btn btn-cancel">Cancel</a>
            <button type="submit" class="btn btn-submit">Update</button>
        </div>
    </form>
</div>
@endsection
