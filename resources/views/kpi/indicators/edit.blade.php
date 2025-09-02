@extends('layouts.admin')

@section('title', 'Edit Indikator KPI')
@section('header_icon', 'icon-park-outline--edit')
@section('content_header', 'Edit Indikator KPI')

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

                <form action="{{ route('kpi-indicators.update', $kpiIndicator->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('kpi.indicators._form', ['kpiIndicator' => $kpiIndicator])
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-buttons-container">
                                <button type="button" class="btn btn-delete" onclick="showDeleteModal('kpi-indicator-{{ $kpiIndicator->id }}')">Delete</button>
                                <a href="{{ route('kpi-indicators.index') }}" class="btn btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Komponen Modal Delete -->
                <x-delete-modal 
                    modalId="kpi-indicator-{{ $kpiIndicator->id }}" 
                    :action="route('kpi-indicators.destroy', [$kpiIndicator->id])"
                    message="Are you sure to delete this Indicator?" 
                />


            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Nonaktifkan tombol submit saat pengiriman dan log data
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            console.log('Form submitted with method: PUT');
            console.log('Form data:', new FormData(this));
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Menyimpan...';
            }
        });
    </script>
@endpush
