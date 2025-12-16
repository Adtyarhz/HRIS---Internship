@extends('layouts.admin')

@section('title', 'Detail Dokumen Onboarding')

@push('styles')
<style>
    .header-with-icon {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        background: none;
        color: #7b7c7e;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        border: none;
        padding: 0;
    }

    .btn-back:hover {
        color: #000;
        text-decoration: underline;
    }

    .info-row {
        display: flex;
        margin-bottom: 12px;
    }

    .info-label {
        min-width: 140px;
        font-weight: 600;
    }
</style>
@endpush

@section('content_header')
<div class="header-with-icon">
    <i class="fas fa-file-alt"></i>
    <span>Onboarding Management</span>
</div>
@endsection

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 32px; background: #FEFEF9">
    <div style="background: #FFFEF9; border-radius: 10px; outline: 1px rgba(0,0,0,.2) solid; padding: 32px;">

        {{-- TITLE --}}
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <h3 style="font-weight:700; font-size:25px">
                {{ $onboardingDocument->title }}
            </h3>
        </div>

        {{-- BACK --}}
        <a href="{{ route('onboarding.index') }}" class="btn-back mt-1">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>

        {{-- DESCRIPTION --}}
        <div style="margin-top:16px; color:#333">
            {!! nl2br(e($onboardingDocument->description ?? 'Tidak ada deskripsi')) !!}
        </div>

        {{-- INFO --}}
        <div>
            <div class="info-row">
                <div class="info-label">Division</div>
                <div>{{ $onboardingDocument->division->name ?? 'Umum' }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">Status</div>
                <div>
                    @if($onboardingDocument->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Non-Active</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- FILE ATTACHMENT --}}
<div style="display: flex; margin-bottom: 12px">
    <div class="info-label">Attachment</div>
    <div>
        @if ($onboardingDocument->file_path)
            <a href="{{ asset('storage/' . $onboardingDocument->file_path) }}"
               target="_blank"
               title="Click here to view the attachment">
                See File
            </a>
        @else
            -
        @endif
    </div>
</div>

        {{-- ACTION BUTTON --}}
        <div style="display:flex; justify-content:flex-end; gap:12px;">
            <a href="{{ route('onboarding.edit', $onboardingDocument->id) }}"
               class="btn btn-warning">
                Edit
            </a>

            <button onclick="showDeleteModal()" class="btn btn-danger">
                Delete
            </button>
        </div>
    </div>
</div>

{{-- DELETE MODAL --}}
<div id="deleteModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.4); z-index:999;">
    <div style="background:white; padding:24px; border-radius:10px; max-width:400px; margin:15% auto;">
        <h5>Delete Document?</h5>
        <p>Onboarding documents will be permanently deleted.</p>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button onclick="hideDeleteModal()" class="btn btn-secondary">Cancel</button>

            <form action="{{ route('onboarding.destroy', $onboardingDocument->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showDeleteModal() {
        document.getElementById('deleteModal').style.display = 'block';
    }
    function hideDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }
</script>
@endpush

@endsection
