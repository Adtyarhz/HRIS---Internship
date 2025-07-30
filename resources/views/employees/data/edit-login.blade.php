@extends('layouts.admin')

@section('content')
    <h2>Edit Login Account</h2>

    <form action="{{ route('employees.data.update_login', $employee->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name">Login Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="email">Email Login</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
        </div>

        <div class="mb-3">
    <label for="role">Role</label>
    <select name="role" class="form-control" required>
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

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection
