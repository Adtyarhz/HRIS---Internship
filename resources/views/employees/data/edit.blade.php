@extends('layouts.admin')

@section('title', 'Edit Data Karyawan')
@section('content_header', 'Edit Data Karyawan')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Formulir Edit Karyawan: {{ $employee->full_name }}</h3>
    </div>
    <!-- /.card-header -->
    <!-- form start -->
    <form action="{{ route('employees.update', $employee) }}" method="POST">
        @method('PUT')
        @include('employees.data._form', ['employee' => $employee])
    </form>
</div>
@endsection
