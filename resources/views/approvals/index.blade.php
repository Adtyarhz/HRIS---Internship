@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Panel Approval</h1>

    {{-- Notifikasi Sukses atau Error --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif


    {{-- BAGIAN UNTUK APPROVER (HANYA TAMPIL JIKA USER ADALAH APPROVER) --}}
    @if($isApprover)
    <div class="card">
        <div class="card-header">
            <h3>Menunggu Persetujuan (Approver)</h3>
        </div>
        <div class="card-body">
            @if($pendingForApproval->isEmpty())
                <p>Tidak ada request yang menunggu persetujuan Anda.</p>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Model</th>
                            <th>Aksi</th>
                            <th>Pembuat</th>
                            <th>Diperiksa Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingForApproval as $cdr)
                            <tr>
                                <td>{{ $cdr->id }}</td>
                                <td>{{ $cdr->model_short_name }}</td>
                                <td>{{ Str::upper($cdr->action) }}</td>
                                <td>{{ $cdr->requester->name }}</td>
                                <td>{{ optional($cdr->checker)->name }}</td>
                                <td>
                                    <a href="{{ route('approvals.show', $cdr->id) }}" class="btn btn-sm btn-info">Tinjau & Putuskan</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- BAGIAN UNTUK CHECKER (HANYA TAMPIL JIKA USER BUKAN APPROVER) --}}
    @else
    <div class="card mb-4">
        <div class="card-header">
            <h3>Menunggu Diperiksa (Checker)</h3>
        </div>
        <div class="card-body">
            @if($pendingForChecking->isEmpty())
                <p>Tidak ada request yang menunggu untuk diperiksa.</p>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Model</th>
                            <th>Aksi</th>
                            <th>Pembuat</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingForChecking as $cdr)
                            <tr>
                                <td>{{ $cdr->id }}</td>
                                <td>{{ $cdr->model_short_name }}</td>
                                <td>{{ Str::upper($cdr->action) }}</td>
                                <td>{{ $cdr->requester->name }}</td>
                                <td>{{ $cdr->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('approvals.show', $cdr->id) }}" class="btn btn-sm btn-info">Lihat Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection
