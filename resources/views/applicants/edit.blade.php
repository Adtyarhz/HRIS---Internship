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
