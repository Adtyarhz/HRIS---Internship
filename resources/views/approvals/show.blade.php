@extends('layouts.admin')
@section('title', 'Detail Permintaan Approval')

@php
// Helper untuk mendapatkan nilai dari array, bahkan jika nested.
function get_value($array, $key) {
    return data_get($array, $key, 'N/A');
}

// Helper untuk membandingkan nilai dan memberikan kelas CSS jika berbeda.
function get_comparison_class($old_value, $new_value) {
    return $old_value != $new_value ? 'table-warning' : '';
}
@endphp

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Detail Request #{{ $cdr->id }}</h4>
                <a href="{{ route('approvals.index') }}" class="btn btn-light btn-sm">&larr; Kembali ke Daftar</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                {{-- Kolom Kiri: Informasi Meta --}}
                <div class="col-md-4">
                    <h5>Informasi Request</h5>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th style="width: 120px;">ID Request</th>
                            <td>: #{{ $cdr->id }}</td>
                        </tr>
                        <tr>
                            <th>Tipe Model</th>
                            <td>: {{ $cdr->model_short_name }}</td>
                        </tr>
                        <tr>
                            <th>Aksi</th>
                            <td>: <span class="badge bg-info text-uppercase">{{ $cdr->action }}</span></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>: <span class="badge bg-secondary text-uppercase">{{ $cdr->status }}</span></td>
                        </tr>
                    </table>

                    <hr>

                    <h5>Jejak Audit</h5>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th style="width: 120px;">Dibuat oleh</th>
                            <td>: {{ optional($cdr->requester)->name }}</td>
                        </tr>
                        <tr>
                            <th>Pada</th>
                            <td>: {{ $cdr->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                        @if($cdr->checked_by)
                        <tr class="text-success">
                            <th>Diperiksa oleh</th>
                            <td>: {{ optional($cdr->checker)->name }}</td>
                        </tr>
                        <tr>
                            <th>Pada</th>
                            <td>: {{ optional($cdr->checked_at)->format('d M Y, H:i') }}</td>
                        </tr>
                        @endif
                        @if($cdr->approved_by)
                        <tr class="text-success">
                            <th>Disetujui oleh</th>
                            <td>: {{ optional($cdr->approver)->name }}</td>
                        </tr>
                        <tr>
                            <th>Pada</th>
                            <td>: {{ optional($cdr->approved_at)->format('d M Y, H:i') }}</td>
                        </tr>
                        @endif
                         @if($cdr->rejected_by)
                        <tr class="text-danger">
                            <th>Ditolak oleh</th>
                            <td>: {{ optional($cdr->rejecter)->name }}</td>
                        </tr>
                        <tr>
                            <th>Pada</th>
                            <td>: {{ optional($cdr->rejected_at)->format('d M Y, H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>

                {{-- Kolom Kanan: Detail Perubahan --}}
                <div class="col-md-8">
                    <h5>Detail Perubahan</h5>

                    @if ($cdr->action == 'update')
                        {{-- Tampilan untuk Aksi UPDATE --}}
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Field</th>
                                    <th>Data Lama</th>
                                    <th>Data Baru</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $old_data = $cdr->changes['old'] ?? [];
                                    $new_data = $cdr->changes['new'] ?? [];
                                    $all_keys = array_unique(array_merge(array_keys($old_data), array_keys($new_data)));
                                @endphp
                                @foreach ($all_keys as $key)
                                    @php
                                        $old_value = get_value($old_data, $key);
                                        $new_value = get_value($new_data, $key);
                                    @endphp
                                    <tr class="{{ get_comparison_class($old_value, $new_value) }}">
                                        <td><strong>{{ Str::title(str_replace('_', ' ', $key)) }}</strong></td>
                                        <td>{{ $old_value }}</td>
                                        <td>{{ $new_value }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        {{-- Tampilan untuk Aksi CREATE atau DELETE --}}
                        <table class="table table-bordered">
                             <thead class="table-dark">
                                <tr>
                                    <th>Field</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cdr->changes['data'] ?? [] as $key => $value)
                                <tr>
                                    <td style="width: 30%;"><strong>{{ Str::title(str_replace('_', ' ', $key)) }}</strong></td>
                                    <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    {{-- Menampilkan Catatan/Remarks --}}
                    @if($cdr->status_notes)
                    <div class="mt-4">
                        <h5>Catatan Terakhir:</h5>
                        <div class="alert alert-warning">
                            {{ $cdr->status_notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- BAGIAN AKSI (FOOTER) --}}
        <div class="card-footer">
            @php
                $user = Auth::user();
                $isApprover = optional($user->employee)->position->title === config('approval.approver_position_name', 'HC & GA Manager');
            @endphp

            {{-- Form Aksi untuk CHECKER --}}
            @if($cdr->status === 'pending' && $cdr->requested_by !== $user->id)
            <div class="border p-3 rounded">
                <h5>Aksi Checker</h5>
                <form method="POST" action="{{ route('approvals.check', $cdr) }}" class="d-inline-block">
                    @csrf
                    <div class="mb-2">
                        <label for="check_notes">Catatan (Opsional):</label>
                        <textarea name="status_notes" id="check_notes" class="form-control" rows="2" placeholder="Berikan catatan jika perlu..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning">Verifikasi (Mark as Checked)</button>
                </form>
                <form method="POST" action="{{ route('approvals.reject', $cdr) }}" class="d-inline-block">
                    @csrf
                     <div class="mb-2">
                        <label for="reject_notes_checker">Alasan Penolakan (Wajib):</label>
                        <textarea name="status_notes" id="reject_notes_checker" class="form-control" rows="2" placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </form>
            </div>
            @endif

             {{-- Form Aksi untuk APPROVER --}}
            @if($cdr->status === 'checked' && $isApprover)
             <div class="border p-3 rounded">
                <h5>Aksi Approver</h5>
                <form method="POST" action="{{ route('approvals.approve', $cdr) }}" class="d-inline-block">
                    @csrf
                    <div class="mb-2">
                        <label for="approve_notes">Catatan (Opsional):</label>
                        <textarea name="status_notes" id="approve_notes" class="form-control" rows="2" placeholder="Berikan catatan jika perlu..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Setujui dan Terapkan</button>
                </form>
                 <form method="POST" action="{{ route('approvals.reject', $cdr) }}" class="d-inline-block">
                    @csrf
                     <div class="mb-2">
                        <label for="reject_notes_approver">Alasan Penolakan (Wajib):</label>
                        <textarea name="status_notes" id="reject_notes_approver" class="form-control" rows="2" placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </form>
            </div>
            @endif

            @if(in_array($cdr->status, ['applied', 'rejected', 'failed']))
                <p class="text-muted">Request ini telah selesai diproses dan tidak ada aksi lebih lanjut yang diperlukan.</p>
            @endif
        </div>
    </div>
</div>
@endsection
