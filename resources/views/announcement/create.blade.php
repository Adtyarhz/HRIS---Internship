@extends('layouts.admin')

@section('title', 'Tambah Pengumuman')
@push('styles')
<style>
    .form-control {
        padding: 1px 5px;
        font-size: 15px;
        border: 1px solid #000 !important;
        border-radius: 6px;
        box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.05);
    }

    textarea.form-control {
        resize: vertical;
    }

    label.fw-semibold {
        font-weight: 600;
        font-size: 15px;
    }

    .btn-primary {
        background-color: #000;
        border: none;
    }

    .btn-primary:hover {
        background-color: #333;
    }

    .btn-secondary {
        background-color: #777;
        border: none;
    }

    .btn-secondary:hover {
        background-color: #555;
    }

    .form-group {
        margin-bottom: 1.4rem;
    }
</style>
@endpush

@section('content_header')
    <h1 class="header-title">Create Announcement</h1>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('announcement.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="title" class="fw-semibold">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
            @error('title')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group mt-3">
            <label for="content" class="fw-semibold">Content</label>
            <textarea name="content" id="content" class="form-control" rows="5" required>{{ old('content') }}</textarea>
            @error('content')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group mt-3">
            <label for="announcement_type" class="fw-semibold">Announcement Type</label>
            <select name="announcement_type" id="announcement_type" class="form-control" required>
                <option value="">-- Choose Type --</option>
                @foreach (['Umum', 'Divisi', 'Urgent', 'Informasi', 'Polling'] as $tipe)
                    <option value="{{ $tipe }}" {{ old('announcement_type') == $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                @endforeach
            </select>
            @error('announcement_type')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group mt-3">
            <label for="label" class="fw-semibold">Label</label>
            <input type="text" name="label" id="label" class="form-control" value="{{ old('label') }}" placeholder="Example: HR, IT, Marketing">
            @error('label')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group mt-3">
            <label for="external_link" class="fw-semibold">External Link (optional)</label>
            <input type="url" name="external_link" id="external_link" class="form-control" value="{{ old('external_link') }}" placeholder="https://contoh.com">
            @error('external_link')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- Polling Section --}}
        <div id="polling-section" style="display: none">
            <div class="form-group mt-3">
                <label class="fw-semibold">The Polling Options</label>
                <div id="options-container">
                    <input type="text" name="options[]" class="form-control mb-2" placeholder="Opsi 1">
                </div>
                <button type="button" class="btn btn-sm btn-secondary mb-2" id="add-option">+ Tambah Opsi</button>
                @error('options')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group mt-3">
                <label for="deadline" class="fw-semibold">Deadline</label>
                <input type="datetime-local" name="deadline" id="deadline" class="form-control" value="{{ old('deadline') }}">
                @error('deadline')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="form-group mt-3">
            <label for="attachment_file" class="fw-semibold">Attachment File (PDF/Picture)</label>
            <input type="file" name="attachment_file" class="form-control" accept=".pdf,image/*">
            @error('attachment_file')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary mt-4">Save</button>
        <a href="{{ route('announcement.index') }}" class="btn btn-secondary mt-4">Back</a>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('announcement_type');
            const pollingSection = document.getElementById('polling-section');
            const addOptionBtn = document.getElementById('add-option');
            const optionsContainer = document.getElementById('options-container');

            // Tampilkan polling jika terpilih
            function togglePollingSection() {
                pollingSection.style.display = typeSelect.value === 'Polling' ? 'block' : 'none';
            }

            togglePollingSection(); // panggil awal

            typeSelect.addEventListener('change', togglePollingSection);

            if (addOptionBtn) {
                addOptionBtn.addEventListener('click', function () {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'options[]';
                    input.className = 'form-control mb-2';
                    input.placeholder = 'Opsi tambahan';
                    optionsContainer.appendChild(input);
                });
            }
        });
    </script>
@endsection
