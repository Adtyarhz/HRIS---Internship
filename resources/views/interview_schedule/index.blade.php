@extends('layouts.admin')

@section('title', 'Interview Schedule')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<style>
    #calendar { max-width: 100%; margin: 0 auto; background: #fffdf5; border-radius: 10px; padding: 10px; }
    .btn-add {
        background-color: #9A3B3B;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .btn-add:hover {
        background-color: #7a2f2f;
        color: white;              /* biar teks tetap putih */
        text-decoration: none;     /* hilangkan underline */
    }
    .fc-event { cursor: pointer; background-color: #b44343; border: none; color: white; }
    .fc-toolbar-title { font-family: 'Manrope', sans-serif; font-weight: bold; color: #333; }
    .week-list { margin-top: 30px; background-color: #faf6e9; padding: 20px; border-radius: 10px; }
    .week-item { border-bottom: 1px solid #ddd; padding: 10px 0; }
    .week-item:last-child { border-bottom: none; }
    .week-item h5 { margin: 0; font-weight: bold; color: #9a3b3b; }
    .week-item small { color: #555; }
</style>
@endpush

@section('content_header')
    <div class="header-with-icon d-flex align-items-center">
        <!-- Ikon Recruitment -->
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 2048 2048" class="mr-2">
            <path fill="currentColor"
                d="M2048 1280v768H1024v-768h256v-256h512v256zm-640 0h256v-128h-256zm512 384h-128v128h-128v-128h-256v128h-128v-128h-128v256h768zm0-256h-768v128h768zm-355-512q-54-61-128-94t-157-34q-80 0-149 30t-122 82t-83 123t-30 149q0 92-41 173t-116 136q45 23 84 53t73 68v338q0-79-30-149t-82-122t-123-83t-149-30q-80 0-149 30t-122 82t-83 123t-30 149H0q0-73 20-141t57-129t90-108t118-81q-74-54-115-135t-42-174q0-79 30-149t82-122t122-83t150-30q92 0 173 41t136 116q38-75 97-134t135-98q-74-54-115-135t-42-174q0-79 30-149t82-122t122-83t150-30q79 0 149 30t122 82t83 123t30 149q0 92-41 173t-116 136q68 34 123 85t93 118zM512 1408q53 0 99-20t82-55t55-81t20-100q0-53-20-99t-55-82t-81-55t-100-20q-53 0-99 20t-82 55t-55 81t-20 100q0 53 20 99t55 82t81 55t100 20m512-1024q0 53 20 99t55 82t81 55t100 20q53 0 99-20t82-55t55-81t20-100q0-53-20-99t-55-82t-81-55t-100-20q-53 0-99 20t-82 55t-55 81t-20 100"/>
        </svg>
        <h1 class="header-title mb-0">Recruitment Applicant</h1>
    </div>
@endsection
@section('content-wrapper')
    @include('recruitment.tabs')

    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">
                    {{-- Section Title --}}
                   <div class="header-with-icon mb-4 d-flex align-items-center justify-content-between">
    <h1 class="header-title mb-0">Interview Schedule Overview</h1>

    @if ($canAddInterview ?? false)
        <a href="{{ route('interview-schedule.create') }}" class="btn-add" style="margin-left: auto;">+ Add Interview Schedule
        </a>
    @endif
</div>

<div id="calendar"></div>

{{-- Modal Detail Jadwal --}}
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="eventModalLabel">Interview Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p><strong>Applicant:</strong> <span id="modalApplicant"></span></p>
                <p><strong>Type:</strong> <span id="modalType"></span></p>
                <p><strong>Date:</strong> <span id="modalDate"></span></p>
                <p><strong>Interviewer:</strong> <span id="modalInterviewer"></span></p>
                <p><strong>Location:</strong> <span id="modalLocation"></span></p>
            </div>

            <div class="modal-footer">
                {{-- ✏️ Tombol Edit hanya untuk role hc & superadmin --}}
                @if(in_array(auth()->user()->role, ['hc', 'superadmin']))
                    <a href="#" id="editButton" class="btn btn-sm btn-warning">Edit</a>
                @endif
                @if(in_array(auth()->user()->role, ['hc', 'superadmin']))
                {{-- 🗑️ Tombol Delete (jika ingin juga dibatasi, tinggal bungkus @if yang sama) --}}
                <form id="deleteForm" action="#" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                        Delete
                    </button>
                </form>
                @endif
                {{-- ✅ Tombol Close aktif dan bisa menutup modal --}}
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- List 7 hari ke depan --}}
<div class="week-list mt-5">
    <h3>Upcoming Interviews (Next 7 Days)</h3>
    @forelse ($upcomingSchedules as $schedule)
        <div class="week-item">
            <h5>{{ $schedule->applicant?->full_name ?? '-' }} ({{ $schedule->interview_type ?? '-' }})</h5>
            <small>
                📅 {{ \Carbon\Carbon::parse($schedule->interview_date)->format('d M Y, H:i') }} — 
                👤 {{ $schedule->interviewer?->name ?? '-' }} — 
                📍 {{ $schedule->location ?? '-' }}
            </small>
        </div>
    @empty
        <p class="text-muted">No interviews scheduled for the next week.</p>
    @endforelse
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const events = @json($events);

    // Template route edit dan delete
    const editRouteTemplate = "{{ route('interview-schedule.edit', ['schedule' => ':id']) }}";
    const deleteRouteTemplate = "{{ route('interview-schedule.destroy', ['schedule' => ':id']) }}";

    const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 'auto',
    locale: 'id', // gunakan lokal bahasa Indonesia
    events: events,
    eventTimeFormat: { // ubah format jam menjadi 24 jam
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    },
    eventClick: function(info) {
        const e = info.event;

        // Format waktu dalam 24 jam
        const date = new Date(e.start);
        const formattedDate = date.toLocaleString('id-ID', {
            dateStyle: 'full',
            timeStyle: 'short',
            hour12: false
        });

        document.getElementById('modalApplicant').textContent = e.title;
        document.getElementById('modalType').textContent = e.extendedProps.type ?? '-';
        document.getElementById('modalDate').textContent = formattedDate;
        document.getElementById('modalInterviewer').textContent = e.extendedProps.interviewer ?? '-';
        document.getElementById('modalLocation').textContent = e.extendedProps.location ?? '-';

        const editButton = document.getElementById('editButton');
        const deleteForm = document.getElementById('deleteForm');

        if (editButton) editButton.href = editRouteTemplate.replace(':id', e.id);
        if (deleteForm) deleteForm.action = deleteRouteTemplate.replace(':id', e.id);

        new bootstrap.Modal(document.getElementById('eventModal')).show();
    }
});

    calendar.render();
});
</script>
@endpush
