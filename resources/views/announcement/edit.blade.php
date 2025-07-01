@extends('layouts.app')

@section('title', 'Edit Pengumuman')

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
    <h1 class="header-title">Edit Pengumuman</h1>
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

    @php
        $polling = $announcement->polling;
        $isPolling = $announcement->announcement_type === 'Polling';
        $isExpired = $isPolling && $polling && $polling->deadline && now()->gt($polling->deadline);
        $hasVotes = $isPolling && $polling && $polling->options->sum(fn($opt) => $opt->votes->count()) > 0;
        $disablePollingEdit = $isExpired || $hasVotes;
    @endphp

    <form action="{{ route('announcement.update', $announcement->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="title" class="fw-semibold">Title</label>
            <input type="text" name="title" id="title" class="form-control"
                   value="{{ old('title', $announcement->title) }}" required>
            @error('title') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="form-group mt-3">
            <label for="content" class="fw-semibold">Content</label>
            <textarea name="content" id="content" class="form-control" rows="5" required>{{ old('content', $announcement->content) }}</textarea>
            @error('content') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="form-group mt-3">
            <label for="announcement_type" class="fw-semibold">Announcement Type</label>
            <select name="announcement_type" id="announcement_type" class="form-control" disabled>
                @foreach (['Umum', 'Divisi', 'Urgent', 'Informasi', 'Polling'] as $tipe)
                    <option value="{{ $tipe }}" {{ $announcement->announcement_type === $tipe ? 'selected' : '' }}>
                        {{ $tipe }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="announcement_type" value="{{ $announcement->announcement_type }}">
        </div>

        <div class="form-group mt-3">
            <label for="label" class="fw-semibold">Label</label>
            <input type="text" name="label" id="label" class="form-control"
                   value="{{ old('label', $announcement->label) }}">
            @error('label') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="form-group mt-3">
            <label for="external_link" class="fw-semibold">External Link (Optional)</label>
            <input type="url" name="external_link" id="external_link" class="form-control"
                   value="{{ old('external_link', $announcement->external_link) }}"
                   placeholder="https://example.com">
            @error('external_link') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        @if ($isPolling && $polling)
            <div class="form-group mt-3">
                <label for="deadline" class="fw-semibold">Deadline</label>
                @if ($disablePollingEdit)
                    <input type="datetime-local" class="form-control" value="{{ $polling->deadline->format('Y-m-d\TH:i') }}" readonly>
                    <input type="hidden" name="deadline" value="{{ $polling->deadline->format('Y-m-d\TH:i') }}">
                    <small class="text-danger">Batas waktu tidak bisa diubah karena polling sudah kadaluarsa atau memiliki suara.</small>
                @else
                    <input type="datetime-local" name="deadline" id="deadline" class="form-control"
                           value="{{ old('deadline', $polling->deadline ? $polling->deadline->format('Y-m-d\TH:i') : '') }}">
                @endif

                @if ($errors->has('deadline'))
    <small class="text-danger">{{ $errors->first('deadline') }}</small>
@endif
            </div>

            <div class="form-group mt-3">
                <label class="fw-semibold">The Polling Options</label>
                @forelse ($polling->options as $option)
                    <div class="input-group mb-2">
                        <input type="text" name="existing_options[{{ $option->id }}]"
                               value="{{ old('existing_options.' . $option->id, $option->option_text) }}"
                               class="form-control" {{ $disablePollingEdit ? 'readonly' : '' }}>
                        @if (!$disablePollingEdit)
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <input type="checkbox" name="delete_options[]" value="{{ $option->id }}">
                                    <span class="ms-1">Hapus</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p>No polling options available.</p>
                @endforelse

                @if (!$disablePollingEdit)
                    <div id="polling-options">
                        <input type="text" name="options[]" class="form-control mb-2" placeholder="Tambah opsi baru">
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mb-2" id="add-option">+ Add Option</button>
                @endif
            </div>
        @endif

        <div class="form-group mt-3">
            <label for="attachment_file" class="fw-semibold">Change the Attachment (PDF/Picture)</label>
            <input type="file" name="attachment_file" id="attachment_file" class="form-control" accept=".pdf,image/*">
            @if ($announcement->attachment_file)
                <p class="mt-2">Current Attachment:
                    <a href="{{ asset('storage/announcement/' . $announcement->attachment_file) }}" target="_blank">Look at the File</a>
                </p>
            @endif
            @error('attachment_file') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-primary mt-4">Save Changes</button>
        <a href="{{ route('announcement.index') }}" class="btn btn-secondary mt-4">Back</a>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addOptionBtn = document.getElementById('add-option');
            const pollingOptions = document.getElementById('polling-options');

            if (addOptionBtn && pollingOptions) {
                addOptionBtn.addEventListener('click', function () {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'options[]';
                    input.className = 'form-control mb-2';
                    input.placeholder = 'Tambah opsi baru';
                    pollingOptions.appendChild(input);
                });
            }
        });
    </script>
@endsection
