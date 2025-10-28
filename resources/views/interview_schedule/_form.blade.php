<form action="{{ $route }}" method="POST">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    {{-- Applicant --}}
    <div class="detail-row">
        <div class="detail-label">Applicant</div>
        <div class="input-wrapper">
            <select name="applicant_id" class="form-control input-small" required>
                <option value="">-- Select Applicant --</option>
                @foreach ($applicants as $app)
                    <option value="{{ $app->id }}"
                        {{ old('applicant_id', $schedule->applicant_id ?? '') == $app->id ? 'selected' : '' }}>
                        {{ $app->full_name ?? 'Applicant #' . $app->id }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Interview Type --}}
    <div class="detail-row">
        <div class="detail-label">Interview Type</div>
        <div class="input-wrapper">
            <select name="interview_type" id="interview_type" class="form-control input-small" required>
                @foreach (['User', 'HC', 'Direksi'] as $type)
                    <option value="{{ $type }}" 
                        {{ old('interview_type', $schedule->interview_type ?? '') == $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Date & Time --}}
    <div class="detail-row">
        <div class="detail-label">Date & Time</div>
        <div class="input-wrapper">
            <input type="datetime-local" name="interview_date" class="form-control input-small"
                value="{{ old('interview_date', isset($schedule) ? \Carbon\Carbon::parse($schedule->interview_date)->format('Y-m-d\TH:i') : '') }}" required>
        </div>
    </div>

    {{-- Interviewer --}}
   <div class="detail-row">
    <div class="detail-label">Interviewer</div>
    <div class="input-wrapper">
        <select name="interviewer_id" id="interviewer_id" class="form-control input-small" required>
            <option value="">-- Select Interviewer --</option>

            {{-- User interviewer (hanya manager atau section head sesuai divisi) --}}
            @foreach ($userInterviewers as $user)
                <option value="{{ $user->id }}" 
                    data-type="User"
                    {{ old('interviewer_id', $schedule->interviewer_id ?? '') == $user->id ? 'selected' : '' }}>
                    {{ $user->employee->full_name ?? $user->name }} ({{ ucfirst($user->role) }})
                </option>
            @endforeach

            {{-- HC interviewer --}}
            @foreach ($hcInterviewers as $hc)
                <option value="{{ $hc->id }}" 
                    data-type="HC"
                    {{ old('interviewer_id', $schedule->interviewer_id ?? '') == $hc->id ? 'selected' : '' }}>
                    {{ $hc->employee->full_name ?? $hc->name }} ({{ ucfirst($hc->role) }})
                </option>
            @endforeach

            {{-- Direksi interviewer --}}
            @foreach ($direksiInterviewers as $dir)
                <option value="{{ $dir->id }}" 
                    data-type="Direksi"
                    {{ old('interviewer_id', $schedule->interviewer_id ?? '') == $dir->id ? 'selected' : '' }}>
                    {{ $dir->employee->full_name ?? $dir->name }} (Direksi)
                </option>
            @endforeach
        </select>
    </div>
</div>


     {{-- Location --}}
    <div class="detail-row">
        <div class="detail-label">Location</div>
        <div class="input-wrapper">
            <select name="location" id="location" class="form-control input-small" required>
                <option value="">-- Select Location Type --</option>
                <option value="onsite" {{ old('location', $schedule->location ?? '') == 'onsite' ? 'selected' : '' }}>Onsite</option>
                <option value="online" {{ old('location', $schedule->location ?? '') == 'online' ? 'selected' : '' }}>Online</option>
            </select>
        </div>
    </div>
    {{-- Meeting Link (for Online) --}}
    <div class="detail-row" id="meeting_link_wrapper" style="display: none;">
        <div class="detail-label">Meeting Link</div>
        <div class="input-wrapper">
            <input type="url" name="meeting_link" id="meeting_link" class="form-control input-large"
                placeholder="https://zoom.us/..." 
                value="{{ old('meeting_link', $schedule->meeting_link ?? '') }}">
            <small class="text-muted">Enter valid URL (Zoom/Google Meet link)</small>
        </div>
    </div>

    {{-- Description --}}
    <div class="detail-row" id="description_wrapper">
        <div class="detail-label">Description</div>
        <div class="input-wrapper">
            <textarea name="result" class="form-control input-large" rows="3">{{ old('result', $schedule->result ?? '') }}</textarea>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="action-buttons">
        <button type="submit" class="btn-submit">Save</button>
        @if ($method === 'PUT')
            <a href="{{ route('interview-schedule.index', [$applicant->id, $schedule->id]) }}" class="btn-cancel">Back</a>
        @else
            <a href="{{ route('interview-schedule.index') }}" class="btn-cancel">Back</a>
        @endif
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('interview_type');
    const interviewerSelect = document.getElementById('interviewer_id');
    const locationSelect = document.getElementById('location');
    const meetingLinkWrapper = document.getElementById('meeting_link_wrapper');
    const descriptionWrapper = document.getElementById('description_wrapper');

    function filterInterviewers() {
        const selectedType = typeSelect.value;
        const options = interviewerSelect.querySelectorAll('option');

        options.forEach(opt => {
            if (!opt.dataset.type) return; // skip placeholder

            // Pastikan selected option selalu terlihat
            if (opt.value === interviewerSelect.value) {
                opt.style.display = 'block';
            } else {
                opt.style.display = (opt.dataset.type === selectedType) ? 'block' : 'none';
            }
        });
    }
    // === Fungsi tampilkan Meeting Link jika Online ===
    function toggleMeetingLink() {
        if (locationSelect.value === 'online') {
            meetingLinkWrapper.style.display = 'flex';
        } else {
            meetingLinkWrapper.style.display = 'none';
        }
    }

     // === Event listeners ===
    typeSelect.addEventListener('change', filterInterviewers);
    locationSelect.addEventListener('change', toggleMeetingLink);

    // === Jalankan saat halaman pertama kali dibuka ===
    filterInterviewers();
    toggleMeetingLink();
});
</script>
@endpush

@push('styles')
<style>
.detail-row { margin-bottom: 16px; display: flex; align-items: flex-start; flex-wrap: wrap; }
.detail-label { width: 30%; font-weight: 600; font-size: 14px; margin-top: 8px; }
.input-wrapper { width: 70%; }
.form-control { border: 1px solid #ccc; border-radius: 6px; padding: 8px 10px; font-size: 14px; font-family: 'Manrope', sans-serif; width: 100%; }
.input-small { max-width: 300px; }
.input-large { max-width: 100%; }
.action-buttons { display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; }
.btn-submit { background-color: #1B4965; color: white; padding: 10px 20px; border: none; border-radius: 6px; transition: background-color 0.3s ease; }
.btn-submit:hover, .btn-submit:focus { background-color: #3d7194ff; color: white; }
.btn-cancel { background-color: #8B1E1E; color: white; padding: 10px 20px; border: none; border-radius: 6px; text-decoration: none; transition: background-color 0.3s ease, color 0.3s ease; }
.btn-cancel:hover, .btn-cancel:focus, .btn-cancel:active { background-color: #c34a4aff; color: white !important; text-decoration: none; }
</style>
@endpush
