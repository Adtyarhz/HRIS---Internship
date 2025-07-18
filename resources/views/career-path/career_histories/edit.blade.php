@extends('layouts.admin')

@section('title', 'Career Path')
@section('header_icon', 'material-symbols--work-outline-01')
@section('content_header', 'Careers Administration')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/career-path.css') }}">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="form-content-container">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    action="{{ isset($careerHistory) ? route('employees.career_histories.update', [$employee, $careerHistory]) : route('employees.career_histories.store', $employee) }}"
                    method="POST" id="careerHistoryForm">
                    @csrf
                    @if (isset($careerHistory))
                        @method('PUT')
                    @endif

                    @include('career-path.career_histories._form', [
                        'employee' => $employee,
                        'positions' => $positions,
                        'divisions' => $divisions,
                        'careerHistory' => $careerHistory,
                    ])

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-buttons-container">
                                @if (isset($careerHistory))
                                    <button type="button" class="btn btn-delete" data-toggle="modal" data-target="#deleteModal">Delete</button>
                                @endif
                                <a href="{{ route('employees.career_histories.index', $employee) }}" class="btn btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-submit">Save</button>
                            </div>
                        </div>
                    </div>
                </form>

                @if (isset($careerHistory))
                    <!-- Modal -->
                    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Penghapusan</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Apakah Anda yakin ingin menghapus riwayat karir ini?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    <form action="{{ route('employees.career_histories.destroy', [$employee, $careerHistory]) }}" method="POST" id="deleteCareerHistoryForm">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
