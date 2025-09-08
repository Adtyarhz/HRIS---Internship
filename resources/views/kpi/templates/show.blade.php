@extends('layouts.admin')

@section('title', 'KPI Performance Index')
@section('header_icon', 'ri--bill-line-01')
@section('content_header', 'KPI Performance Index')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">

    <style>
        /* Khusus halaman Manage KPI Template */

        /* Form Add KPI Item */
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

        .action-button-delete-item {
            display: flex;
            margin-left: auto;
            background-color: #FF4242;
            border-radius: 5px;
            width: 110px;
            height: 35px;
            color: white;
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 500;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            border: none;
        }

        .delete-rule {
            display: inline-block;
            width: 28px;
            height: 28px;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            border: none;
            background: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cg fill='%23FF4242'%3E%3Cpath fill-rule='evenodd' d='M17 5V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V7h1a1 1 0 1 0 0-2zm-2-1H9v1h6zm2 3H7v11a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z' clip-rule='evenodd'/%3E%3Cpath d='M9 9h2v8H9zm4 0h2v8h-2z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .delete-rule:hover {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cg fill='%23e63939'%3E%3Cpath fill-rule='evenodd' d='M17 5V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V7h1a1 1 0 1 0 0-2zm-2-1H9v1h6zm2 3H7v11a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z' clip-rule='evenodd'/%3E%3Cpath d='M9 9h2v8H9zm4 0h2v8h-2z'/%3E%3C/g%3E%3C/svg%3E");
        }

        .action-button-delete-rule:hover {
            background-color: #e63939;
            color: white;
        }

        .col-text {
            font-family: "Noto Sans Georgian", sans-serif;
            font-size: 15px;
            color: #000;
            text-align: left;
            padding-top: calc(.375rem + 1px);
            line-height: 1.5;
        }

        .col-text-rule {
            font-family: "Noto Sans Georgian", sans-serif;
            font-size: 13px;
            color: #000;
            text-align: left;
            line-height: 1.5;
        }

        .list-group-item {
            background: #fefef9;
        }
    </style>
@endpush

@section('content')
    @include('kpi.partials.tab-menu')
    <div class="container-fluid">

        {{-- Form tambah KPI Item --}}
        <div class="form-content-container mb-4">
            <div class="card-body">
                <form action="{{ route('kpi-templates.items.store', $kpiTemplate->id) }}" method="POST">
                    @csrf
                    <div class="row">
                        
                        <!-- Indicator -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kpi_indicator_id" class="col-form-label">
                                    KPI Indicator <span class="text-danger">*</span> :
                                </label>
                                <select name="kpi_indicator_id" id="kpi_indicator_id"
                                    class="form-control @error('kpi_indicator_id') is-invalid @enderror" required>
                                    <option value="">-- Select Indicator --</option>
                                    @foreach ($availableIndicators as $indicator)
                                        <option value="{{ $indicator->id }}"
                                            {{ old('kpi_indicator_id') == $indicator->id ? 'selected' : '' }}>
                                            {{ $indicator->indicator_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kpi_indicator_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="type" class="col-form-label">
                                    Type <span class="text-danger">*</span> :
                                </label>
                                <select name="type" id="type"
                                    class="form-control @error('type') is-invalid @enderror" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="1" {{ old('type') == '1' ? 'selected' : '' }}>Routine</option>
                                    <option value="2" {{ old('type') == '2' ? 'selected' : '' }}>Improvement</option>
                                    <option value="3" {{ old('type') == '3' ? 'selected' : '' }}>Breakthrough</option>
                                </select>
                                @error('type')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Weight -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="weight" class="col-form-label">
                                    Weight (%) <span class="text-danger">*</span> :
                                </label>
                                <input type="number" name="weight" id="weight"
                                    class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight') }}"
                                    required placeholder="Enter Weight">
                                @error('weight')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Default Target -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="default_target" class="col-form-label">
                                    Default Target <span class="text-danger">*</span> :
                                </label>
                                <input type="text" name="default_target" id="default_target"
                                    class="form-control @error('default_target') is-invalid @enderror"
                                    value="{{ old('default_target') }}" required placeholder="Enter Default Target">
                                @error('default_target')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="col-12">
                            <div class="form-buttons-container">
                                <a href="{{ route('kpi-templates.index') }}" class="btn btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-submit">Add</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- List KPI Items --}}
        @forelse($kpiTemplate->templateItems as $item)
            <div class="form-content-container mb-4">
                {{-- Header Item --}}
                <div class="card-header">
                    <h3 class="card-title">{{ $item->indicator->indicator_name }} (Weight:
                        {{ number_format($item->weight, 2) }}%)</h3>
                    <button type="button" class="action-button-delete-item"
                        onclick="showDeleteModal('kpi-template-item-{{ $item->id }}')">Delete Item
                    </button>
                </div>

                {{-- Body Item --}}
                <div class="card-body">
                    <h6 class="col-text">Scoring Rules :</h6>

                    {{-- List Scoring Rules --}}
                    @if ($item->scoringRules->isNotEmpty())
                        <div class="list-group mb-3">
                            @foreach ($item->scoringRules as $rule)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="col-text-rule">
                                        If achievement <strong>{{ $rule->operator }}</strong>
                                        {{ number_format($rule->value1, 2) }}
                                        @if ($rule->operator == 'between')
                                            & {{ number_format($rule->value2, 2) }}
                                        @endif
                                        → Score = <strong>{{ number_format($rule->score, 2) }}</strong>
                                    </div>
                                    <button type="button" class="delete-rule"
                                        onclick="showDeleteModal('delete-scoring-rule', '{{ route('kpi-scoring-rules.destroy', [$rule->id]) }}')">
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-3">No scoring rules yet.</p>
                    @endif

                    {{-- Add Rule --}}
                    <form action="{{ route('kpi-template-items.rules.store', $item->id) }}" method="POST"
                        class="row g-2 align-items-end">
                        @csrf
                        <div class="col-auto">
                            <label class="col-form-label">Add Rule <span class="text-danger">*</span> :</label>
                            <select name="operator" class="form-control" required>
                                <option value="">-- Select Rule --</option>
                                <option value="<">&lt;</option>
                                <option value="<=">&lt;=</option>
                                <option value="=">=</option>
                                <option value=">=">&gt;=</option>
                                <option value=">">&gt;</option>
                                <option value="between">between</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="col-form-label">Value 1 <span class="text-danger">*</span> :</label>
                            <input type="number" name="value1" class="form-control" placeholder="Value 1" required>
                        </div>
                        <div class="col-auto">
                            <label class="col-form-label">Value 2 :</label>
                            <input type="number" name="value2" class="form-control"
                                placeholder="Value 2 (if between)">
                        </div>
                        <div class="col-auto">
                            <label class="col-form-label">Score <span class="text-danger">*</span> :</label>
                            <input type="number" name="score" class="form-control" placeholder="Score" required>
                        </div>
                        <div class="col-auto ms-auto">
                            <button type="submit" class="btn btn-submit">Add Rule</button>
                        </div>
                    </form>

                    <!-- Komponen Modal Delete Template Item -->
                    <x-delete-modal modalId="kpi-template-item-{{ $item->id }}" :action="route('kpi-template-items.destroy', [$item->id])"
                        message="Are you sure to delete this template item?" />

                    <!-- Komponen Modal Delete Scoring Rule -->
                    <x-delete-modal-material modalId="delete-scoring-rule"
                        message="Are you sure to delete this scoring rule?" />
                </div>
            </div>
        @empty
            <p>No KPI items in this template yet.</p>
        @endforelse

    </div>
@endsection

@push('scripts')
    <script>
        // Nonaktifkan tombol submit saat pengiriman dan log data
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            console.log('Form submitted with method: PUT');
            console.log('Form data:', new FormData(this));
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Menyimpan...';
            }
        });
    </script>
@endpush
