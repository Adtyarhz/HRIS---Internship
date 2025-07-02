@extends('layouts.admin')

@section('title', 'Edit Riwayat Kesehatan Karyawan')
@section('header_icon', 'icon-park-outline--health')
@section('content_header', 'Edit Riwayat Kesehatan')

@section('content')
    <div class="container-fluid">
        {{-- 1. Panggil partial menu tab --}}
        {{-- Pastikan variabel $employee ada saat memanggil view ini --}}
        @include('employees.partials.tab-menu', ['employee' => $employee])

        {{-- 2. Container untuk konten form --}}
        <div class="form-content-container">
            <div class="card-body">
                <h4 class="mb-4">Riwayat Kesehatan untuk: <strong>{{ $employee->full_name }}</strong></h4>
                
                {{-- Form ini akan mengirim data ke HealthRecordController --}}
                <form action="{{ route('health-records.storeOrUpdate', $employee->id) }}" method="POST">
                    @csrf
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-form">
                            <tbody>
                                <tr>
                                    <td class="table-label"><label for="height">Tinggi Badan (cm)</label></td>
                                    <td><input type="number" step="0.01" class="form-control @error('height') is-invalid @enderror" id="height" name="height" value="{{ old('height', $healthRecord->height ?? '') }}">
                                        @error('height') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label"><label for="weight">Berat Badan (kg)</label></td>
                                    <td><input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight" value="{{ old('weight', $healthRecord->weight ?? '') }}">
                                        @error('weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label"><label for="blood_type">Golongan Darah</label></td>
                                    <td>
                                        <select class="form-control @error('blood_type') is-invalid @enderror" id="blood_type" name="blood_type">
                                            <option value="">-- Pilih Golongan Darah --</option>
                                            @php
                                                $bloodTypes = ['A', 'B', 'AB', 'O', 'Tidak Tahu'];
                                                $selectedBloodType = old('blood_type', $healthRecord->blood_type ?? '');
                                            @endphp
                                            @foreach ($bloodTypes as $type)
                                                <option value="{{ $type }}" {{ $selectedBloodType == $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                        @error('blood_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label"><label for="known_allergies">Alergi yang Diketahui</label></td>
                                    <td><textarea class="form-control @error('known_allergies') is-invalid @enderror" id="known_allergies" name="known_allergies" rows="3">{{ old('known_allergies', $healthRecord->known_allergies ?? '') }}</textarea>
                                        @error('known_allergies') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label"><label for="chronic_diseases">Penyakit Kronis</label></td>
                                    <td><textarea class="form-control @error('chronic_diseases') is-invalid @enderror" id="chronic_diseases" name="chronic_diseases" rows="3">{{ old('chronic_diseases', $healthRecord->chronic_diseases ?? '') }}</textarea>
                                        @error('chronic_diseases') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label"><label for="last_checkup_date">Tanggal Checkup Terakhir</label></td>
                                    <td><input type="date" class="form-control @error('last_checkup_date') is-invalid @enderror" id="last_checkup_date" name="last_checkup_date" value="{{ old('last_checkup_date', $healthRecord ? optional($healthRecord->last_checkup_date)->format('Y-m-d') : '') }}">
                                        @error('last_checkup_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </td>
                                </tr>
                                 <tr>
                                    <td class="table-label"><label for="checkup_loc">Lokasi Checkup</label></td>
                                    <td><input type="text" class="form-control @error('checkup_loc') is-invalid @enderror" id="checkup_loc" name="checkup_loc" value="{{ old('checkup_loc', $healthRecord->checkup_loc ?? '') }}">
                                        @error('checkup_loc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label"><label for="price_last_checkup">Biaya Checkup Terakhir</label></td>
                                    <td><input type="number" class="form-control @error('price_last_checkup') is-invalid @enderror" id="price_last_checkup" name="price_last_checkup" value="{{ old('price_last_checkup', $healthRecord->price_last_checkup ?? '') }}">
                                        @error('price_last_checkup') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </td>
                                </tr>
                                <tr>
                                    <td class="table-label"><label for="notes">Catatan Tambahan</label></td>
                                    <td><textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $healthRecord->notes ?? '') }}</textarea>
                                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>

                {{-- Form untuk Hapus Data (jika data sudah ada) --}}
                @if ($healthRecord)
                <div class="mt-4 text-right">
                    <form action="{{ route('health-records.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat kesehatan ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus Riwayat Kesehatan</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
