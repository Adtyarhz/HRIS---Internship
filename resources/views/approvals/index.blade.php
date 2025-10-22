@extends('layouts.admin')

@section('title', 'Panel Approval')
@section('header_icon', 'material-symbols--verified-outline')
@section('content_header', 'Approval Management')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
<style>
    .section-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }

    .table-custom th,
    .table-custom td {
        text-align: center;
        vertical-align: middle;
        font-size: 13px;
        white-space: nowrap;
    }

    .table-custom th {
        background-color: #DFD9B6;
        font-weight: 600;
    }

    .table-responsive {
        width: 100%;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .container-fluid {
        padding-bottom: 30px;
    }

    .btn-info {
        background-color: #0b9db4;
        color: #fff;
        font-size: 13px;
        border-radius: 6px;
        padding: 6px 12px;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-info:hover {
        background-color: #098ba5;
        color: #fff;
    }

    .alert {
        font-size: 14px;
    }

    /* ===== Responsiveness ===== */
    @media (max-width: 768px) {
        .table-custom,
        .table-custom thead,
        .table-custom tbody,
        .table-custom th,
        .table-custom td,
        .table-custom tr {
            display: block;
            width: 100%;
        }

        .table-custom thead {
            display: none;
        }

        .table-custom tr {
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.75rem;
            background-color: #fff;
        }

        .table-custom td {
            text-align: left !important;
            white-space: normal;
            padding: 6px 8px;
            font-size: 13px;
            border: none;
        }

        .table-custom td::before {
            content: attr(data-label);
            font-weight: 600;
            display: block;
            margin-bottom: 2px;
            color: #333;
        }

        .btn-info {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content-wrapper')
<section class="content">
    <div class="container-fluid">
        <div class="form-content-container">
            <div class="card-body">

                {{-- Notifikasi --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info">{{ session('info') }}</div>
                @endif

                {{-- APPROVER SECTION --}}
                @if($isApprover)
                    <div class="section-title d-flex justify-content-between align-items-center flex-wrap">
                        Menunggu Persetujuan (Approver)
                    </div>

                    @if($pendingForApproval->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-custom text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Model</th>
                                        <th>Aksi</th>
                                        <th>Pembuat</th>
                                        <th>Diperiksa Oleh</th>
                                        <th>Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingForApproval as $cdr)
                                        <tr>
                                            <td data-label="ID">{{ $cdr->id }}</td>
                                            <td data-label="Model">{{ $cdr->model_short_name }}</td>
                                            <td data-label="Aksi">{{ Str::upper($cdr->action) }}</td>
                                            <td data-label="Pembuat">{{ $cdr->requester->name }}</td>
                                            <td data-label="Diperiksa Oleh">{{ optional($cdr->checker)->name ?? '-' }}</td>
                                            <td data-label="Opsi">
                                                <div class="action-buttons">
                                                    <a href="{{ route('approvals.show', $cdr->id) }}" class="btn-info" title="Tinjau & Putuskan">
                                                        <i class="fa-solid fa-eye"></i> Tinjau
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            Tidak ada request yang menunggu persetujuan Anda.
                        </div>
                    @endif

                {{-- CHECKER SECTION --}}
                @else
                    <div class="section-title d-flex justify-content-between align-items-center flex-wrap">
                        Menunggu Diperiksa (Checker)
                    </div>

                    @if($pendingForChecking->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-custom text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Model</th>
                                        <th>Aksi</th>
                                        <th>Pembuat</th>
                                        <th>Tanggal/Waktu</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingForChecking as $cdr)
                                        <tr>
                                            <td data-label="No.">{{ $loop->iteration }}</td>
                                            <td data-label="Model">{{ $cdr->model_short_name }}</td>
                                            <td data-label="Aksi">{{ Str::upper($cdr->action) }}</td>
                                            <td data-label="Pembuat">{{ $cdr->requester->name }}</td>
                                            <td data-label="Tanggal/Waktu">{{ $cdr->created_at->format('d M Y H:i') }}</td>
                                            <td data-label="Aksi">
                                                <div class="action-buttons">
                                                    <a href="{{ route('approvals.show', $cdr->id) }}" class="btn-info" title="Lihat Detail">
                                                        <i class="fa-solid fa-eye"></i> Detail
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            Tidak ada request yang menunggu untuk diperiksa.
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
