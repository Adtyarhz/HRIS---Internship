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

    .modal-content {
        background-color: #FAFBEF;
        border-radius: 12px;
        border: none;
    }

    .modal-body {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 1rem 1.25rem;
    }

    .icon-wrapper {
        background-color: #FFEA9F;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        min-height: 40px;
    }

    .icon-wrapper i {
        color: #9A3B3B;
        font-size: 24px;
    }

    .modal-body p {
        margin: 0;
        font-weight: bold;
        font-size: 1rem;
        color: #000000;
        white-space: nowrap;
    }

    .modal-footer {
        justify-content: center;
        border: none;
        padding-top: 16px;
    }

    .btn-cancel-modal {
        background-color: #9A3B3B;
        color: #FAFBEF;
        border: none;
        font-weight: bold;
        min-width: 100px;
        border-radius: 6px;
        padding: 8px 16px;
    }

    .btn-cancel-modal:hover {
        background-color: #7b2e2e;
    }

    .btn-yes-modal {
        background-color: #F9FCE6;
        color: #000000;
        font-weight: bold;
        border: 1px solid #ccc;
        min-width: 100px;
        border-radius: 6px;
        padding: 8px 16px;
    }

    .btn-yes-modal:hover {
        background-color: #e6f1c9;
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
            <button type="button" class="btn btn-delete" data-toggle="modal" data-target="#deleteModal">
            <i class="fas fa-trash-alt"></i> Delete</button>
            <a href="{{ route('employees.educationhistory.index', $employee) }}" class="btn btn-cancel">Cancel</a>
            <button type="submit" class="btn btn-submit">Submit</button>
            </div>
        </form>
    </div>
    {{-- Modal Delete --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content p-4">
            <div class="modal-body">
                <div class="icon-wrapper">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <p>Are you sure to delete this Education History?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel-modal" data-dismiss="modal">Cancel</button>
                <form action="{{ route('employees.educationhistory.destroy', [$employee, $educationHistory]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-yes-modal">Yes</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
