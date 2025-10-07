@extends('layouts.admin')

@section('title', 'Employee Information')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Memuat CSS khusus untuk halaman ini --}}
    <link rel="stylesheet" href="{{ asset('css/form-edit.css') }}">
@endpush

@section('content-wrapper')
    @include('employees.partials.tab-menu', ['employee' => $employee])
    <section class="content">
        <div class="container-fluid">
            <form id="updateForm" action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                <div class="form-content-container">
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
                        <div class="p-4">
                            <p>Konten untuk tab ini belum tersedia.</p>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </section>    
@endsection

@push('scripts')
    <script>
        // Disable submit button on form submission
        document.getElementById('updateForm').addEventListener('submit', function (e) {
            console.log('Form submitted with method: PUT');
            console.log('Form data:', new FormData(this));
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Saving...';
            }
        });
    </script>
@endpush
