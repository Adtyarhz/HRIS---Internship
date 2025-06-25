@extends('adminlte::page')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
    use Carbon\Carbon;
@endphp

@section('active_menu', 'pengumuman')
@section('title', 'Detail Pengumuman')

@section('content_header')
    <h1>Detail Pengumuman</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        {{ $announcement->title }} ({{ ucfirst($announcement->announcement_type) }})
    </div>

    <div class="mb-2">
        <strong>Label:</strong>
        @if($announcement->label)
            <span class="badge bg-info text-white">{{ $announcement->label }}</span>
        @else
            <span class="text-muted">Umum</span>
        @endif
    </div>

    <div class="card-body">
        {!! nl2br(e($announcement->content)) !!}

        {{-- Polling --}}
        @if($announcement->announcement_type === 'Polling' && $announcement->polling)
            @php
                $polling    = $announcement->polling;
                $totalVotes = $polling->options->sum(fn($o) => $o->votes->count());
                $isExpired  = $polling->deadline && now()->gt($polling->deadline);
                $isHC       = auth()->user()->role === 'HC';
                $isOwner    = $announcement->created_by === auth()->id();
                $canVote    = ! $isHC && ! $isOwner && ! $isExpired;
                $csvPath    = "polling/hasil_polling_{$polling->id}.csv";
                $csvReady   = Storage::disk('public')->exists($csvPath);
            @endphp

            <hr>
            <h5>Polling:</h5>
            <p class="text-muted">
                <strong>Batas Waktu Polling:</strong>
                {{ Carbon::parse($polling->deadline)->translatedFormat('d F Y H:i') }}
                @if($isExpired)
                    <span class="badge bg-danger">Sudah Berakhir</span>
                @else
                    <span class="badge bg-success">Masih Berlaku</span>
                @endif
            </p>

            {{-- Hasil & Progress Bar --}}
            @foreach($polling->options as $opt)
                @php
                    $cnt = $opt->votes->count();
                    $pct = $totalVotes ? round($cnt / $totalVotes * 100, 2) : 0;
                @endphp
                <div class="mb-2">
                    <strong>{{ $opt->option_text }}</strong> ({{ $cnt }} suara | {{ $pct }}%)
                    <div class="progress">
                        <div class="progress-bar" role="progressbar"
                            style="width: {{ $pct }}%"
                            aria-valuenow="{{ $pct }}"
                            aria-valuemin="0" aria-valuemax="100">
                            {{ $pct }}%
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Form Voting --}}
            @if($canVote)
                <form action="{{ route('polling.vote', $polling->id) }}" method="POST" class="mt-3">
                    @csrf
                    @foreach($polling->options as $opt)
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="polling_option_id"
                                id="o{{ $opt->id }}"
                                value="{{ $opt->id }}"
                                required>
                            <label class="form-check-label" for="o{{ $opt->id }}">
                                {{ $opt->option_text }}
                            </label>
                        </div>
                    @endforeach
                    <button class="btn btn-primary mt-2">Kirim Suara</button>
                </form>
            @elseif($isHC || $isOwner)
                <p class="text-muted mt-3"><i>Anda tidak dapat memberikan suara.</i></p>
            @endif

            {{-- Tombol Unduh CSV --}}
            @if ($isHC && $isExpired && $csvReady)
                <a href="{{ asset('storage/' . $csvPath) }}" class="btn btn-success mt-3" target="_blank">
                    Unduh Hasil Polling (CSV)
                </a>
            @endif
        @endif
        
        @if($announcement->external_link)
    <hr>
    <strong>Link Eksternal:</strong>
    <p>
        <a href="{{ $announcement->external_link }}" target="_blank" rel="noopener noreferrer">
            {{ $announcement->external_link }}
        </a>
    </p>
@endif
        {{-- Lampiran --}}
        @if($announcement->attachment_file)
            <hr>
            <strong>Lampiran:</strong><br>
            @if(Str::endsWith($announcement->attachment_file, ['.jpg', '.jpeg', '.png']))
                <img src="{{ asset('storage/announcement/' . $announcement->attachment_file) }}"
                    class="img-fluid mt-2" alt="Lampiran Gambar">
            @elseif(Str::endsWith($announcement->attachment_file, ['.pdf']))
                <a href="{{ asset('storage/announcement/' . $announcement->attachment_file) }}"
                    class="btn btn-primary mt-2" target="_blank">Lihat PDF</a>
            @else
                <a href="{{ asset('storage/announcement/' . $announcement->attachment_file) }}"
                    class="btn btn-secondary mt-2" target="_blank">Download Lampiran</a>
            @endif
        @endif

    </div>
</div>

<a href="{{ route('announcement.index') }}" class="btn btn-secondary mt-3">Kembali</a>
@stop
