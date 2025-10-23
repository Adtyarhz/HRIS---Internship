@extends('layouts.admin')

@section('title', 'Divisions')
@section('header_icon', 'ri--organization-chart')
@section('content_header', 'Organization - Divisions')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/form-health.css') }}">
    <style>
        .section-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

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

        .container-fluid {
            padding-bottom: 30px;
        }

        .badge-custom {
            font-size: 12px;
            font-weight: 500;
            padding: 6px 8px;
            border-radius: 8px;
        }

        .add-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            max-width: 240px;
            height: 2.5rem;
            background-color: #9a3b3b;
            color: #fff;
            font-size: 15px;
            font-weight: 500;
            border-radius: 8px;
            text-decoration: none;
            margin-left: auto;
        }

        .add-button:hover {
            background-color: #803030;
            color: #fff;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-info {
            background-color: #0b99b6;
            color: #fff;
            border-radius: 8px;
            padding: 5px 10px;
            text-decoration: none;
            font-size: 13px;
        }

        .btn-info:hover {
            background-color: #098ba5;
            color: #fff;
        }

        /* Pagination */
        .page-footer {
            display: flex;
            justify-content: center;
            padding-top: 0.75rem;
        }

        .pagination {
            display: flex;
            align-items: center;
            list-style: none;
            background-color: #f3efe2;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            padding: 0 0.25rem;
            height: 2.5rem;
        }

        .pagination li {
            margin: 0 0.25rem;
        }

        .pagination li a,
        .pagination li span {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            color: var(--text-dark);
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            border: none;
            background: transparent;
            cursor: pointer;
        }

        .pagination li.active {
            background-color: #fffdeb;
            border: 1px solid rgba(0, 0, 0, 0.2);
            height: 2.5rem;
            line-height: 2.5rem;
            margin: 0;
            padding: 0 0.25rem;
        }

        .pagination li.active span {
            font-weight: 600;
        }

        .pagination li.disabled span {
            color: #6c757d;
            cursor: default;
        }
    </style>
@endpush

@section('content-wrapper')
    @include('organization.partials.tab-menu')

    <section class="content">
        <div class="container-fluid">
            <div class="form-content-container">
                <div class="card-body">

                    {{-- Section Title + Add Button --}}
                    <div class="section-title d-flex justify-content-between align-items-center">
                        Division List
                        @if (in_array(auth()->user()->role, ['superadmin', 'hc']))
                            <a href="{{ route('organization.division.create') }}" class="add-button">
                                <i class="fas fa-plus"></i>Add New Division
                            </a>
                        @endif
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-custom text-center align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Division Name</th>
                                    <th>Total Employees</th>
                                    <th>Total Career Histories</th>
                                    @if (in_array(auth()->user()->role, ['superadmin', 'hc']))
                                        <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($divisions as $index => $division)
                                    <tr>
                                        <td>{{ $loop->iteration + ($divisions->firstItem() - 1) }}</td>
                                        <td><strong>{{ $division->name }}</strong></td>
                                        <td>
                                            <span class="badge-custom bg-info text-dark">
                                                {{ $division->employees_count }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-custom bg-secondary text-dark">
                                                {{ $division->career_histories_count }}
                                            </span>
                                        </td>
                                        @if (in_array(auth()->user()->role, ['superadmin', 'hc']))
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="{{ route('organization.division.edit', $division->id) }}" class="btn-info">
                                                        <i class="fas fa-pen"></i> Edit
                                                    </a>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ in_array(auth()->user()->role, ['superadmin', 'hc']) ? 5 : 4 }}"
                                            class="text-muted text-center">No divisions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if ($divisions->hasPages())
                        <footer class="page-footer">
                            {{ $divisions->withQueryString()->links('vendor.pagination.custom') }}
                        </footer>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
