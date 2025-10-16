@extends('layouts.admin')

@section('title', 'Division Detail')
@section('header_icon', 'fluent--organization-24-regular-01')
@section('content_header', 'Division Detail')

@section('content')
<div class="container-fluid">
    <div class="card-body">
        <h4>{{ $division->name }}</h4>
        <p><strong>Total Employees:</strong> {{ $division->employees_count ?? 0 }}</p>

        <hr>
        <h5>Employees List</h5>
        <ul>
            @forelse ($division->employees as $emp)
                <li>{{ $emp->full_name }} — {{ $emp->position?->title }}</li>
            @empty
                <li><em>No employees in this division.</em></li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
