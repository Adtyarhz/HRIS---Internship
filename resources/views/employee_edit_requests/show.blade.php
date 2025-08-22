@extends('layouts.admin')

@section('title', 'Employee Request Detail')

@section('content_header')
    <div class="header-with-icon">
        <iconify-icon icon="charm:git-request" width="24" height="24"></iconify-icon>
        Employee Request
    </div>
@endsection

@section('content')
<div class="container">
    <h1>Detail Permintaan Perubahan Data</h1>

    {{-- Flash message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        // Decode original & changed data supaya aman
        $originalData = is_string($editRequest->original_data) 
            ? json_decode($editRequest->original_data, true) ?? [] 
            : ($editRequest->original_data ?? []);

        $changedData = is_string($editRequest->changed_data) 
            ? json_decode($editRequest->changed_data, true) ?? [] 
            : ($editRequest->changed_data ?? []);

        // Helper format tanggal
        // Helper format nilai
function formatValue($field, $value) {
    if (is_array($value)) {
        // kalau array, tampilkan dalam bentuk JSON atau implode
        return implode(', ', array_map(function($v, $k) {
            return is_array($v) ? $k . ': ' . json_encode($v) : $k . ': ' . $v;
        }, $value, array_keys($value)));
    }

    if (!empty($value) && str_contains($field, 'date')) {
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return $value;
        }
    }

    return $value ?? '-';
}

    @endphp

    {{-- Info karyawan --}}
    <div class="mb-3">
        <strong>Karyawan:</strong> {{ $editRequest->employee->full_name ?? '-' }} <br>
        <strong>Status:</strong> {{ ucfirst($editRequest->status) }} <br>
        <strong>Metode:</strong> {{ ucfirst($editRequest->method) }} <br>
        <strong>Diajukan:</strong> {{ $editRequest->requested_at ? \Carbon\Carbon::parse($editRequest->requested_at)->format('d-m-Y H:i') : '-' }} <br>
        <strong>Disetujui Oleh:</strong> {{ $editRequest->approvedBy->name ?? '-' }}
    </div>

    <h4>Perubahan Data</h4>

    @if(!empty($changedData))
        @php
            // Cek apakah flat array (field => value) atau nested (tabel => records)
            $isFlat = true;
            foreach ($changedData as $value) {
                if (is_array($value)) {
                    $isFlat = false;
                    break;
                }
            }
        @endphp

        @if($isFlat)
            {{-- Format flat field --}}
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">Data</div>
                <div class="card-body">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Data Lama</th>
                                <th>Data Baru</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($changedData as $field => $newValue)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                    <td>{{ formatValue($field, $originalData[$field] ?? '-') }}</td>
                                    <td>{{ formatValue($field, $newValue ?? '-') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            {{-- Format nested (per tabel & record) --}}
            @foreach($changedData as $table => $records)
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        {{ ucfirst(str_replace('_', ' ', $table)) }}
                    </div>
                    <div class="card-body">
                        @if(is_array($records) && !empty($records))
                            @php 
                                $firstValue = reset($records);
                                $isNested = is_array($firstValue) && array_keys($records) !== range(0, count($records) - 1);
                            @endphp

                            @if($isNested)
                                @foreach($records as $recordId => $fields)
                                    <h6>Record ID: {{ $recordId }}</h6>
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Field</th>
                                                <th>Data Lama</th>
                                                <th>Data Baru</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($fields as $field => $newValue)
                                                <tr>
                                                    <td>{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                                    <td>{{ formatValue($field, $originalData[$table][$recordId][$field] ?? '-') }}</td>
                                                    <td>{{ formatValue($field, $newValue ?? '-') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endforeach
                            @else
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Data Lama</th>
                                            <th>Data Baru</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $field => $newValue)
                                            <tr>
                                                <td>{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                                <td>{{ formatValue($field, $originalData[$table][$field] ?? '-') }}</td>
                                                <td>{{ formatValue($field, $newValue ?? '-') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        @else
                            <p class="text-muted mb-0">Tidak ada data perubahan pada {{ ucfirst(str_replace('_', ' ', $table)) }}.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    @else
        <p class="text-muted">Tidak ada perubahan data.</p>
    @endif

    {{-- Tombol Approve / Reject --}}
    @if($editRequest->status === 'waiting')
        <form action="{{ route('employee-edit-requests.approve', $editRequest->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">Approve</button>
        </form>
        <form action="{{ route('employee-edit-requests.reject', $editRequest->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
        </form>
    @endif

    <a href="{{ route('employee-edit-requests.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
