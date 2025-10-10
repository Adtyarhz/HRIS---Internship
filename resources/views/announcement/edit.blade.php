@extends('layouts.admin')

@section('title', 'Edit Pengumuman')

@push('styles')
<style>
    .header-with-icon {
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 5px;
    }

    .header-with-icon .custom-hamburger {
        margin-right: 6px;
        width: 35px;
        height: 35px;
        color: #000;
    }

    body {
        background-color: #9A3B3B;
    }

    .form-wrapper {
        display: flex;
        justify-content: center;
        padding: 20px;
        margin-top: 20px;
    }

    .form-container {
        width: 100%;
        max-width: 1133px;
        background: #FFFEF9;
        border-radius: 10px;
        outline: 1px rgba(0, 0, 0, 0.20) solid;
        outline-offset: -1px;
        padding: 40px;
        position: relative;
        box-sizing: border-box;
    }

    label.fw-semibold {
        font-weight: 600;
        font-size: 18px;
        color: black;
        font-family: 'Noto Sans Georgian', sans-serif;
        margin-bottom: 5px;
        display: block;
    }

    .form-control {
        background: white;
        border-radius: 5px;
        font-family: 'Noto Sans Georgian', sans-serif;
        color: black;
        border: 1px rgba(0, 0, 0, 0.20) solid;
        font-size: 15px;
        padding: 5px 10px;
        width: 100%;
        box-sizing: border-box;
        height: 36px;
    }

    textarea.form-control {
        height: 120px;
        resize: vertical;
    }

    .form-group-flex {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        gap: 10px;
    }

    .form-group-flex label {
        width: 160px;
        flex-shrink: 0;
    }

    .form-group-flex .form-control,
    .form-group-flex .type-select {
        flex: 1;
    }

    .form-small .form-control {
        width: 180px;
    }

    .type-select {
        position: relative;
        width: 100%;
    }

    .type-select select {
        width: 100%;
        height: 36px;
        appearance: none;
        padding-right: 20px;
        border: 1px rgba(0, 0, 0, 0.20) solid;
        border-radius: 5px;
        background: white;
        font-family: 'Noto Sans Georgian', sans-serif;
        color: black;
        font-size: 15px;
    }

    .type-select::after {
        content: '';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid rgba(0, 0, 0, 0.60);
    }

    .attachment-section {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .attachment-section i {
        font-size: 18px;
    }

    .attachment-section a {
        color: #000;
        font-weight: 500;
    }

    .attachment-section .change-file {
        color: #3F7D9D;
        font-size: 14px;
        cursor: pointer;
    }

    .attachment-input input {
        padding-left: 10px;
        background: #F6F3F3;
        border: 1px rgba(0, 0, 0, 0.10) solid;
        border-radius: 5px;
    }

    .custom-upload-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 5px;
        background-color: #fff;
        padding: 5px 10px 5px 35px;
        height: 36px;
        width: 180px;
        cursor: pointer;
        overflow: hidden;
        font-size: 14px;
        box-sizing: border-box;
    }

    .custom-upload-wrapper input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        height: 100%;
        width: 100%;
        cursor: pointer;
        z-index: 2;
    }

    .custom-upload-wrapper .file-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #555;
        z-index: 1;
    }

    .custom-upload-wrapper .file-label {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        z-index: 1;
        color: #333;
    }

    .polling-group {
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 12px;
        margin-top: 10px;
        width: 50%;
    }

    .polling-option {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 30px;
    }

    .btn-submit,
    .btn-cancel {
        width: 100px;
        height: 36px;
        border-radius: 5px;
        font-size: 16px;
        font-family: 'Noto Sans Georgian', sans-serif;
        font-weight: 500;
        border: none;
    }

    .btn-submit {
        background-color: #367FA9;
        color: white;
    }

    .btn-cancel {
        background-color: #9A3B3B;
        color: white;
    }

    @media (max-width: 768px) {
        .form-container {
            padding: 20px;
        }

        label.fw-semibold {
            font-size: 16px;
        }

        .btn-submit,
        .btn-cancel {
            width: 100%;
        }

        .buttons {
            flex-direction: column;
            align-items: stretch;
        }

        .polling-group {
            width: 100%;
        }
    }
</style>
@endpush

@section('content_header')
<div class="header-with-icon">
    <svg class="custom-hamburger" width="24" height="24" fill="currentColor" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
    </svg>
    Announcement Management
</div>
@endsection
@php
    $polling = $announcement->polling;
    $hasVotes = $polling?->options->sum(fn($opt) => $opt->votes->count()) > 0;
    $isExpired = $polling?->deadline && now()->gt($polling->deadline);
    $disablePollingEdit = $polling && ($polling->is_locked || $hasVotes || $isExpired);
@endphp

@section('content')
<div class="form-wrapper">
    <div class="form-container">
        <form action="{{ route('announcement.update', $announcement->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Voting Deadline tetap di atas form --}}
            @if($announcement->announcement_type === 'Polling')
            <div style="width: 100%; margin-bottom: 20px;">
                <div style="display: flex; justify-content: flex-end; align-items: center;">
                    @if($disablePollingEdit)
                        <div style="color: #C70000; font-size: 15px; font-weight: 500; display: flex; align-items: center; gap: 6px; margin-right: auto;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 16px; color: #FFC107;"></i>
                            Polling Has Ended
                        </div>
                    @endif
                    <div style="display: flex; align-items: center;">
                        <label for="deadline" class="fw-semibold" style="margin-right: 8px; font-size:15px;">Voting Deadline:</label>
                        <input type="datetime-local" name="deadline" id="deadline"
                               value="{{ old('deadline', optional($polling?->deadline)->format('Y-m-d\TH:i')) }}"
                               style="padding: 6px 10px; font-size: 14px; border: 1px solid #ccc; border-radius: 5px; height: 36px; width: auto;"
                               {{ $disablePollingEdit ? 'readonly' : '' }}>
                    </div>
                </div>
            </div>
            @endif

            <div class="form-group-flex">
                <label for="title" class="fw-semibold">Title:</label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
            </div>

            <div class="form-group-flex form-small">
                <label for="announcement_type" class="fw-semibold">Type:</label>
                <div class="type-select">
                    <select name="announcement_type" id="announcement_type" class="form-control" disabled>
                        @foreach (['Umum', 'Urgent', 'Informasi', 'Polling'] as $tipe)
                            <option value="{{ $tipe }}" {{ $announcement->announcement_type === $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="announcement_type" value="{{ $announcement->announcement_type }}">
                </div>
            </div>

            <div class="form-group-flex">
                <label for="content" class="fw-semibold">Field:</label>
                <textarea name="content" id="content" class="form-control" required>{{ old('content', $announcement->content) }}</textarea>
            </div>

            <div class="form-group-flex form-small">
                <label for="attachment_file" class="fw-semibold">Attachment:</label>
                <div class="attachment-section">
                    @if ($announcement->attachment_file)
                        <span class="iconify" data-icon="famicons:document-outline" data-width="20" data-height="20"></span>
                        <a href="{{ asset('storage/announcement/' . $announcement->attachment_file) }}" target="_blank">
                            {{ $announcement->attachment_file }}
                        </a>
                    @endif
                    <span class="change-file" onclick="document.getElementById('hidden-file-input').click()">change file</span>
                    <input type="file" id="hidden-file-input" name="attachment_file" accept=".pdf,image/*" style="display: none;">
                </div>
            </div>

            <div class="form-group-flex form-small">
                <label for="label" class="fw-semibold">Label:</label>
                <div class="type-select">
                    <input type="text" name="label" id="label" class="form-control" value="{{ old('label', $announcement->label) }}">
                </div>
            </div>

            <div class="form-group-flex">
                <label for="external_link" class="fw-semibold">External Link:</label>
                <input type="url" name="external_link" id="external_link" class="form-control" value="{{ old('external_link', $announcement->external_link) }}">
            </div>

            @if($announcement->announcement_type === 'Polling' && $polling)
            <div class="form-group-flex">
                <label class="fw-semibold">Polling Options:</label>
                <div class="polling-group">
                    <div id="polling-options">
                        @foreach ($announcement->polling->options as $option)
                        <div class="polling-option">
                            <input type="radio" disabled>
                            <input type="text" name="existing_options[{{ $option->id }}]" value="{{ $option->option_text }}" class="form-control">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this, {{ $option->id }})">×</button>
                        </div>
                        @endforeach
                    </div>
                    <div id="new-polling-options"></div>
                    <div style="margin-top: 10px;">
                        <button type="button" class="btn btn-success btn-sm" id="add-option">+ Add Option</button>
                    </div>
                </div>
            </div>
            @endif

            <div id="deleted-options-container"></div>
            <div class="buttons">
                <a href="{{ route('announcement.index') }}" class="btn-cancel btn">Cancel</a>
                <button type="submit" class="btn-submit btn">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    function removeOption(button, optionId = null) {
        const parent = button.parentElement;
        parent.remove();

        if (optionId) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'deleted_options[]';
            input.value = optionId;
            document.getElementById('deleted-options-container').appendChild(input);
        }
    }

    document.getElementById('add-option')?.addEventListener('click', function () {
        const wrapper = document.createElement('div');
        wrapper.className = 'polling-option';
        wrapper.innerHTML = `
            <input type="radio" disabled>
            <input type="text" name="options[]" class="form-control" placeholder="Add Option">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">×</button>
        `;
        document.getElementById('new-polling-options').appendChild(wrapper);
    });

    document.getElementById('hidden-file-input')?.addEventListener('change', function () {
        const fileName = this.files[0]?.name;
        if (fileName) {
            const label = document.querySelector('.attachment-section a');
            if (label) label.textContent = fileName;
        }
    });
</script>
@stack('scripts')
@endsection