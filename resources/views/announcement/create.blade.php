@extends('layouts.admin')

@section('title', 'Tambah Pengumuman')
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
        margin: 0;
        padding: 0;
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

    .type-select {
        position: relative;
        width: fit-content;
        display: inline-block;
    }

    .type-select select {
        width: 100%;
        height: 36px;
        appearance: none;
        padding-right: 20px;
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

    .attachment-input input {
        padding-left: 10px;
        background: #F6F3F3;
        border: 1px rgba(0, 0, 0, 0.10) solid;
        border-radius: 5px;
    }

    .buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 30px;
    }

    .btn-submit, .btn-cancel {
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

        .btn-submit, .btn-cancel {
            width: 100%;
        }

        .buttons {
            flex-direction: column;
            align-items: stretch;
        }
    }
    .polling-group {
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 12px;
        margin-top: 10px;
    }
    .polling-option {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }
    .form-group-flex {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
    gap: 10px;
}

.form-group-flex label {
    width: 160px; /* Tentukan panjang label */
    flex-shrink: 0;
}

.form-group-flex .form-control
.form-group-flex .type-select {
    flex: 1; /* Input akan isi sisa ruang */
}
.form-small .form-control {
    width: 180px; /* atau ukuran lain yang kamu mau */
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

@section('content')
<div class="form-wrapper">
    <div class="form-container">
        <form action="{{ route('announcement.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- ✅ Voting Deadline Header --}}
            <div id="deadline-wrapper" style="display: none; width: 100%; margin-bottom: 20px;">
    <div style="display: flex; justify-content: flex-end; align-items: center;">
        <label for="deadline" style="font-weight: 600; font-size: 15px; margin-right: 8px;">Voting Deadline:</label>
        <input type="datetime-local" name="deadline" id="deadline" value="{{ old('deadline') }}"
            style="padding: 6px 10px; font-size: 14px; border: 1px solid #ccc; border-radius: 5px; height: 36px; width: auto;">
    </div>
</div>

            <div class="form-group-flex">
            <label for="title" class="fw-semibold">Title <span style="color: red">*</span></label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
            </div>

            <div class="form-group-flex form-small">
            <label for="announcement_type" class="fw-semibold">Attachment Type <span style="color: red">*</span></label>
                <div class="type-select">
                    <select name="announcement_type" id="announcement_type" class="form-control" required>
                        <option value="">Not Specified</option>
                        @foreach (['Umum', 'Urgent', 'Informasi', 'Polling'] as $tipe)
                            <option value="{{ $tipe }}" {{ old('announcement_type') == $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group-flex" style="position: relative;">
            <label for="content" class="fw-semibold">Field <span style="color: red">*</span></label>
                <textarea name="content" id="content" class="form-control" required>{{ old('content') }}</textarea>
            </div>

            <div class="form-group-flex form-small">
            <label for="attachment_file" class="fw-semibold">Attachment</label>
            <div class="custom-upload-wrapper">
                <span class="iconify file-icon" data-icon="famicons:document-outline" data-width="20" data-height="20"></span>
                <span class="file-label">Choose file</span>
                <input type="file" name="attachment_file" id="attachment_file" accept=".pdf,image/*" onchange="updateFileName(this)">
            </div>
        </div>

            <div class="form-group-flex form-small">
            <label for="label" class="fw-semibold">Label <span style="color: red">*</span></label>
                <input type="text" name="label" id="label" class="form-control" value="{{ old('label') }}" placeholder="Example: HR, IT, Marketing">
            </div>
            <div class="form-group-flex">
            <label for="target_divisions" class="fw-semibold">Tujuan Divisi </label>
            <div style="flex: 1;">
                <select name="target_divisions[]" id="target_divisions" class="form-control" multiple>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}"
                            {{ (collect(old('target_divisions'))->contains($division->id)) ? 'selected' : '' }}>
                            {{ $division->name }}
                        </option>
                    @endforeach
                </select>
                <small style="color: #555;">(Tekan Ctrl / Cmd untuk memilih lebih dari satu divisi)</small>
            </div>
        </div>

            <div class="form-group-flex">
    <label for="external_link" class="fw-semibold">External Link</label>

    <div id="external-links-container" style="flex: 1;">
        {{-- Input pertama --}}
        <div class="d-flex mb-2 external-link-item">
            <input type="url" name="external_link[]" class="form-control" placeholder="https://contoh.com" required>
            <button type="button" class="btn btn-danger btn-sm ms-2 remove-link" style="display:none;">Hapus</button>
        </div>
    </div>

    <button type="button" id="add-link" class="btn btn-primary btn-sm mt-2">+ Tambah Link</button>

    <small class="text-muted d-block mt-1">Kamu dapat menambahkan lebih dari satu link eksternal.</small>
</div>

            <div id="polling-section" style="display: none;">
    <div class="form-group-flex">
        <label class="fw-semibold">Polling Options:</label>
        <div class="polling-group" style="width: 50%;">
            <div id="polling-options">
                <div class="polling-option">
                    <input type="radio" disabled>
                    <input type="text" name="options[]" class="form-control" placeholder="Add Option">
                    <button type="button" onclick="removeOption(this)">×</button>
                </div>
            </div>
            {{-- Opsi polling baru akan ditambahkan di sini --}}
            <div id="new-polling-options"></div>

            {{-- Tombol tambah --}}
            <div style="margin-top: 10px;">
            <button type="button" class="btn btn-success btn-sm" id="add-option">+ Add Option</button>
            </div>
        </div>
    </div>
</div>

            <div class="buttons">
                <a href="{{ route('announcement.index') }}" class="btn-cancel btn">Cancel</a>
                <button type="submit" class="btn-submit btn">Add</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('announcement_type');
        const pollingSection = document.getElementById('polling-section');
        const addOptionBtn = document.getElementById('add-option');
        const optionsContainer = document.getElementById('options-container');
        const deadlineWrapper = document.getElementById('deadline-wrapper');

        function togglePollingSection() {
            const isPolling = typeSelect.value === 'Polling';
            pollingSection.style.display = isPolling ? 'block' : 'none';
            deadlineWrapper.style.display = isPolling ? 'block' : 'none';
        }

        togglePollingSection();
        typeSelect.addEventListener('change', togglePollingSection);
    });
    function removeOption(button) {
    const parent = button.parentElement;
    parent.remove();
}
function updateFileName(input) {
    const label = input.previousElementSibling;
    const fileName = input.files.length ? input.files[0].name : 'Choose file';
    label.textContent = fileName;
}
document.getElementById('add-option')?.addEventListener('click', function () {
    const wrapper = document.createElement('div');
    wrapper.className = 'polling-option';
    wrapper.innerHTML = `
        <input type="radio" disabled>
        <input type="text" name="options[]" class="form-control" placeholder="Add Option">
        <button type="button" onclick="removeOption(this)">×</button>
    `;
    document.getElementById('polling-options').appendChild(wrapper);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#target_divisions').select2({
            placeholder: "Pilih satu atau lebih divisi",
            allowClear: true,
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('external-links-container');
    const addBtn = document.getElementById('add-link');

    addBtn.addEventListener('click', function() {
        const newInput = document.createElement('div');
        newInput.classList.add('d-flex', 'mb-2', 'external-link-item');
        newInput.innerHTML = `
            <input type="url" name="external_link[]" class="form-control" placeholder="https://contoh.com" required>
            <button type="button" class="btn btn-danger btn-sm ms-2 remove-link">Hapus</button>
        `;
        container.appendChild(newInput);
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-link')) {
            e.target.closest('.external-link-item').remove();
        }
    });
});
</script>
@endsection