@extends('layouts.admin')

@section('title', 'Edit Data Karyawan')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Edit Data Karyawan')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Memuat CSS khusus untuk halaman ini --}}
    <link rel="stylesheet" href="{{ asset('css/form-edit.css') }}">
@endpush

@section('content')
    <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @csrf

        {{-- 1. Panggil partial menu tab --}}
        @include('employees.partials.tab-menu', ['employee' => $employee])

        {{-- 2. Container untuk konten form --}}
        <div class="form-content-container">
            {{-- 
                Logika untuk menampilkan form yang sesuai dengan tab aktif.
                Saat ini, kita hanya fokus pada tab 'Personal' yang memuat _form.blade.php.
            --}}
            @if (request()->routeIs('employees.edit'))
                @include('employees.data._form', [
                    'employee' => $employee,
                    'divisions' => $divisions,
                    'positions' => $positions,
                    'users' => $users,
                ])
            @elseif(request()->routeIs('employees.address.edit'))
                @include('employees.data._address_form', ['employee' => $employee])
            @else
                {{-- Fallback atau form untuk tab lain bisa ditambahkan di sini --}}
                <div class="p-4">
                    <p>Konten untuk tab ini belum tersedia.</p>
                </div>
            @endif
        </div>
    </form>
@endsection
