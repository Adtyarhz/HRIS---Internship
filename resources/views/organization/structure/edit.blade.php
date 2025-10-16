@extends('layouts.admin')

@section('title', 'Struktur Organisasi')
@section('header_icon', 'fluent--organization-24-regular-01')
@section('content_header', 'Organization Structure')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
@endpush

@section('content-wrapper')
@include('organization.partials.tab-menu')
<section class="content">
<div class="container-fluid">
    <div class="form-content-container">
        <div class="card-body">

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form id="updateForm" action="{{ route('organization.structure.update', $position->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-12">

                        {{-- Division --}}
                        <div class="form-group row align-items-center">
                            <label for="division_id" class="col-md-2 col-form-label">Division <span class="text-danger">*</span> :</label>
                            <div class="col-md-4">
                                <select class="form-control @error('division_id') is-invalid @enderror"
                                    id="division_id" name="division_id" required>
                                    <option value="">Choose Division</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->id }}"
                                            {{ old('division_id', $position->division_id) == $division->id ? 'selected' : '' }}>
                                            {{ $division->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('division_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Nama Jabatan --}}
                        <div class="form-group row align-items-center">
                            <label for="title" class="col-md-2 col-form-label">Position Name <span class="text-danger">*</span> :</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                    id="title" name="title" value="{{ old('title', $position->title) }}"
                                    placeholder="Input Your Position Name" required>
                                @error('title')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Jabatan Atasan --}}
                        <div class="form-group row align-items-center">
                            <label for="parent_id" class="col-md-2 col-form-label">Superior Position :</label>
                            <div class="col-md-4">
                                <select class="form-control @error('parent_id') is-invalid @enderror" id="parent_id"
                                    name="parent_id">
                                    <option value="">Choose Your Superior Position</option>
                                    @foreach ($possibleParents as $parent)
                                        <option value="{{ $parent->id }}"
                                            data-division="{{ $parent->division_id }}"
                                            {{ old('parent_id', $position->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Changing the supervisor will move this position and
                                    all its subordinates.</small>
                                @error('parent_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Pengawas Tidak Langsung --}}
                        <div class="form-group row align-items-center">
                            <label for="indirect_supervisor_id" class="col-md-2 col-form-label">Indirect Supervisor :</label>
                            <div class="col-md-4">
                                <select class="form-control @error('indirect_supervisor_id') is-invalid @enderror"
                                    id="indirect_supervisor_id" name="indirect_supervisor_id">
                                    <option value="">-- Not Specified --</option>
                                    @foreach ($possibleParents as $parent)
                                        <option value="{{ $parent->id }}"
                                            data-division="{{ $parent->division_id }}"
                                            {{ old('indirect_supervisor_id', $position->indirect_supervisor_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('indirect_supervisor_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Kedalaman --}}
                        <div class="form-group row align-items-center">
                            <label for="depth" class="col-md-2 col-form-label">Depth Position :</label>
                            <div class="col-md-4">
                                <input type="number" class="form-control @error('depth') is-invalid @enderror"
                                    id="depth" name="depth" value="{{ old('depth', $position->depth) }}"
                                    placeholder="Input Your Depth Position" min="0">
                                <small class="form-text text-muted">Leave blank to automatically calculate based on
                                    Superior Position.</small>
                                @error('depth')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="form-buttons-container">
                            <button type="button" class="btn btn-delete"
                                onclick="showDeleteModal('position-{{ $position->id }}')">Delete</button>
                            <a href="{{ route('organization.structure.index') }}" class="btn btn-cancel">Cancel</a>
                            <button type="submit" class="btn btn-submit" form="updateForm">Submit</button>
                        </div>
                    </div>
                </div>
            </form>

            <x-delete-modal modalId="position-{{ $position->id }}"
                :action="route('organization.structure.destroy', $position->id)"
                message="Are you sure you want to delete this position?" />
        </div>
    </div>
</div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('division_id').addEventListener('change', function() {
    const selectedDivision = this.value;
    const options = document.querySelectorAll('#parent_id option, #indirect_supervisor_id option');
    options.forEach(opt => {
        if (!opt.value) return;
        opt.hidden = opt.getAttribute('data-division') != selectedDivision;
    });
});

document.getElementById('updateForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    if (btn) {
        btn.disabled = true;
        btn.innerText = 'Saving...';
    }
});
</script>
@endpush
