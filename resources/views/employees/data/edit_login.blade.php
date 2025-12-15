@extends('layouts.admin')

@section('title', 'Employee Information')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Memuat CSS khusus untuk halaman ini --}}
    <link rel="stylesheet" href="{{ asset('css/form-edit.css') }}">
    <style>
        .btn-warning {
            border-radius: 5px;
            min-width: 110px;
            height: 37px;
            color: rgb(0, 0, 0);
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 400;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            border: none;
        }
    </style>
@endpush

@section('content-wrapper')
    <section class="content">
        <div class="container-fluid">
            <form id="updateForm" action="{{ route('employees.data.update_login', $employee->id) }}" method="POST">
                @csrf
                <div class="form-content-container">
                            <div class="row">
                                <!-- Kolom Kiri -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Login Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" placeholder="Enter login name"
                                            value="{{ old('name', $user->name ?? '') }}" required>
                                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="password">Password (Leave blank if not changing)</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="password" name="password" placeholder="Enter new password">
                                        @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="role">Role <span class="text-danger">*</span></label>
                                        <select class="form-control @error('role') is-invalid @enderror" id="role" name="role"
                                            @if(!in_array(auth()->user()->role, ['superadmin', 'hc'])) disabled @endif required>
                                            <option value="">-- Select Role --</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role }}" {{ (isset($user) && $user->role === $role) ? 'selected' : '' }}>
                                                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email Login <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" placeholder="employee@login.com"
                                            value="{{ old('email', $user->email ?? '') }}"
                                            @if(!in_array(auth()->user()->role, ['superadmin', 'hc'])) readonly @endif required>
                                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm Password</label>
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                            id="password_confirmation" name="password_confirmation" placeholder="Confirm new password">
                                        @error('password_confirmation') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="form-buttons-container">
                                                    {{-- Tombol Reset Password --}}
                                                    @if (isset($user) && auth()->check() && in_array(auth()->user()->role, ['superadmin', 'hc']))
                                                            <button type="button" class="btn btn-warning" onclick="showDeleteModal('reset-password-{{ $employee->id }}')">
                                                                <i class="fas fa-key" style="margin-right: 5px"></i>Reset Password
                                                            </button>
                                        
                                                        <x-delete-modal modalId="reset-password-{{ $employee->id }}" 
                                                                        action="{{ route('employees.reset_password', $employee->id) }}" 
                                                                        message="Reset password to employee’s NIP?" 
                                                                        title="Password Reset Confirmation" 
                                                                        method="POST" 
                                                                        iconClass="fas fa-key" />
                                                    @endif                                        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-cancel">Back</a>
                                        <button type="submit" class="btn btn-submit">Submit</button>
                                    </div>
                                </div>
                            </div>
                </div>
            </form>
        </div>
    </section>
@endsection