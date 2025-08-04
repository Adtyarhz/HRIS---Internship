@extends('layouts.admin')
@section('title', 'Edit Insurance')
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
        <form id="updateForm" action="{{ route('employees.insurance.update', [$employee, $insurance]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('employees.insurance._form', ['insurance' => $insurance])
        </form>

        <div class="form-actions">
            <button type="button" class="btn btn-delete" onclick="showDeleteModal('insurance-{{ $insurance->id }}')">Delete</button>
            <a href="{{ route('employees.insurance.index', $employee) }}" class="btn btn-cancel">Cancel</a>
            <button type="submit" form="updateForm" class="btn btn-submit">Submit</button>
        </div>
    </div>

    <!-- Komponen Modal Delete -->
    <x-delete-modal 
        modalId="insurance-{{ $insurance->id }}" 
        :action="route('employees.insurance.destroy', [$employee, $insurance])" 
        message="Are you sure to delete this Insurance?" 
    />
@endsection
