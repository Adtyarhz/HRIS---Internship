{{--
    File: resources/views/employees/edit.blade.php
    Deskripsi:
    View utama untuk halaman edit.
    - Menghapus wrapper .card untuk menghindari konflik gaya dengan desain kustom.
    - Menyiapkan tag <form> dengan method dan action yang benar.
    - Menyertakan partial tab-menu dan _form untuk menampilkan konten.
--}}
@extends('layouts.admin')

@section('title', 'Edit Data Karyawan')
@section('content_header', 'Edit Data Karyawan')

@section('content')
    {{-- 
        Kita tidak lagi menggunakan .card dari template admin.
        Sebagai gantinya, kita langsung merender komponen tab dan form
        yang sudah memiliki styling sendiri.
    --}}
    <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @csrf

        {{-- 1. Panggil partial menu tab --}}
        @include('employees.partials.tab-menu', ['employee' => $employee])

        {{-- 2. Panggil partial form untuk konten tab --}}
        {{-- Di sini, kita asumsikan _form.blade.php adalah untuk tab "Personal" --}}
        @include('employees.data._form', ['employee' => $employee])

    </form>
@endsection