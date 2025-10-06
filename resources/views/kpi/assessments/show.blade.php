@extends('layouts.admin')

@section('title', 'Key Performance Index')
@section('header_icon', 'ri--bill-line-01')
@section('content_header', 'Key Performance Index')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
        .table-custom th {
            background-color: #DFD9B6;
            font-weight: 600;
            font-size: 13px;
            text-align: center;
        }

        .table-custom td {
            vertical-align: middle;
            font-size: 13px;
            text-align: center;
        }

        .kpi-table-note {
            font-size: 13px;
            color: #555;
        }

        .btn-secondary {
            border-radius: 5px;
            width: 110px;
            height: 37px;
            color: white;
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            border: none;
        }

        .btn-secondary {
            margin-left: 10px;
        }

        .tooltip-submit {
            position: absolute;
            background: rgba(0, 0, 0, 0.700);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            pointer-events: none;
            font-size: 12px;
            max-width: 300px;
            white-space: pre-wrap;
            z-index: 1000;
        }
    </style>
@endpush

@section('content-wrapper')
    @include('kpi.partials.tab-menu')
    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">

                    {{-- Header Info --}}
                    <div class="d-flex justify-content-between mb-3">
                        <span>Assessment for: <strong>{{ $kpiAssessment->employee->full_name }}</strong></span>
                        <span>Period: <strong>
                            @php
                                $name = $kpiAssessment->period->period_name;
                                $hasDate = preg_match('/\d{2}\s\w{3}\s\d{4}/', $name);
                            @endphp

                            @if($hasDate)
                                {{ $name }}
                            @else
                                {{ $name }} ({{ $kpiAssessment->period->start_date->format('d M Y') }} -
                                {{ $kpiAssessment->period->end_date->format('d M Y') }})
                            @endif<br>
                        </strong></span>
                    </div>

                    @php
                        $user = Auth::user();
                        $isSelf = $user->employee && $user->employee->id === $kpiAssessment->employee_id;
                        $isSupervisor = $user->id === $kpiAssessment->primary_supervisor_id;

                        $canEditTarget = $isSupervisor && in_array($kpiAssessment->status, ['Penyesuaian Target']);
                        $canEditSelf = $isSelf && $kpiAssessment->status == 'Penilaian Diri';
                        $canEditSupervisor = $isSupervisor && $kpiAssessment->status == 'Penilaian Atasan Langsung';
                        $isEditable = $canEditTarget || $canEditSelf || $canEditSupervisor;
                    @endphp

                    {{-- Info Box --}}
                    @if ($canEditTarget)
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Target and Weight Adjustment</h6>
                            <span>As a supervisor, please adjust the targets and weights. Once saved, the employee will be able to
                                perform the self-assessment.</span>
                        </div>
                    @elseif($kpiAssessment->status == 'Penyesuaian Target')
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Waiting for Supervisor Adjustment</h6>
                            <span>This assessment is waiting for target adjustment by the supervisor. You cannot perform
                                self-assessment yet.</span>
                        </div>
                    @elseif($canEditSelf)
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Self-Assessment</h6>
                            <span>Please fill in your achievements and scores. Use the <b>"Save Draft"</b> button to save
                                temporarily or <b>"Submit"</b> to send your assessment to your supervisor.</span>
                        </div>
                    @elseif($canEditSupervisor)
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Supervisor Assessment</h6>
                            <span>Please fill in the achievements and scores for your subordinate. Use the <b>"Save Draft"</b>
                                button to save temporarily or <b>"Submit"</b> to finalize your assessment.</span>
                        </div>
                    @endif

                    <form action="{{ route('kpi-assessments.update', $kpiAssessment->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Assessment Table --}}
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-custom text-center align-middle">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="align-middle">KPI Indicator</th>
                                        <th rowspan="2" class="align-middle">Weight</th>
                                        <th rowspan="2" class="align-middle">Target</th>
                                        <th colspan="2">Self-Assessment</th>
                                        <th colspan="2">Supervisor Assessment</th>
                                    </tr>
                                    <tr>
                                        <th>Achievement</th>
                                        <th>Score</th>
                                        <th>Achievement</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($kpiAssessment->assessmentItems as $item)
                                        @php
                                            $selfScore = $item->scores->firstWhere('participant.role', 'self');
                                            $supervisorScore = $item->scores->firstWhere('participant.role', 'direct_supervisor');
                                        @endphp
                                        <tr>
                                            {{-- KPI Indicator --}}
                                            <td class="text-start">
                                                <strong>{{ $item->indicator->indicator_name }}</strong><br>
                                                <small class="kpi-table-note">{{ $item->indicator->description }}</small>
                                            </td>

                                            {{-- Weight --}}
                                            <td>
                                                @if ($canEditTarget)
                                                    <input type="number" step="0.01" name="items[{{ $item->id }}][weight]"
                                                        class="form-control"
                                                        value="{{ old('items.' . $item->id . '.weight', $item->weight) }}" required>
                                                @else
                                                    {{ $item->weight }}%
                                                @endif
                                            </td>

                                            {{-- Target --}}
                                            <td>
                                                @if ($canEditTarget)
                                                    <input type="text" name="items[{{ $item->id }}][target]" class="form-control"
                                                        value="{{ old('items.' . $item->id . '.target', $item->target) }}" required>
                                                @else
                                                    {{ $item->target }} {{ $item->indicator->measurement_unit }}
                                                @endif
                                            </td>

                                            {{-- Self --}}
                                            <td>
                                                @if ($canEditSelf)
                                                    <input type="text" name="items[{{ $item->id }}][achievement_input]"
                                                        class="form-control"
                                                        value="{{ old('items.' . $item->id . '.achievement_input', $selfScore->achievement_input ?? '') }}">
                                                @else
                                                    {{ $selfScore->achievement_input ?? '-' }}
                                                @endif
                                            </td>
                                            <td>
                                                {{-- Skor otomatis, bukan input --}}
                                                {{ $selfScore->score ?? '-' }}
                                            </td>

                                            {{-- Supervisor --}}
                                            <td>
                                                @if ($canEditSupervisor)
                                                    <input type="text" name="items[{{ $item->id }}][achievement_input]"
                                                        class="form-control"
                                                        value="{{ old('items.' . $item->id . '.achievement_input', $supervisorScore->achievement_input ?? '') }}">
                                                @else
                                                    {{ $supervisorScore->achievement_input ?? '-' }}
                                                @endif
                                            </td>
                                            <td>
                                                {{-- Skor otomatis, bukan input --}}
                                                {{ $supervisorScore->score ?? '-' }}
                                            </td>
                                        </tr>

                                        {{-- FORM RULES SAAT PENYESUAIAN TARGET --}}
                                        @if ($canEditTarget)
                                            <tr>
                                                <td colspan="7" class="text-start">
                                                    <div class="mb-2"><strong>Scoring Rules</strong></div>
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Operator</th>
                                                                <th>Value 1</th>
                                                                <th>Value 2</th>
                                                                <th>Score</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $rules = old("items.{$item->id}.rules", $item->scoringRules->toArray());
                                                            @endphp
                                                            @forelse ($rules as $ruleIndex => $rule)
                                                                <tr>
                                                                    <td>
                                                                        <select
                                                                            name="items[{{ $item->id }}][rules][{{ $ruleIndex }}][operator]"
                                                                            class="form-control" required>
                                                                            @foreach(['<', '<=', '=', '>=', '>', 'between'] as $op)
                                                                                <option value="{{ $op }}" {{ ($rule['operator'] ?? '') == $op ? 'selected' : '' }}>
                                                                                    {{ $op }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" step="0.01"
                                                                            name="items[{{ $item->id }}][rules][{{ $ruleIndex }}][value1]"
                                                                            class="form-control" value="{{ $rule['value1'] ?? '' }}"
                                                                            required>
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" step="0.01"
                                                                            name="items[{{ $item->id }}][rules][{{ $ruleIndex }}][value2]"
                                                                            class="form-control" value="{{ $rule['value2'] ?? '' }}">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" step="0.01"
                                                                            name="items[{{ $item->id }}][rules][{{ $ruleIndex }}][score]"
                                                                            class="form-control" value="{{ $rule['score'] ?? '' }}"
                                                                            required>
                                                                    </td>
                                                                    <td>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-danger remove-rule">x</button>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center">No rules defined</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                    <button type="button" class="btn btn-sm btn-primary add-rule"
                                                        data-item="{{ $item->id }}">
                                                        + Add Rule
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Notes --}}
                        <div class="form-group mt-3">
                            <label for="notes">Additional Notes:</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" {{ !$isEditable ? 'readonly' : '' }}>{{ old('notes') }}</textarea>
                        </div>

                        {{-- Buttons --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-buttons-container">

                                    @php
                                        use Carbon\Carbon;

                                        $now = Carbon::now();
                                        $periodEnd = $kpiAssessment->period->end_date->endOfDay();
                                        $hoursRemaining = $now->diffInHours($periodEnd, false);
                                        $daysRemaining = $hoursRemaining / 24; // hasil bisa desimal
                                    @endphp

                                    @if ($canEditTarget)
                                        <a href="{{ route('kpi-assessments.index') }}" class="btn btn-cancel">Cancel</a>
                                        <button type="submit" class="btn btn-submit">Save</button>
                                        
                                    @elseif($canEditSelf || $canEditSupervisor)
                                        <a href="{{ route('kpi-assessments.index') }}" class="btn btn-cancel">Cancel</a>
                                        <button type="submit" name="action" value="save_draft" class="btn btn-secondary">Save Draft</button>
                                        <button type="submit" name="action" value="submit" class="btn btn-submit has-tooltip"
                                        @if($daysRemaining > 5) disabled @endif>Submit</button>
                                    @else
                                        <a href="{{ route('kpi-assessments.index') }}" class="btn btn-cancel">Cancel</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    // === Tooltip Script ===
    const daysRemaining = @json($daysRemaining);
    const periodEnd = @json($kpiAssessment->period->end_date->endOfDay()->toIso8601String()); // kirim ke JS dalam format ISO

    const tooltip = document.createElement("div");
    tooltip.className = "tooltip-submit";
    tooltip.style.opacity = 0;
    tooltip.style.position = "absolute";
    document.body.appendChild(tooltip);

    const submitBtn = document.querySelector(".btn-submit.has-tooltip[disabled]");

    if (submitBtn) {
        // Ambil waktu sekarang & waktu mulai boleh submit (5 hari sebelum akhir periode)
        const now = new Date();
        const endDate = new Date(periodEnd);
        const startAllowed = new Date(endDate.getTime() - (5 * 24 * 60 * 60 * 1000)); // minus 5 hari

        // Hitung sisa waktu sampai periode boleh disubmit
        const diffMs = startAllowed - now;
        const diffHours = diffMs / (1000 * 60 * 60);
        const fullDays = Math.floor(diffHours / 24);
        const hours = Math.round(diffHours % 24);

        // Format waktu dan tanggal mulai submit
        const startDateStr = startAllowed.toLocaleString('id-ID', {
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });

        let timeText = "";
        if (diffMs <= 0) {
            timeText = "sudah bisa digunakan.";
        } else {
            if (fullDays > 0) timeText += `${fullDays} hari`;
            if (hours > 0) timeText += (timeText ? " " : "") + `${hours} jam`;
            if (!timeText) timeText = "kurang dari 1 jam";
        }

        submitBtn.addEventListener("mouseenter", () => {
            const rect = submitBtn.getBoundingClientRect();

            tooltip.innerHTML = diffMs <= 0
                ? `Tombol submit sudah aktif.`
                : `Tombol submit akan aktif pada <b>${startDateStr}</b> (sekitar <b>${timeText}</b> lagi). Gunakan tombol <b>"Save Draft"</b> untuk menyimpan penilaian Anda sementara.`;

            tooltip.style.opacity = 1;
            tooltip.style.left = (rect.left + window.scrollX + (rect.width/2) - (tooltip.offsetWidth/2)) + "px";
            tooltip.style.top = (rect.top + window.scrollY - tooltip.offsetHeight - 8) + "px";
        });

        submitBtn.addEventListener("mouseleave", () => {
            tooltip.style.opacity = 0;
        });
    }

    // === Add / Remove Rule Script ===
    document.querySelectorAll(".add-rule").forEach(button => {
        button.addEventListener("click", function() {
            const itemId = this.dataset.item;
            const tbody = this.closest("td").querySelector("tbody");

            const index = tbody.querySelectorAll("tr").length;

            const newRow = document.createElement("tr");
            newRow.innerHTML = `
                <td>
                    <select name="items[${itemId}][rules][${index}][operator]" class="form-control" required>
                        <option value="<"><</option>
                        <option value="<="><=</option>
                        <option value="=">=</option>
                        <option value=">=">>=</option>
                        <option value=">">></option>
                        <option value="between">between</option>
                    </select>
                </td>
                <td><input type="number" step="0.01" name="items[${itemId}][rules][${index}][value1]" class="form-control" required></td>
                <td><input type="number" step="0.01" name="items[${itemId}][rules][${index}][value2]" class="form-control"></td>
                <td><input type="number" step="0.01" name="items[${itemId}][rules][${index}][score]" class="form-control" required></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-rule">x</button></td>
            `;
            tbody.appendChild(newRow);
        });
    });

    // pakai event delegation biar row baru juga bisa dihapus
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("remove-rule")) {
            e.target.closest("tr").remove();
        }
    });
});
</script>
@endpush
