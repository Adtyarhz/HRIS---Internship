@extends('layouts.admin')

@section('title', 'Tambah Jabatan Baru')
@section('header_icon', 'icon-park-outline--add')
@section('content_header', 'Tambah Jabatan Baru')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/form-edit.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('organization.structure.store') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered table-form">
                        <tbody>
                            <tr>
                                <td class="table-label"><label for="title">Nama Jabatan <span class="text-danger">*</span></label></td>
                                <td>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="table-label"><label for="parent_id">Jabatan Atasan</label></td>
                                <td>
                                    <select class="form-control @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                        <option value="">-- Tidak Ada (Jabatan Puncak) --</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}" {{ old('parent_id') == $position->id ? 'selected' : '' }}>
                                                {{ $position->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Pilih jabatan yang menjadi atasan langsung dari jabatan baru ini.</small>
                                    @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="table-label"><label for="depth">Kedalaman (Depth)</label></td>
                                <td>
                                    <input type="number" class="form-control @error('depth') is-invalid @enderror" id="depth" name="depth" value="{{ old('depth') }}" min="0">
                                    <small class="form-text text-muted">Opsional. Kosongkan agar dihitung otomatis berdasarkan Jabatan Atasan (0 untuk jabatan puncak).</small>
                                    @error('depth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('organization.structure.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
