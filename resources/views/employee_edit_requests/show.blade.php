@extends('layouts.admin')

@section('title', 'Employee Request Detail')

@section('content_header')
    <div class="header-with-icon">
        <iconify-icon icon="charm:git-request" width="24" height="24"></iconify-icon>
        Employee Request
    </div>
@endsection

@section('content')
<a href="{{ route('employee-edit-requests.index') }}" class="action-button btn-back">
                    <i class="fas fa-arrow-left"></i> Back to List</a>
<div class="container">
    <h1>Detail of Employee Data Change Request</h1>

    {{-- Flash message --}}
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    {{-- Employee info --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3 fw-bold">Employee :</div>
                <div class="col-md-9">{{ $editRequest->employee->full_name ?? '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3 fw-bold">Status :</div>
                <div class="col-md-9">{{ ucfirst($editRequest->status) }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3 fw-bold">Method :</div>
                <div class="col-md-9">{{ ucfirst($editRequest->method) }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3 fw-bold">Submitted At :</div>
                <div class="col-md-9">
                    {{ $editRequest->requested_at ? \Carbon\Carbon::parse($editRequest->requested_at)->format('d-m-Y H:i') : '-' }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 fw-bold">Approved By :</div>
                <div class="col-md-9">{{ $editRequest->approvedBy->name ?? '-' }}</div>
            </div>
        </div>
    </div>

    <h4>Data Changes</h4>

    {{-- DATA CHANGE LOGIC (UNCHANGED) --}}

    @php
        use Illuminate\Support\Str;

        $originalData = is_string($editRequest->original_data) 
            ? json_decode($editRequest->original_data, true) ?? [] 
            : ($editRequest->original_data ?? []);

        $changedData = is_string($editRequest->changed_data) 
            ? json_decode($editRequest->changed_data, true) ?? [] 
            : ($editRequest->changed_data ?? []);

        function formatValue($field, $value) {
            if (is_array($value)) {
                return implode(', ', array_map(function($v, $k) {
                    return is_array($v) ? $k . ': ' . json_encode($v) : $k . ': ' . $v;
                }, $value, array_keys($value)));
            }
            if ($field === 'division_id') {
                $division = \App\Models\Division::find($value);
                return $division ? $division->name : '-';
            }

            if ($field === 'position_id') {
                $position = \App\Models\Position::find($value);
                return $position ? $position->title : '-';
            }

            // Format tanggal
            if (!empty($value) && str_contains($field, 'date')) {
                try {
                    return \Carbon\Carbon::parse($value)->format('Y-m-d');
                } catch (\Exception $e) {
                    return $value;
                }
            }

            // 🔥 Cek apakah value adalah file path
            if (!empty($value) && is_string($value) && 
                (str_contains($value, 'certifications/') || str_contains($value, 'storage/') || str_contains($value, '.png') || str_contains($value, '.jpg') || str_contains($value, '.pdf'))
            ) {
                // hapus prefix storage/
                $cleanPath = Str::after($value, 'storage/');
                $url = asset('storage/' . $cleanPath);

                $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
                $filename = basename($cleanPath); // ✅ ambil nama file asli

                // Kalau gambar → tampilkan preview + link dengan nama file
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    return '<a href="'.$url.'" target="_blank">
                                <img src="'.$url.'" alt="preview" style="max-height:80px; border:1px solid #ccc; padding:2px; border-radius:4px">
                            </a>
                            <div><a href="'.$url.'" download="'.$filename.'">'.$filename.'</a></div>';
                }

                // Kalau bukan gambar → link dengan nama file + force download
                return '<a href="'.$url.'" download="'.$filename.'">'.$filename.'</a>';
            }

            return $value ?? '-';
        }

    @endphp

    @if(!empty($changedData))
        @php
            $flattened = [];


            foreach ($changedData as $table => $records) {
                if (is_array($records)) {
                    foreach ($records as $recordId => $fields) {
                        if (is_array($fields)) {
                            foreach ($fields as $field => $newValue) {
                                $oldValue = $originalData[$table][$recordId][$field] 
                                    ?? $originalData[$table][$field] 
                                    ?? '-';

                                $flattened[] = [
                                    'field' => $field,
                                    'old'   => $oldValue,
                                    'new'   => $newValue,
                                ];
                            }
                        } else {
                            $oldValue = $originalData[$table][$recordId] ?? '-';

                            // mapping khusus untuk material files
                            $fieldName = is_numeric($recordId) && $recordId == 0 ? 'Certification Supporting files' : $recordId;

                            if ($fieldName === 'Material files') {
                                $oldFiles = is_array($oldValue) ? $oldValue : (empty($oldValue) || $oldValue === '-' ? [] : [$oldValue]);
                                $newFiles = is_array($fields) ? $fields : [$fields];

                                $flattened[] = [
                                    'field' => $fieldName,
                                    // ambil file pertama saja biar formatValue bisa handle preview
                                    'old'   => count($oldFiles) ? $oldFiles[0] : '-',
                                    'new'   => count($newFiles) ? $newFiles[0] : '-',
                                ];
                            } else {
                                $flattened[] = [
                                    'field' => $fieldName,
                                    'old'   => $oldValue,
                                    'new'   => $fields,
                                ];
                            }
                        }
                    }
                } else {
                    $oldValue = $originalData[$table] ?? '-';
                    $flattened[] = [
                        'field' => $table,
                        'old'   => $oldValue,
                        'new'   => $records,
                    ];
                }
            }
        @endphp

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white text-center">Perubahan Data</div>
            <div class="card-body">

                <table class="table table-sm table-bordered text-center custom-table">
                    <thead class="table-secondary">
                        <tr>
                            <th>Field</th>
                            <th>Old Data</th>
                            <th>New Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($flattened as $row)
                                @php
                                    $label = $row['field'];
                                    if ($label === 'division_id') $label = 'Division';
                                    elseif ($label === 'position_id') $label = 'Position';
                                    $old = $row['old'];
                                    $new = $row['new'];

                                    $normalize = fn($v) => ($v === null || $v === '' || $v === '-' ? null : trim((string) $v));

                                    $isChanged = $normalize($old) !== $normalize($new);
                                @endphp
                                <tr class="{{ $isChanged ? 'changed-row' : '' }}">
                                <td>{{ ucfirst(str_replace('_', ' ', $label)) }}</td>
                                <td>{!! formatValue($row['field'], $row['old']) !!}</td>
                                <td>{!! formatValue($row['field'], $row['new']) !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <p class="text-muted text-center">No data changes.</p>
    @endif

    {{-- Buttons --}}
   <div class="d-flex justify-content-end gap-3 fixed-bottom mb-4 me-4 button-group">
    @if($editRequest->status === 'waiting')
        <form action="{{ route('employee-edit-requests.approve', $editRequest->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success btn-sm px-4">Approve</button>
        </form>
        <form action="{{ route('employee-edit-requests.reject', $editRequest->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm px-4">Reject</button>
        </form>
    @endif
</div>

</div>
@endsection

@push('styles')
<style>
    .custom-table tbody tr {
        background-color: #f9f9f9;
    }
    .custom-table tbody tr:nth-child(even) {
        background-color: #f1f1f1;
    }
    .changed-row td {
        font-weight: bold;
        background-color: #fff8c6 !important; /* kuning lembut */
        border-color: #e0c000;
    }
    .button-group {
        bottom: 20px; /* beri jarak dari bawah */
        right: 30px; /* beri jarak dari sisi kanan */
    }

    .button-group .btn {
        min-width: 90px;
        border-radius: 6px;
    }

    .button-group .btn + .btn,
    .button-group form + form,
    .button-group form + a {
        margin-left: 10px;
    }
    .btn-back { 
    background-color: #383535ff; 
    padding: 5px 17px;
    border-radius: 6px;
    font-size: 14px;        /* ✅ Tambahkan baris ini */
    font-weight: 400;       /* ubah 10 jadi 400 (karena 10 tidak valid untuk font-weight) */
    font-family: 'Manrope', sans-serif;
    text-decoration: none;
    color: white;
    display: inline-block;
    margin-bottom: 5px;
    margin-left: 115px;
}

    .btn-back:hover { 
        background-color: #555; 
        color: white;              /* biar teks tetap putih */
        text-decoration: none;     /* hilangkan underline */
    }
</style>
@endpush
