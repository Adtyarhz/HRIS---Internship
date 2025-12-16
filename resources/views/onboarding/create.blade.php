@extends('layouts.admin')

@section('title', 'Tambah Dokumen Onboarding')

@section('content_header')
    <h1>Tambah Dokumen Onboarding</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('onboarding.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label>Document Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label>File (PDF / DOCX / PPTX)</label>
                <input type="file" name="file" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Division (opsional)</label>
                <select name="division_id" class="form-control">
                    <option value="">General (All Divisions)</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" class="form-check-input" checked>
                <label class="form-check-label">Active</label>
            </div>

            <button class="btn btn-success">Save</button>
            <a href="{{ route('onboarding.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection
