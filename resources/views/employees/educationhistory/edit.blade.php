@extends('layouts.admin')
@section('title', 'Edit Education History')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        border: none;
    }
    .btn-cancel:hover {
        background-color: #7b2e2e;
    }
    .btn-submit {
        background-color: #367FA9;
        color: #fff;
        border: none;
    }
    .btn-submit:hover {
        background-color: #2b6282;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 12px 20px;
        max-width: 700px;
    }
    .form-grid label {
        font-size: 12px;
        font-weight: 500;
        font-family: 'Noto Sans Georgian', sans-serif;
        margin-bottom: 4px;
    }
    .form-grid .required::after {
        content: '*';
        color: red;
        margin-left: 2px;
    }
    .form-control {
        height: 30px;
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 5px;
        border: 1px solid rgba(0, 0, 0, 0.2);
    }
    .btn-delete {
        background-color: #FF0000;
        color: #fff;
        border: none;
    }
    .btn-delete:hover {
        background-color: #cc0000;
    }
</style>
@endpush

@section('content')
    @include('employees.partials.tab-menu', ['employee' => $employee])

    <div class="form-wrapper">
        <form action="{{ route('employees.educationhistory.update', [$employee, $educationHistory]) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Reuse the same form --}}
            @include('employees.educationhistory._form', ['education' => $educationHistory])

            <div class="form-actions">
            <button type="button" class="btn btn-delete" onclick="showDeleteModal('education-history-{{ $educationHistory->id }}')">Delete</button>
            <a href="{{ route('employees.educationhistory.index', $employee) }}" class="btn btn-cancel">Cancel</a>
            <button type="submit" class="btn btn-submit">Submit</button>
            </div>
        </form>
    </div>

    {{-- Modal Delete Komponen --}}
    <x-delete-modal
        modalId="education-history-{{ $educationHistory->id }}" 
        :action="route('employees.educationhistory.destroy', [$employee, $educationHistory])" 
        message="Are you sure you want to delete this Education History?" 
    />

@endsection
