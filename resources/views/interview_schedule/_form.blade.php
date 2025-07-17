<form action="{{ $route }}" method="POST">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    <div class="detail-row">
        <div class="detail-label">Interview Type</div>
        <div class="input-wrapper">
            <select name="interview_type" class="form-control input-small" required>
                @foreach (['User', 'HC', 'Direksi'] as $type)
                    <option value="{{ $type }}" {{ old('interview_type', $schedule->interview_type ?? '') == $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">Date & Time</div>
        <div class="input-wrapper">
            <input type="datetime-local" name="interview_date" class="form-control input-small"
                   value="{{ old('interview_date', isset($schedule) ? \Carbon\Carbon::parse($schedule->interview_date)->format('Y-m-d\\TH:i') : '') }}" required>
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">Interviewer</div>
        <div class="input-wrapper">
            <input type="text" name="interviewer" class="form-control input-small"
                   value="{{ old('interviewer', $schedule->interviewer ?? '') }}" required>
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">Location</div>
        <div class="input-wrapper">
            <input type="text" name="location" class="form-control input-large"
                   value="{{ old('location', $schedule->location ?? '') }}" required>
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">Description</div>
        <div class="input-wrapper">
            <textarea name="result" class="form-control input-large" rows="3">{{ old('result', $schedule->result ?? '') }}</textarea>
        </div>
    </div>

    <div class="action-buttons">
        <button type="submit" class="btn-submit">Save</button>

        @if ($method === 'PUT')
            <a href="{{ route('interview-schedule.show', [$applicant->id, $schedule->id]) }}" class="btn-cancel">Back</a>
        @else
            <a href="{{ route('interview-schedule.index', $applicant->id) }}" class="btn-cancel">Back</a>
        @endif
    </div>
</form>

@push('styles')
<style>
    .detail-row {
        margin-bottom: 16px;
        display: flex;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .detail-label {
        width: 30%;
        font-weight: 600;
        font-size: 14px;
        margin-top: 8px;
    }

    .input-wrapper {
        width: 70%;
    }

    .form-control {
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 8px 10px;
        font-size: 14px;
        font-family: 'Manrope', sans-serif;
        width: 100%;
    }

    .input-small { max-width: 300px; }
    .input-large { max-width: 100%; }

    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 32px;
    }

    .btn-submit {
        background-color: #1B4965;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
    }

    .btn-cancel {
        background-color: #8B1E1E;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
    }
</style>
@endpush