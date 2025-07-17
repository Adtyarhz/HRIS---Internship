@extends('layouts.admin')

@section('title', 'Career Path')
@section('header_icon', 'material-symbols--work-outline-01')
@section('content_header', 'Careers Administration')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Memuat CSS khusus untuk form ini --}}
    <link rel="stylesheet" href="{{ asset('css/career-path.css') }}">
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

                <form
                    action="{{ isset($careerHistory) ? route('employees.career_histories.update', [$employee, $careerHistory]) : route('employees.career_histories.store', $employee) }}"
                    method="POST">
                    @csrf
                    @if (isset($careerHistory))
                        @method('PUT')
                    @endif

                    @include('career-path.career_histories._form', [
                        'employee' => $employee,
                        'positions' => $positions,
                        'divisions' => $divisions,
                        'careerHistory' => $careerHistory,
                    ])

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-buttons-container">
                                <form action="{{ route('employees.career_histories.destroy', [$employee, $careerHistory]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this career history?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-delete">Delete</button>
                                </form>
                                <a href="{{ route('employees.career_histories.index', $employee) }}" class="btn btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-submit">Save</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
