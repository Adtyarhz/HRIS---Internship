@extends('layouts.admin')

@section('title', 'Tambah Karyawan Baru')
@section('content_header', 'Tambah Karyawan Baru')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Formulir Karyawan</h3>
    </div>
    <!-- /.card-header -->
    <!-- form start -->
    <form action="{{ route('employees.store') }}" method="POST">
        @include('employees.data._form')
    </form>
</div>
@endsection
