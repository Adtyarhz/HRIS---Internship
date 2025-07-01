@extends('layouts.app')

@section('title', 'Detail Pengumuman')

@section('content_header')
    <h1 class="header-title">Detail Pengumuman</h1>
@endsection

@section('content')
    @php
        $polling = $announcement->polling;
        $isPolling = strtolower($announcement->announcement_type) === 'polling';
        $isExpired = $isPolling && $polling && $polling->deadline && now()->gt($polling->deadline);
    @endphp

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card" style="background: #FFFEF9; border-radius: 10px; border: 1px solid rgba(0, 0, 0, 0.20); padding: 20px;">
        <h2>{{ $announcement->title }}</h2>
        <p><strong>Type:</strong> {{ $announcement->announcement_type }}</p>
        <p><strong>Content:</strong> {{ $announcement->content }}</p>

        @if ($announcement->attachment_file)
            <p><strong>Attachment:</strong>
                <a href="{{ asset('storage/announcement/' . $announcement->attachment_file) }}" target="_blank">
                    Look at the File
                </a>
            </p>
        @endif

        @if ($announcement->label)
            <p><strong>Label:</strong> {{ $announcement->label }}</p>
        @endif

        @if ($announcement->external_link)
            <p><strong>External Link:</strong>
                <a href="{{ $announcement->external_link }}" target="_blank">{{ $announcement->external_link }}</a>
            </p>
        @endif

        {{-- Bagian Polling --}}
        @if ($isPolling && $polling)
            <p><strong>Deadline:</strong> {{ \Carbon\Carbon::parse($polling->deadline)->format('d-m-Y H:i') }}</p>

            @if (!$isExpired)
                <form action="{{ route('polling.vote', $polling->id) }}" method="POST">
                    @csrf
                    @foreach ($polling->options as $option)
                        <div>
                            <input type="radio" name="option_id" value="{{ $option->id }}" required>
                            <label>{{ $option->option_text }} (Votes: {{ $option->votes->count() }})</label>
                        </div>
                    @endforeach
                    <button type="submit" class="btn btn-primary mt-2">Vote</button>
                </form>
            @else
                <p>The poll has expired.</p>

                {{-- Tombol Download Hasil Polling --}}
                <a href="{{ route('announcement.export_polling', $announcement->id) }}" class="btn btn-success mt-3">
                    Download Poll Result (.csv)
                </a>
            @endif
        @endif

        <div class="actions" style="margin-top: 20px;">
            <a href="{{ route('announcement.edit', $announcement->id) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('announcement.destroy', $announcement->id) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
            </form>
        </div>
    </div>
@endsection
