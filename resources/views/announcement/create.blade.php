@extends('adminlte::page')
@section('active_menu', 'pengumuman')

@section('title', 'Tambah Pengumuman')

@section('content_header')
    <h1>Tambah Pengumuman</h1>
@stop

@section('content')
    <form method="POST" action="{{ route('announcement.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>Judul</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="form-group mt-3">
            <label>Isi Pengumuman</label>
            <textarea name="content" class="form-control" rows="4" required></textarea>
        </div>

        <div class="form-group mt-3">
            <label>Tipe Pengumuman</label>
            <select name="announcement_type" class="form-control" required>
                <option value="Umum">Umum</option>
                <option value="Divisi">Divisi</option>
                <option value="Urgent">Urgent</option>
                <option value="Informasi">Informasi</option>
                <option value="Polling">Polling</option>
            </select>
        </div>

        <div class="form-group mt-3">
            <label>Label (opsional)</label>
            <input type="text" name="label" class="form-control" placeholder="Contoh: HR, IT, Marketing">
        </div>

        <div class="form-group mt-3">
            <label>Link Eksternal (opsional)</label>
            <input type="url" name="external_link" class="form-control" placeholder="https://contoh.com">
        </div>

        <div class="form-group mt-3">
            <label>File Lampiran (PDF/Gambar)</label>
            <input type="file" name="attachment_file" class="form-control" accept=".pdf,image/*">
        </div>

        {{-- Bagian untuk polling --}}
        <div id="polling-options" style="display: none;" class="mt-3">
            <div class="form-group">
                <label>Opsi Polling</label>
                <div id="options-container">
                    <input type="text" name="options[]" class="form-control mb-2" placeholder="Opsi 1">
                </div>
                <button type="button" class="btn btn-sm btn-primary mb-2" id="add-option">Tambah Opsi</button>
            </div>

            <div class="form-group">
                <label>Batas Waktu Pengisian Polling</label>
                <input type="datetime-local" name="deadline" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-success mt-3">Simpan</button>
        <a href="{{ route('announcement.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </form>

    {{-- Script --}}
    <script>
        const typeSelect = document.querySelector('[name="announcement_type"]');
        const pollingOptions = document.getElementById('polling-options');

        typeSelect.addEventListener('change', function () {
            pollingOptions.style.display = this.value === 'Polling' ? 'block' : 'none';
        });

        document.getElementById('add-option').addEventListener('click', function () {
            const container = document.getElementById('options-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'options[]';
            input.classList.add('form-control', 'mb-2');
            input.placeholder = 'Opsi tambahan';
            container.appendChild(input);
        });
    </script>
@stop
