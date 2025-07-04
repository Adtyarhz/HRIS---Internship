@extends('layouts.admin')
@section('title', 'Add Work Experience')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}" />
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
    </style>
@endpush

@section('content')
    {{-- ✅ Tab Menu --}}
    @include('employees.partials.tab-menu', ['employee' => $employee])

    {{-- ✅ Bungkus dengan kotak --}}
    <div class="form-wrapper">
        <form action="{{ route('employees.work-experience.store', $employee) }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- ⛳ Ini WAJIB: kirim nilai null ke partial --}}
            @include('employees.data.work-experience._form', ['workExperience' => null])

            {{-- ✅ Tombol di kanan bawah --}}
            <div class="form-actions">
                <a href="{{ route('employees.work-experience.index', $employee) }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-submit">Submit</button>
            </div>
        </form>
    </div>
@endsection
