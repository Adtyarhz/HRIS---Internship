@extends('layouts.admin')

@section('title', 'Employee Request Detail')

@section('content_header')
    <div class="header-with-icon">
        <iconify-icon icon="charm:git-request" width="24" height="24"></iconify-icon>
        Employee Request
    </div>
@endsection

@section('content')
<div class="container">
    <h1>Detail of Employee Data Change Request</h1>

    {{-- Flash message --}}
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    {{-- Employee info --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3 fw-bold">Employee :</div>
                <div class="col-md-9">{{ $editRequest->employee->full_name ?? '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3 fw-bold">Status :</div>
                <div class="col-md-9">{{ ucfirst($editRequest->status) }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3 fw-bold">Method :</div>
                <div class="col-md-9">{{ ucfirst($editRequest->method) }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3 fw-bold">Submitted At :</div>
                <div class="col-md-9">
                    {{ $editRequest->requested_at ? \Carbon\Carbon::parse($editRequest->requested_at)->format('d-m-Y H:i') : '-' }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 fw-bold">Approved By :</div>
                <div class="col-md-9">{{ $editRequest->approvedBy->name ?? '-' }}</div>
            </div>
        </div>
    </div>

    <h4>Data Changes</h4>

    {{-- DATA CHANGE LOGIC (UNCHANGED) --}}
    @php
        $originalData = is_string($editRequest->original_data) 
            ? json_decode($editRequest->original_data, true) ?? [] 
            : ($editRequest->original_data ?? []);

        $changedData = is_string($editRequest->changed_data) 
            ? json_decode($editRequest->changed_data, true) ?? [] 
            : ($editRequest->changed_data ?? []);

        function formatValue($field, $value) {
            if (is_array($value)) {
                return implode(', ', array_map(function($v, $k) {
                    return is_array($v) ? $k . ': ' . json_encode($v) : $k . ': ' . $v;
                }, $value, array_keys($value)));
            }
            if (!empty($value) && str_contains($field, 'date')) {
                try {
                    return \Carbon\Carbon::parse($value)->format('Y-m-d');
                } catch (\Exception $e) {
                    return $value;
                }
            }
            return $value ?? '-';
        }
    @endphp

    @if(!empty($changedData))
        @php
            $isFlat = true;
            foreach ($changedData as $value) {
                if (is_array($value)) { $isFlat = false; break; }
            }
        @endphp

        @if($isFlat)
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-secondary text-white text-center">Data</div>
                <div class="card-body">
                    <table class="table table-sm table-bordered text-center custom-table">
                        <thead class="table-secondary">
                            <tr>
                                <th>Field</th>
                                <th>Old Data</th>
                                <th>New Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($changedData as $field => $newValue)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                    <td>{{ formatValue($field, $originalData[$field] ?? '-') }}</td>
                                    <td>{{ formatValue($field, $newValue ?? '-') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
           @foreach($changedData as $table => $records)
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-secondary text-white">
            {{ ucfirst(str_replace('_', ' ', $table)) }}
        </div>
        <div class="card-body">
            @php 
                if (is_array($records)) {
                    $firstValue = reset($records);
                    $isNested = is_array($firstValue) && array_keys($records) !== range(0, count($records) - 1);
                } else {
                    $firstValue = $records;
                    $isNested = false;
                }
            @endphp

            @if($isNested)
                @foreach($records as $recordId => $fields)
                    <h6>Record ID: {{ $recordId }}</h6>
                    <table class="table table-sm table-bordered text-center custom-table">
                        <thead class="table-secondary">
                            <tr>
                                <th>Field</th>
                                <th>Old Data</th>
                                <th>New Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fields as $field => $newValue)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                    <td>{{ formatValue($field, $originalData[$table][$recordId][$field] ?? '-') }}</td>
                                    <td>{{ formatValue($field, $newValue ?? '-') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @else
                <table class="table table-sm table-bordered text-center custom-table">
                    <thead class="table-secondary">
                        <tr>
                            <th>Field</th>
                            <th>Old Data</th>
                            <th>New Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(is_array($records))
                            @foreach($records as $field => $newValue)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                    <td>{{ formatValue($field, $originalData[$table][$field] ?? '-') }}</td>
                                    <td>{{ formatValue($field, $newValue ?? '-') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $table)) }}</td>
                                <td>{{ formatValue($table, $originalData[$table] ?? '-') }}</td>
                                <td>{{ formatValue($table, $records ?? '-') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endforeach

        @endif
    @else
        <p class="text-muted text-center">No data changes.</p>
    @endif

    {{-- Buttons --}}
    <div class="d-flex justify-content-end fixed-bottom mb-3 me-3">
    @if($editRequest->status === 'waiting')
        <form action="{{ route('employee-edit-requests.approve', $editRequest->id) }}" method="POST" class="d-inline me-2">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">Approve</button>
        </form>
        <form action="{{ route('employee-edit-requests.reject', $editRequest->id) }}" method="POST" class="d-inline me-2">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
        </form>
    @endif
    <a href="{{ route('employee-edit-requests.index') }}" class="btn btn-secondary btn-sm">Back</a>
</div>
</div>
@endsection

@push('styles')
<style>
    .custom-table tbody tr {
        background-color: #f9f9f9;
    }
    .custom-table tbody tr:nth-child(even) {
        background-color: #f1f1f1;
    }
</style>
@endpush
