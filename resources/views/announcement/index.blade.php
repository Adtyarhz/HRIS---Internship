@extends('adminlte::page')

@section('title', 'Daftar Pengumuman')

@section('content_header')
    <h1>Daftar Pengumuman</h1>
@stop

@section('content')
    <a href="{{ route('announcement.create') }}" class="btn btn-primary mb-3">Tambah Pengumuman</a>

    {{-- Filter --}}
<form method="GET" class="mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label for="search" class="form-label">Cari Judul</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Cari judul..." value="{{ request('search') }}">
        </div>

        <div class="col-md-3">
            <label for="type" class="form-label">Tipe Pengumuman</label>
            <select name="type" id="type" class="form-select">
                <option value="">Semua Tipe</option>
                @php
                    $types = ['Umum' => '📄 Umum', 'Divisi' => '🏢 Divisi', 'Urgent' => '📢 Urgent', 'Informasi' => 'ℹ️ Informasi', 'Polling' => '📊 Polling'];
                @endphp
                @foreach ($types as $key => $label)
                    <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label for="label" class="form-label">Label (HR, IT, dll)</label>
            <input type="text" name="label" id="label" class="form-control" value="{{ request('label') }}">
        </div>

        <div class="col-md-2">
            <button class="btn btn-dark w-100">Filter</button>
        </div>
    </div>
</form>


    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Tipe</th>
                <th>Label</th>
                <th>Lampiran</th>
                <th>Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($announcements as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ ucfirst($item->announcement_type) }}</td>
                    <td>
                        @if($item->label)
                            <span class="badge bg-info text-white">{{ $item->label }}</span>
                        @else
                            <span class="text-muted">Umum</span>
                        @endif
                    </td>
                    <td>
                        @if ($item->attachment_file)
                            @php
                                $ext = strtolower(pathinfo($item->attachment_file, PATHINFO_EXTENSION));
                            @endphp
                            @if (in_array($ext, ['jpg', 'jpeg', 'png']))
                                <img src="{{ asset('storage/announcement/' . $item->attachment_file) }}" alt="gambar" width="60">
                            @elseif ($ext === 'pdf')
                                <a href="{{ asset('storage/announcement/' . $item->attachment_file) }}" target="_blank">Lihat PDF</a>
                            @else
                                <a href="{{ asset('storage/announcement/' . $item->attachment_file) }}" target="_blank">Unduh File</a>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $item->created_at->translatedFormat('d M Y H:i') }}</td>
                    <td>
                        <a href="{{ route('announcement.show', $item->id) }}" class="btn btn-info btn-sm">Detail</a>
                        <a href="{{ route('announcement.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('announcement.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Belum ada pengumuman</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center">
        {{ $announcements->withQueryString()->links() }}
    </div>
@stop
