@extends('layouts.admin')

@section('title', 'Detail Pengumuman')

@push('styles')
<style>
    .header-with-icon {
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: 5px;
    }

    .header-with-icon .custom-hamburger {
        margin-right: 6px; /* Jarak antara ikon dan teks */
        width: 35px; /* Diperbesar untuk sesuai dengan font-size teks 24px */
        height: 35px; /* Diperbesar untuk sesuai dengan font-size teks 24px */
        color: #000; /* Warna ikon */
    }

    .announcement-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .btn-back {
        display: inline-flex;
        align-items: center;
        background: none;
        color: #7b7c7eff;
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;
        font-family: initial;
        border: none;
        padding: 0;
        transition: color 0.2s ease;
    }

    .btn-back:hover {
        color: #000; /* Sedikit lebih gelap saat hover */
        text-decoration: underline; /* Efek hover seperti link */
    }
</style>
@endpush

@section('content_header')
    <div class="header-with-icon">
        <svg class="custom-hamburger" width="24" height="24" fill="currentColor" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
        </svg>
        Announcement Management
    </div>
@endsection

@section('content')
@php
   use Carbon\Carbon;
    use App\Models\PollingVote;

    $polling = $announcement->polling;
    $isPolling = strtolower($announcement->announcement_type) === 'polling';
    $isExpired = $isPolling && $polling && $polling->deadline && now()->gt($polling->deadline);

    $user = Auth::user();
    $allowedRoles = ['direksi', 'manager', 'section_head', 'staff_bisnis', 'staff_support'];
    $canVote = in_array($user->role, $allowedRoles);

   $userVote = null;

if ($isPolling && $polling) {
    $userVote = PollingVote::where('created_by', $user->id)
        ->whereHas('pollingOption', function ($q) use ($polling) {
            $q->where('polling_id', $polling->id);
        })->first();
}
@endphp

<div style="width: 100%; max-width: 1200px; margin: 0 auto; padding: 32px; background: #FEFEF9">
    <div style="background: #FFFEF9; border-radius: 10px; outline: 1px rgba(0,0,0,0.2) solid; outline-offset: -1px; padding: 32px; position: relative;">
        {{-- Judul Pengumuman dan Voting Deadline --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap;">
            <h3 style="font-weight: 700; font-size: 25px">{{ $announcement->title }}</h3>
            @if ($isPolling && $polling && $polling->deadline)
                <div style="text-align: right; font-size: 14px">
                    <div>Voting Deadline:</div>
                    <div><strong>{{ Carbon::parse($polling->deadline)->format('d-m-Y H:i') }}</strong></div>
                </div>
            @endif
        </div>

        {{-- Alert jika expired --}}
        @if ($isPolling && $isExpired)
            <div style="margin-top: 1px; margin-bottom: 40px; color: #C70000; font-size: 16px; font-weight: 500; display: flex; align-items: center; gap: 8px">
            <span class="iconify" data-icon="fa6-solid:triangle-exclamation" style="color: #FFC107; font-size: 20px;"></span>
                <span>
                    Polling Has Ended, Download Poll?
                   @auth
    @if (in_array(Auth::user()->role, ['superadmin', 'hc']))
        <a href='{{ route('announcement.export_polling', $announcement->id) }}' style="color: #C70000; text-decoration: underline">
            <span class="iconify" data-icon="fa6-solid:file-arrow-down" style="color: black; font-size: 20px;"></span>
        </a>
    @endif
@endauth
                </span>
            </div>
        @endif
        {{-- Tombol Back hanya untuk Superadmin & HC --}}
@auth
@if (in_array(Auth::user()->role, ['superadmin', 'hc']))
        <a href="{{ route('announcement.index') }}"
           class="action-button btn-back">
            <i class="fas fa-arrow-left" style="margin-right: 6px;"></i> Back to List
        </a>
@endif
@endauth

        {{-- Detail Informasi --}}
        <div style="margin-top: 24px">
            <div style="display: flex; margin-bottom: 12px">
                <div style="min-width: 130px; font-weight: 600">Type</div>
                <div style="font-family: 'Noto Sans Georgian', sans-serif;
                            color: black">{{ ucfirst($announcement->announcement_type) }}</div>
            </div>

            <div style="display: flex; margin-bottom: 12px">
                <div style="min-width: 130px; font-weight: 600">Field</div>
                <div style="max-width: 1000px; font-family: 'Noto Sans Georgian', sans-serif; color: black">
    {!! nl2br(e($announcement->content)) !!}
</div>

            </div>

            <div style="display: flex; margin-bottom: 12px">
                <div style="min-width: 130px; font-weight: 600">Attachment</div>
                <div>
                    @if ($announcement->attachment_file)
                        <a href="{{ asset('storage/announcement/' . $announcement->attachment_file) }}" 
                        target="_blank"
                        title="Click here to view the attachment">Lihat File</a>
                    @else
                        -
                    @endif

                </div>
            </div>

            <div style="display: flex; margin-bottom: 12px">
                <div style="min-width: 130px; font-weight: 600">Label</div>
                <div style=" font-family: 'Noto Sans Georgian', sans-serif;
                            color: red">{{ $announcement->label }}</div>
            </div>

            <div style="display: flex; margin-bottom: 12px">
                <div style="min-width: 130px; font-weight: 600">External Link</div>
                <div>
                    @if ($announcement->external_link)
                        <a href="{{ $announcement->external_link }}" 
                        target="_blank"
                        title="Click here to open the link">
                        {{ $announcement->external_link }}
                        </a>
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>

        {{-- Polling aktif atau sudah expired --}}
        @if ($isPolling && $polling)
            <div style="margin-top: 24px; border-radius: 5px; outline: 1px rgba(0, 0, 0, 0.20) solid; outline-offset: -1px; padding: 16px; width: 100%; max-width: 640px">
                <div style="font-size: 18px; font-weight: 600; margin-bottom: 16px">Temporary Poll Results :</div>
                @foreach ($polling->options as $i => $option)
    @php
        $votedByUser = $userVote && $userVote->polling_option_id == $option->id;
    @endphp
    <div style="display: flex; align-items: center; margin-bottom: 8px;">
        <input type="radio"
               name="option"
               id="option{{ $i }}"
               value="{{ $option->id }}"
               style="margin-right: 8px"
               {{ $votedByUser ? 'checked' : '' }}
               {{ $userVote ? 'disabled' : '' }}>
        <label for="option{{ $i }}">
            {{ $option->option_text }} (Votes: {{ $option->votes->count() }})
            @if ($votedByUser)
                <strong style="color: green; font-size: 13px;"> - You Voted</strong>
            @endif
        </label>
    </div>
@endforeach
            </div>

        <form action="{{ route('polling.vote', $polling->id) }}" method="POST" style="margin-top: 12px" id="pollForm">
    @csrf
    <input type="hidden" name="polling_option_id" id="polling_option_id" value="">

    <button type="submit"
    id="voteBtn"
    @if(!$canVote || $userVote || $isExpired)
        disabled
    @else
        data-allowed="true"
    @endif
    style="background: {{ (!$canVote || $userVote || $isExpired) ? '#999' : '#367FA9' }};
           color: white;
           border: none;
           border-radius: 5px;
           padding: 10px 20px;
           cursor: {{ (!$canVote || $userVote || $isExpired) ? 'not-allowed' : 'pointer' }};">
    Vote
</button>
</form>

            @endif
    
        {{-- Tombol Edit dan Delete --}}
        @if (!isset($from) || $from !== 'dashboard')
    {{-- Jika dari announcement management --}}
    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px">
        <a href="{{ route('announcement.edit', $announcement->id) }}" style="background: #FEC107; color: black; padding: 10px 20px; border-radius: 5px; text-decoration: none">Edit</a>
        <!-- Modal Delete Confirmation -->
        <div id="deleteModal" style="
    display: none;
    position: fixed; /* ⬅ agar tetap dalam area content */
    top: 20%;           /* ⬅ jarak dari atas konten */
    left: 57%;
    transform: translate(-50%, -50%);
    z-index: 1000;
    width: 100%;
">
    <div style="
        margin: 0 auto;
        background: white;
        border-radius: 12px;
        padding: 24px 32px;
        width: 90%;
        max-width: 520px;
        box-shadow: 0 4px 20px rgba(63, 63, 63, 0.2);
    ">
        <!-- Header: Icon + Text -->
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="
                background: #FFEA9F;
                border-radius: 8px;
                width: 48px;
                height: 48px;
                display: flex;
                justify-content: center;
                align-items: center;
            ">
               <span class="iconify" data-icon="fa6-solid:trash-can" style="font-size: 20px; color:#9A3B3B"></span>
            </div>
            <div style="font-size: 18px; font-family: Inter, sans-serif; font-weight: 600; color: black;">
                Are you sure to delete this Announcement?
            </div>
        </div>

        <!-- Actions -->
        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <button onclick="hideDeleteModal()" style="
                width: 160px;
                height: 48px;
                background: #9A3B3B;
                color: white;
                font-size: 16px;
                font-family: Inter, sans-serif;
                font-weight: 500;
                border: none;
                border-radius: 8px;
                outline: 1px rgba(0, 0, 0, 0.2) solid;
                display: flex;
                align-items: center;
                justify-content: center;
            ">Cancel</button>

            <form action="{{ route('announcement.destroy', $announcement->id) }}" method="POST" style="margin: 0">
                @csrf
                @method('DELETE')
                <button type="submit" style="
                    width: 160px;
                    height: 48px;
                    background: #F9FCE6;
                    color: black;
                    font-size: 16px;
                    font-family: Inter, sans-serif;
                    font-weight: 500;
                    border: none;
                    border-radius: 8px;
                    outline: 1px rgba(0, 0, 0, 0.2) solid;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">Yes</button>
            </form>
        </div>
    </div>
</div>
<!-- Overlay -->
<div id="modalOverlay" style="
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.4); /* efek gelap */
    z-index: 999;
">
</div>
        <button type="button" onclick="showDeleteModal()" style="background: #9A3B3B; color: white; padding: 10px 20px; border: none; border-radius: 5px">
    Delete
</button>
    </div>
@else
    {{-- Jika dari dashboard, hanya tombol kembali --}}
    {{-- Tombol Back di kanan bawah --}}
<div style="display: flex; justify-content: flex-end; margin-top: 32px;">
    <a href="{{ route('dashboard') }}"
       style="background: black; color: white; padding: 10px 20px; border: 1px solid black; border-radius: 5px; text-decoration: none; font-weight: 500;">
        Back
    </a>
</div>
@endif
    </div>
</div>
@push('scripts')
<script>
    function showDeleteModal() {
        document.getElementById('deleteModal').style.display = 'block';
        document.getElementById('modalOverlay').style.display = 'block';
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    }

    // Tutup modal jika klik di luar modal
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('deleteModal');
        const overlay = document.getElementById('modalOverlay');

        if (event.target === overlay) {
            hideDeleteModal();
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="option"]');
    const inputHidden = document.querySelector('input[name="polling_option_id"]');
    const voteBtn = document.getElementById('voteBtn');

    function checkVoteEligibility() {
        const selected = Array.from(radios).some(r => r.checked);
        if (selected && voteBtn.hasAttribute('data-allowed')) {
            voteBtn.disabled = false;
            voteBtn.style.background = '#367FA9';
            voteBtn.style.cursor = 'pointer';
        }
    }

    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            inputHidden.value = this.value;
            checkVoteEligibility();
        });
    });
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('input[name="option"]');
        const inputHidden = document.querySelector('input[name="polling_option_id"]');

        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                inputHidden.value = this.value;
            });
        });
    });
</script>
@endpush

@endsection