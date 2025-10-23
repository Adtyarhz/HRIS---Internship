@extends('layouts.admin')

@section('title', 'Panel Approval')
@section('header_icon', 'material-symbols--verified-outline')
@section('content_header', 'Approval Management')

@php
    use Illuminate\Support\Str;
    use App\Models\Employee;
    use App\Models\Division;
    use App\Models\Position;

    function formatLabel($field)
    {
        return match ($field) {
            'employee_id' => 'Employee Name',
            'division_id' => 'Division',
            'position_id' => 'Position',
            default => Str::title(str_replace('_', ' ', $field)),
        };
    }

    function formatValue($field, $value)
    {
        if ($value === null || $value === '') {
            return '-';
        }

        // Array Handling
        if (is_array($value)) {
            return implode(', ', array_map(function ($v, $k) {
                return is_array($v) ? "$k: " . json_encode($v) : "$k: $v";
            }, $value, array_keys($value)));
        }

        // Foreign Key → Human Readable
        if ($field === 'employee_id') {
            return optional(Employee::find($value))->full_name ?? '-';
        }
        if ($field === 'division_id') {
            return optional(Division::find($value))->name ?? '-';
        }
        if ($field === 'position_id') {
            return optional(Position::find($value))->title ?? '-';
        }

        // Date Formatting
        if (str_contains($field, 'date')) {
            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return $value;
            }
        }

        // FILE HANDLING (IMAGE / PDF / OTHER)
        if (is_string($value) && preg_match('/\.(jpg|jpeg|png|gif|webp|pdf|docx?|xlsx?)$/i', $value)) {
            $cleanPath = Str::after($value, 'storage/');
            $url = asset('storage/' . $cleanPath);
            $filename = basename($cleanPath);
            $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));

            // ✅ IMAGE → Show Thumbnail + Clickable
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                return '<a href="' . $url . '" target="_blank">
                            <img src="' . $url . '" style="max-height:80px;border:1px solid #ccc;padding:2px;border-radius:4px">
                        </a><br>
                        <a href="' . $url . '" download="' . $filename . '">' . $filename . '</a>';
            }

            // ✅ PDF → OPEN IN NEW TAB (NOT DOWNLOAD)
            if ($ext === 'pdf') {
                return '<a href="' . $url . '" target="_blank">' . $filename . '</a>';
            }

            // ✅ OTHER (doc, docx, xls, xlsx) → DOWNLOAD ONLY
            return '<a href="' . $url . '" download="' . $filename . '">' . $filename . '</a>';
        }

        return $value;
    }

    function diffClass($old, $new)
    {
        return $old != $new ? 'table-warning' : '';
    }
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-dark);
        }

        @media (max-width: 768px) {
            .form-buttons-container {
                flex-direction: column-reverse;
                gap: 15px;
            }

            .btn-submit,
            .btn-cancel,
            .btn-delete {
                width: 100%;
                max-width: 100%;
            }

            .btn-submit {
                margin-left: 0px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="form-content-container mb-4">
        <div class="card-header">
            <h3 class="card-title">Detail Request</h3>
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
                    @php
                        $modelClass = $cdr->model;
                        $baseTempPath = $modelClass === 'App\Models\Certification' ? 'certifications/materials/temp' : ($modelClass === 'App\Models\TrainingHistory' ? 'training_materials/temp' : ($modelClass === 'App\Models\Announcement' ? 'temp/announcement' : ''));
                        $baseFinalPath = $modelClass === 'App\Models\Certification' ? 'certifications/materials' : ($modelClass === 'App\Models\TrainingHistory' ? 'training_materials' : ($modelClass === 'App\Models\Announcement' ? 'announcement' : ''));
                    @endphp

                    {{-- 🔹 Perbandingan data utama --}}
                    @if($cdr->action === 'update')
                        <div class="mb-3">
                            <h6>Perubahan Data</h6>
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
                                    @foreach($all_keys as $key)
                                        @if(!in_array($key, ['id', 'created_at', 'updated_at']))
                                            @php
                                                $old_value = data_get($old_data, $key);
                                                $new_value = data_get($new_data, $key);
                                            @endphp
                                            <tr class="{{ diffClass($old_value, $new_value) }}">
                                                <td><strong>{{ formatLabel($key) }}</strong></td>
                                                <td>{!! formatValue($key, $old_value) !!}</td>
                                                <td>{!! formatValue($key, $new_value) !!}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($cdr->action === 'create')
                        <div class="mb-3">
                            <h6>Data Baru</h6>
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Field</th>
                                        <th>Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cdr->changes['data'] as $key => $value)
                                        @if(!in_array($key, ['id', 'created_at', 'updated_at']))
                                            <tr>
                                                <td><strong>{{ formatLabel($key) }}</strong></td>
                                                <td>{!! formatValue($key, $value) !!}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($cdr->action === 'delete')
                        <div class="mb-3">
                            <h6>Data yang Akan Dihapus</h6>
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Field</th>
                                        <th>Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cdr->changes['data'] as $key => $value)
                                        @if(!in_array($key, ['id', 'created_at', 'updated_at']))
                                            <tr>
                                                <td><strong>{{ formatLabel($key) }}</strong></td>
                                                <td>{!! formatValue($key, $value) !!}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- 🔹 Tampilkan file tambahan dari bagian extra.related_files --}}
                    @if(isset($cdr->changes['extra']['related_files']) && ($modelClass === 'App\Models\Certification' || $modelClass === 'App\Models\TrainingHistory'))
                        <div class="mt-4">
                            <h5>Berkas Terkait</h5>
                            @php
                                $related = $cdr->changes['extra']['related_files'];
                            @endphp

                            {{-- File baru --}}
                            @if(($cdr->action === 'create' || $cdr->action === 'update') && !empty($related['new_materials']))
                                <div class="mb-3">
                                    <strong>File Baru:</strong>
                                    <ul class="list-group list-group-flush">
                                        @foreach($related['new_materials'] as $file)
                                            <li class="list-group-item">
                                                <a href="{{ asset('storage/' . $file) }}" target="_blank">
                                                    {{ basename($file) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- File yang dihapus --}}
                            @if(($cdr->action === 'delete' || $cdr->action === 'update') && !empty($related['delete_materials']))
                                <div class="mb-3">
                                    <strong>File Dihapus:</strong>
                                    <ul class="list-group list-group-flush">
                                        @foreach($related['delete_materials'] as $file)
                                            <li class="list-group-item text-danger">
                                                {{ basename($file) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- 🔹 Tampilkan attachment_file untuk Announcement --}}
                    @if(isset($cdr->changes['extra']['attachment_file']) && $modelClass === 'App\Models\Announcement')
                        <div class="mt-4">
                            <h5>File Lampiran</h5>
                            @if($cdr->action === 'create' || ($cdr->action === 'update' && $cdr->changes['extra']['attachment_file']))
                                <div class="mb-3">
                                    <strong>File Baru:</strong>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <a href="{{ asset('storage/' . $cdr->changes['extra']['attachment_file']) }}" target="_blank">
                                                {{ basename($cdr->changes['extra']['attachment_file']) }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                            @if($cdr->action === 'update' && !empty($cdr->changes['old']['attachment_file']) && $cdr->changes['old']['attachment_file'] != ($cdr->changes['extra']['attachment_file'] ?? null))
                                <div class="mb-3">
                                    <strong>File Lama:</strong>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item text-danger">
                                            {{ basename($cdr->changes['old']['attachment_file']) }}
                                        </li>
                                    </ul>
                                </div>
                            @elseif($cdr->action === 'delete' && !empty($cdr->changes['extra']['attachment_file']))
                                <div class="mb-3">
                                    <strong>File Dihapus:</strong>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item text-danger">
                                            {{ basename($cdr->changes['extra']['attachment_file']) }}
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- 🔹 Tampilkan relasi targetDivisions --}}
                    @if(isset($cdr->changes['extra']['target_divisions']) && $modelClass === 'App\Models\Announcement')
                        <div class="mt-4">
                            <h5>Divisi Tujuan</h5>
                            @if(!empty($cdr->changes['extra']['target_divisions']))
                                <ul class="list-group list-group-flush">
                                    @foreach($cdr->changes['extra']['target_divisions'] as $division)
                                        <li class="list-group-item">{{ $division['name'] }} (ID: {{ $division['id'] }})</li>
                                    @endforeach
                                </ul>
                            @else
                                <p>Umum (Tidak ada divisi khusus)</p>
                            @endif
                            @if($cdr->action === 'update' && !empty($cdr->changes['relations']['target_divisions']))
                                <div class="mt-3">
                                    <strong>Divisi Lama:</strong>
                                    <ul class="list-group list-group-flush">
                                        @foreach($cdr->changes['relations']['target_divisions'] as $division)
                                            <li class="list-group-item text-danger">{{ $division['name'] }} (ID: {{ $division['id'] }})</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- 🔹 Tampilkan relasi polling --}}
                    @if(isset($cdr->changes['extra']['polling']) && $modelClass === 'App\Models\Announcement')
                        <div class="mt-4">
                            <h5>Polling</h5>
                            @if(!empty($cdr->changes['extra']['polling']))
                                <table class="table table-bordered table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Field</th>
                                            <th>Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Deadline</td>
                                            <td>{!! formatValue('deadline', $cdr->changes['extra']['polling']['deadline']) !!}</td>
                                        </tr>
                                        <tr>
                                            <td>Created By</td>
                                            <td>{{ optional(\App\Models\User::find($cdr->changes['extra']['polling']['created_by']))->name ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                @if(!empty($cdr->changes['extra']['polling']['options']))
                                    <h6>Opsi Polling</h6>
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Opsi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cdr->changes['extra']['polling']['options'] as $option)
                                                <tr>
                                                    <td>{{ $option['option_text'] }} {{ isset($option['id']) ? '(ID: ' . $option['id'] . ')' : '' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                                @if(!empty($cdr->changes['extra']['polling']['deleted_options']))
                                    <h6>Opsi yang Dihapus</h6>
                                    <ul class="list-group list-group-flush">
                                        @foreach($cdr->changes['extra']['polling']['deleted_options'] as $optionId)
                                            <li class="list-group-item text-danger">
                                                Opsi ID: {{ $optionId }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                            @if($cdr->action === 'update' && !empty($cdr->changes['relations']['polling']))
                                <div class="mt-3">
                                    <h6>Polling Lama</h6>
                                    @foreach($cdr->changes['relations']['polling'] as $poll)
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Field</th>
                                                    <th>Nilai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Deadline</td>
                                                    <td>{!! formatValue('deadline', $poll['deadline']) !!}</td>
                                                </tr>
                                                <tr>
                                                    <td>Created By</td>
                                                    <td>{{ optional(\App\Models\User::find($poll['created_by']))->name ?? '-' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        @if(!empty($poll['options']))
                                            <h6>Opsi Polling Lama</h6>
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Opsi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($poll['options'] as $option)
                                                        <tr>
                                                            <td>{{ $option['option_text'] }} (ID: {{ $option['id'] }})</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    @endforeach
                                </div>
                            @elseif($cdr->action === 'delete' && !empty($cdr->changes['extra']['polling']))
                                <div class="mt-3">
                                    <h6>Polling yang Dihapus</h6>
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Field</th>
                                                <th>Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Deadline</td>
                                                <td>{!! formatValue('deadline', $cdr->changes['extra']['polling']['deadline']) !!}</td>
                                            </tr>
                                            <tr>
                                                <td>Created By</td>
                                                <td>{{ optional(\App\Models\User::find($cdr->changes['extra']['polling']['created_by']))->name ?? '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    @if(!empty($cdr->changes['extra']['polling']['options']))
                                        <h6>Opsi Polling yang Dihapus</h6>
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Opsi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($cdr->changes['extra']['polling']['options'] as $option)
                                                    <tr>
                                                        <td>{{ $option['option_text'] }} {{ isset($option['id']) ? '(ID: ' . $option['id'] . ')' : '' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- 🔹 Tampilkan relasi templateItems --}}
                    @if(isset($cdr->changes['extra']['template_items']) && ($modelClass === 'App\Models\KpiTemplate' || $modelClass === 'App\Models\KpiTemplateItem'))
                        <div class="mt-4">
                            <h5>Item KPI</h5>
                            @foreach($cdr->changes['extra']['template_items'] as $item)
                                <div class="mb-3">
                                    <h6>Item: {{ $item['kpi_indicator_id'] }} ({{ $item['type'] }})</h6>
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Field</th>
                                                <th>Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>KPI Indicator ID</td>
                                                <td>{{ $item['kpi_indicator_id'] }}</td>
                                            </tr>
                                            <tr>
                                                <td>Type</td>
                                                <td>{{ $item['type'] }}</td>
                                            </tr>
                                            <tr>
                                                <td>Weight</td>
                                                <td>{{ $item['weight'] }}</td>
                                            </tr>
                                            <tr>
                                                <td>Default Target</td>
                                                <td>{{ $item['default_target'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    @if(!empty($item['scoring_rules']))
                                        <h6>Aturan Skor</h6>
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Operator</th>
                                                    <th>Value 1</th>
                                                    <th>Value 2</th>
                                                    <th>Score</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($item['scoring_rules'] as $rule)
                                                    <tr>
                                                        <td>{{ $rule['operator'] }}</td>
                                                        <td>{{ $rule['value1'] }}</td>
                                                        <td>{{ $rule['value2'] ?? '-' }}</td>
                                                        <td>{{ $rule['score'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- 🔹 Tampilkan relasi scoringRules untuk KpiScoringRule --}}
                    @if(isset($cdr->changes['extra']['scoring_rules']) && $modelClass === 'App\Models\KpiScoringRule')
                        <div class="mt-4">
                            <h5>Aturan Skor</h5>
                            @foreach($cdr->changes['extra']['scoring_rules'] as $rule)
                                <div class="mb-3">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Field</th>
                                                <th>Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Operator</td>
                                                <td>{{ $rule['operator'] }}</td>
                                            </tr>
                                            <tr>
                                                <td>Value 1</td>
                                                <td>{{ $rule['value1'] }}</td>
                                            </tr>
                                            <tr>
                                                <td>Value 2</td>
                                                <td>{{ $rule['value2'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td>Score</td>
                                                <td>{{ $rule['score'] }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
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
    </div>

    {{-- BAGIAN AKSI (FOOTER) --}}
    @php
        $user = Auth::user();
        $isApprover = optional($user->employee)->position->title === config('approval.approver_position_name', 'HC & GA Manager');
    @endphp

    {{-- Aksi untuk CHECKER --}}
    @if($cdr->status === 'pending' && $cdr->requested_by !== $user->id)
        <div class="form-content-container mb-4">
            <div class="card-header">
                <h3 class="card-title">Checker Action</h3>
            </div>
            <div class="card-body">

                {{-- FORM REJECT --}}
                <form method="POST" action="{{ route('approvals.reject', $cdr) }}">
                    @csrf
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="check_notes" class="col-form-label">Notes :</label>
                                <textarea name="status_notes" id="check_notes" class="form-control" rows="3"
                                    placeholder="Optional note..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reject_notes_checker" class="col-form-label">Reject Reason <span
                                        class="text-danger">*</span> :</label>
                                <textarea name="status_notes" id="reject_notes_checker" class="form-control" rows="2"
                                    placeholder="Explain rejection reason..." required></textarea>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="form-buttons-container">
                                <button type="submit" class="btn btn-delete">Reject</button>
                </form>
                <a href="{{ route('approvals.index') }}" class="btn btn-cancel">Cancel</a>
                <form method="POST" action="{{ route('approvals.check', $cdr) }}">
                    @csrf
                    <button type="submit" class="btn btn-submit">Verify</button>
                </form>
            </div>
        </div>

        </div>
        </div>
        </div>
    @endif

    {{-- Aksi untuk APPROVER --}}
    @if($cdr->status === 'checked' && $isApprover)
        <div class="form-content-container mb-4">
            <div class="card-header">
                <h3 class="card-title">Approval Action</h3>
            </div>
            <div class="card-body">

                {{-- FORM REJECT --}}
                <form method="POST" action="{{ route('approvals.reject', $cdr) }}">
                    @csrf
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="check_notes" class="col-form-label">Notes :</label>
                                <textarea name="status_notes" id="approve_notes" class="form-control" rows="3"
                                    placeholder="Optional note..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reject_notes_checker" class="col-form-label">Reject Reason <span
                                        class="text-danger">*</span> :</label>
                                <textarea name="status_notes" id="reject_notes_approver" class="form-control" rows="2"
                                    placeholder="Explain rejection reason..." required></textarea>
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="form-buttons-container">
                                <button type="submit" class="btn btn-delete">Reject</button>
                </form>
                <a href="{{ route('approvals.index') }}" class="btn btn-cancel">Cancel</a>
                <form method="POST" action="{{ route('approvals.approve', $cdr) }}">
                    @csrf
                    <button type="submit" class="btn btn-submit">Apply</button>
                </form>
            </div>
        </div>

        </div>
        </div>
        </div>
    @endif

    {{-- Sudah Final --}}
    @if(in_array($cdr->status, ['applied', 'rejected', 'failed']))
        <p class="text-muted mt-2">
            This request has been processed and no further action is required.
        </p>
    @endif
@endsection