@extends('layouts.admin')

@section('title', 'Edit Jabatan')
@section('header_icon', 'icon-park-outline--edit')
@section('content_header', 'Edit Jabatan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/form-edit.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('organization.structure.update', $position->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="table-responsive">
                    <table class="table table-bordered table-form">
                        <tbody>
                            <tr>
                                <td class="table-label"><label for="title">Nama Jabatan <span class="text-danger">*</span></label></td>
                                <td>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $position->title) }}" required>
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="table-label"><label for="parent_id">Jabatan Atasan</label></td>
                                <td>
                                    <select class="form-control @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                        <option value="">-- Tidak Ada (Jabatan Puncak) --</option>
                                        @foreach ($possibleParents as $parent)
                                            <option value="{{ $parent->id }}" {{ old('parent_id', $position->parent_id) == $parent->id ? 'selected' : '' }}>
                                                {{ $parent->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Mengubah atasan akan memindahkan posisi ini dan semua bawahannya.</small>
                                    @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="table-label"><label for="indirect_supervisor_id">Pengawas Tidak Langsung</label></td>
                                <td>
                                    <select class="form-control @error('indirect_supervisor_id') is-invalid @enderror" id="indirect_supervisor_id" name="indirect_supervisor_id">
                                        <option value="">-- Tidak Ada --</option>
                                        @foreach ($possibleParents as $parent)
                                            <option value="{{ $parent->id }}" {{ old('indirect_supervisor_id', $position->indirect_supervisor_id) == $parent->id ? 'selected' : '' }}>
                                                {{ $parent->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Pilih jabatan yang mengawasi jabatan ini secara tidak langsung.</small>
                                    @error('indirect_supervisor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="table-label"><label for="depth">Kedalaman (Depth)</label></td>
                                <td>
                                    <input type="number" class="form-control @error('depth') is-invalid @enderror" id="depth" name="depth" value="{{ old('depth', $position->depth) }}" min="0">
                                    <small class="form-text text-muted">Opsional. Kosongkan agar dihitung otomatis berdasarkan Jabatan Atasan.</small>
                                    @error('depth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('organization.structure.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection