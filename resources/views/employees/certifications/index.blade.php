@extends('layouts.admin')

@section('title', 'Sertifikasi Karyawan')
@section('header_icon', 'icon-park-outline--certificate')
@section('content_header', 'Sertifikasi Karyawan')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/certification.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    @include('employees.partials.tab-menu', ['employee' => $employee])

    <div class="content-container">
        <div class="card-body">
            <div class="action-header">
    <h4>Employee Certification: <strong>{{ $employee->full_name }}</strong></h4>
</div>

<div class="action-buttons">
    <a href="{{ route('employees.certifications.create', $employee->id) }}" class="btn btn-add-certification">
        <i class="fas fa-plus"></i> Add Certification
    </a>
</div>


            @if ($certifications->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-bordered certification-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Certification Name</th>
                                <th>Issuer</th>
                                <th>Date Obtained</th>
                                <th>Expiry Date</th>
                                <th>Main Certificate</th>
                                <th>File Material</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($certifications as $certification)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('employees.certifications.edit', [$employee->id, $certification->id]) }}"
                                           class="certification-link">
                                           {{ $certification->certification_name }}
                                        </a>
                                    </td>
                                    <td>{{ $certification->issuer }}</td>
                                    <td>{{ $certification->date_obtained->format('d F Y') }}</td>
                                    <td>{{ $certification->expiry_date ? $certification->expiry_date->format('d F Y') : '-' }}</td>
                                    <td>
                                        @if ($certification->certificate_file)
                                            <a href="{{ asset('storage/certifications/main/' . $certification->certificate_file) }}" target="_blank" class="file-link">
                                               <i class="fas fa-file-alt"></i>
                                               {{ Str::afterLast($certification->certificate_file, '_') }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($certification->certificationMaterials->isNotEmpty())
                                            <ul class="file-list">
                                                @foreach ($certification->certificationMaterials as $index => $material)
                                                    <li>
                                                        <a href="{{ asset('storage/certifications/materials/' . $material->file_path) }}" target="_blank" class="file-link">
                                                           <i class="fas fa-file-alt"></i>
                                                           Certification Material File {{ $index + 1 }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            Tidak ada
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="no-data">
                    <p>Belum ada data sertifikasi untuk karyawan ini.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="page-footer">
    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-cancel">Cancel</a>
</div>

</div>
@endsection
