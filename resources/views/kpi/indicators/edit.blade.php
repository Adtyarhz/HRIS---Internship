@extends('layouts.admin')

@section('title', 'Key Performance Index')
@section('header_icon', 'ri--bill-line-01')
@section('content_header', 'Key Performance Index')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
@endpush

@section('content-wrapper')
    @include('kpi.partials.tab-menu')
    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">
                    <form action="{{ route('kpi-indicators.update', $kpiIndicator->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('kpi.indicators._form', ['kpiIndicator' => $kpiIndicator])
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">
                                    <button type="button" class="btn btn-delete"
                                        onclick="showDeleteModal('kpi-indicator-{{ $kpiIndicator->id }}')">Delete</button>
                                    <a href="{{ route('kpi-indicators.index') }}" class="btn btn-cancel">Cancel</a>
                                    <button type="submit" class="btn btn-submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Komponen Modal Delete -->
                    <x-delete-modal modalId="kpi-indicator-{{ $kpiIndicator->id }}" :action="route('kpi-indicators.destroy', [$kpiIndicator->id])" message="Are you sure to delete this Indicator?" />
                        
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Nonaktifkan tombol submit saat pengiriman dan log data
        document.getElementById('updateForm').addEventListener('submit', function (e) {
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