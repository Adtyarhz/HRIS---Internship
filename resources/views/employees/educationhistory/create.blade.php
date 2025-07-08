@extends('layouts.admin')
@section('title', 'Add Education History')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

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
</style>
@endpush

@section('content')
    @include('employees.partials.tab-menu', ['employee' => $employee])
    <div class="form-wrapper">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

        <form action="{{ route('employees.educationhistory.store', $employee) }}" method="POST">
            @csrf
            @include('employees.educationhistory._form', ['education' => null])
            <div class="form-actions">
                <a href="{{ route('employees.educationhistory.index', $employee) }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-submit">Submit</button>
            </div>
        </form>
    </div>
@endsection
