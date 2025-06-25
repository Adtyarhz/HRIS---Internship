@extends('adminlte::page')
@section('active_menu', 'pengumuman')

@section('title', 'Edit Pengumuman')

@section('content_header')
    <h1>Edit Pengumuman</h1>
@stop

@section('content')
    @php
        $polling = $announcement->polling;
        $isPolling = $announcement->announcement_type === 'Polling';
        $isExpired = $isPolling && $polling && $polling->deadline && now()->gt($polling->deadline);
        $hasVotes = $isPolling && $polling && $polling->options->sum(fn($opt) => $opt->votes->count()) > 0;
        $disablePollingEdit = $isExpired || $hasVotes;
    @endphp

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('announcement.update', $announcement->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="title">Judul</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
        </div>

        <div class="form-group mt-3">
            <label for="content">Isi Pengumuman</label>
            <textarea name="content" id="content" class="form-control" rows="5" required>{{ old('content', $announcement->content) }}</textarea>
        </div>

        <div class="form-group mt-3">
            <label for="announcement_type">Tipe</label>
            <select name="announcement_type" id="announcement_type" class="form-control" required disabled>
                @foreach (['Umum', 'Divisi', 'Urgent', 'Informasi', 'Polling'] as $tipe)
                    <option value="{{ $tipe }}" {{ $announcement->announcement_type === $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                @endforeach
            </select>
            <input type="hidden" name="announcement_type" value="{{ $announcement->announcement_type }}">
        </div>

        <div class="form-group mt-3">
            <label for="label">Label</label>
            <input type="text" name="label" id="label" class="form-control" value="{{ old('label', $announcement->label) }}">
        </div>

        <div class="form-group mt-3">
            <label for="external_link">Link Eksternal (opsional)</label>
            <input type="url" name="external_link" id="external_link" class="form-control"
                value="{{ old('external_link', $announcement->external_link) }}"
                placeholder="https://contoh.com">
        </div>

        @if ($isPolling)
            <div class="form-group mt-3">
                <label for="deadline">Batas Waktu Polling</label>
                <input type="datetime-local" name="batas_waktu" id="deadline" class="form-control"
                       value="{{ old('batas_waktu', optional($polling->deadline)->format('Y-m-d\TH:i')) }}"
                       {{ $disablePollingEdit ? 'disabled' : '' }}>
                @if ($disablePollingEdit)
                    <small class="text-danger">Batas waktu tidak bisa diubah karena polling sudah kadaluarsa atau memiliki suara.</small>
                @endif
            </div>

            <div class="form-group mt-3">
                <label>Opsi Polling</label>
                @foreach ($polling->options as $option)
                    <div class="input-group mb-2">
                        <input type="text" name="existing_options[{{ $option->id }}]" value="{{ $option->option_text }}"
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
                @endforeach

                @if (!$disablePollingEdit)
                    <div id="polling-options">
                        <input type="text" name="options[]" class="form-control mb-2" placeholder="Tambah opsi baru">
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mb-2" onclick="addOption()">+ Tambah Opsi</button>
                @endif

                @if ($disablePollingEdit)
                    <small class="text-muted">Opsi tidak dapat diubah karena polling sudah berakhir atau memiliki suara.</small>
                @endif
            </div>
        @endif

        <div class="form-group mt-3">
            <label for="attachment_file">Ubah Lampiran (PDF/Gambar)</label>
            <input type="file" name="attachment_file" class="form-control" accept=".pdf,image/*">
            @if ($announcement
            ->attachment_file)
                <p class="mt-2">Lampiran saat ini:
                    <a href="{{ asset('storage/announcement/' . $announcement->attachment_file) }}" target="_blank">Lihat File</a>
                </p>
            @endif
        </div>

        <button type="submit" class="btn btn-primary mt-4">Simpan Perubahan</button>
        <a href="{{ route('announcement.index') }}" class="btn btn-secondary mt-4">Batal</a>
    </form>

    <script>
        function addOption() {
            const container = document.getElementById('polling-options');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'options[]';
            input.className = 'form-control mb-2';
            input.placeholder = 'Tambah opsi baru';
            container.appendChild(input);
        }
    </script>
@stop
