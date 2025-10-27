@extends('layouts.admin')
@section('title', 'Edit Data Karyawan')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Edit Data Karyawan')
@section('content')

    <h2>Edit Login Account</h2>

    {{-- Notifikasi sukses / error --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('employees.data.update_login', $employee->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name">Login Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
        </div>

       <div class="mb-3">
            <label for="email">Email Login</label>
            <input type="email" name="email" class="form-control"
                value="{{ old('email', $user->email ?? '') }}"
                @if(!in_array(auth()->user()->role, ['superadmin', 'hc'])) readonly @endif required>
        </div>

        <div class="mb-3">
            <label for="role">Role</label>
            <select name="role" class="form-control"
                    @if(!in_array(auth()->user()->role, ['superadmin', 'hc'])) disabled @endif required>
                <option value="">-- Pilih Role --</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" {{ (isset($user) && $user->role === $role) ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $role)) }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-3">
            <label for="password">Password (Kosongkan jika tidak diganti)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-secondary">Batal</a>
</div>
</form>

     {{-- Tombol Reset Password --}}
@if (auth()->check() && in_array(auth()->user()->role, ['superadmin', 'hc']))
    <form action="{{ route('employees.reset_password', $employee->id) }}" method="POST" class="mt-4" id="resetPasswordForm">
        @csrf
        <button type="button" class="btn btn-warning" id="resetPasswordBtn">
            <i class="fas fa-key"></i> Reset Password
        </button>

        <!-- Teks konfirmasi default password -->
        <p class="text-muted mt-2" id="defaultPasswordText" style="display: none;">
            Reset password to employee’s <strong>NIP</strong>?
            <button type="submit" class="btn btn-sm btn-danger ms-2">Ya, Reset</button>
            <button type="button" class="btn btn-sm btn-secondary ms-1" id="cancelReset">Batal</button>
        </p>
    </form>

    @push('scripts')
    <script>
        const btn = document.getElementById('resetPasswordBtn');
        const text = document.getElementById('defaultPasswordText');
        const cancelBtn = document.getElementById('cancelReset');

        btn.addEventListener('click', () => {
            text.style.display = text.style.display === 'none' ? 'block' : 'none';
        });

        cancelBtn.addEventListener('click', () => {
            text.style.display = 'none';
        });
    </script>
    @endpush
@endif

@endsection
