@extends('layouts.admin')

@section('title', 'Tambah Indikator KPI Baru')
@section('header_icon', 'icon-park-outline--add')
@section('content_header', 'Tambah Indikator KPI Baru')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="form-content-container">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('kpi-indicators.store') }}" method="POST">
                    @csrf
                    @include('kpi.indicators._form', ['kpiIndicator' => new \App\Models\KpiIndicator()])

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-buttons-container">
                                <a href="{{ route('kpi-indicators.index') }}" class="btn btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
