@extends('layouts.admin')

@section('title', 'Edit Dokumen Onboarding')

@section('content_header')
    <h1>Edit Dokumen Onboarding</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('onboarding.update', $onboardingDocument->id) }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>Document Title</label>
                <input type="text" name="title" class="form-control"
                       value="{{ $onboardingDocument->title }}" required>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3">{{ $onboardingDocument->description }}</textarea>
            </div>

            <div class="mb-3">
                <label>Update File (optional)</label>
                <input type="file" name="file" class="form-control">
            </div>

            <div class="mb-3">
                <label>Division</label>
                <select name="division_id" class="form-control">
                    <option value="">General</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}"
                            {{ $onboardingDocument->division_id == $division->id ? 'selected' : '' }}>
                            {{ $division->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" class="form-check-input"
                       {{ $onboardingDocument->is_active ? 'checked' : '' }}>
                <label class="form-check-label">Active</label>
            </div>

            <button class="btn btn-primary">Update</button>
            <a href="{{ route('onboarding.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection
