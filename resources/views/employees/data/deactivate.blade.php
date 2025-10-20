@extends('layouts.admin')

@section('title', 'Detail Karyawan')
@section('header_icon', 'icon-park-outline--file-staff-one-01')
@section('content_header', 'Employee Information')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Deactivate Employee: {{ $employee->full_name }}</h4>

    <div class="card shadow-sm p-4">
        <form method="POST" action="{{ route('employees.deactivate', $employee) }}">
            @csrf

            {{-- 📅 Tanggal Terakhir Bekerja --}}
            <div class="mb-3">
                <label for="deactivation_date" class="form-label fw-bold">Tanggal Terakhir Bekerja</label>
                <input type="date" 
                       id="deactivation_date" 
                       name="deactivation_date" 
                       class="form-control @error('deactivation_date') is-invalid @enderror"
                       value="{{ old('deactivation_date') }}" 
                       required>
                @error('deactivation_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 📝 Alasan Pemutusan Hubungan Kerja --}}
            <div class="mb-3">
                <label for="termination_reason" class="form-label fw-bold">Alasan Pemutusan Hubungan Kerja</label>
                <select id="termination_reason" 
                        name="termination_reason" 
                        class="form-select @error('termination_reason') is-invalid @enderror" 
                        required>
                    <option value="" disabled selected>-- Pilih Alasan --</option>
                    <option value="Mengundurkan diri" {{ old('termination_reason') == 'Mengundurkan diri' ? 'selected' : '' }}>Mengundurkan diri</option>
                    <option value="Pensiun" {{ old('termination_reason') == 'Pensiun' ? 'selected' : '' }}>Pensiun</option>
                    <option value="Tidak lulus masa percobaan" {{ old('termination_reason') == 'Tidak lulus masa percobaan' ? 'selected' : '' }}>Tidak lulus masa percobaan</option>
                    <option value="Tidak cakap bekerja" {{ old('termination_reason') == 'Tidak cakap bekerja' ? 'selected' : '' }}>Tidak cakap bekerja</option>
                    <option value="Tidak mampu bekerja karena alasan kesehatan" {{ old('termination_reason') == 'Tidak mampu bekerja karena alasan kesehatan' ? 'selected' : '' }}>Tidak mampu bekerja karena alasan kesehatan</option>
                    <option value="Meninggal dunia" {{ old('termination_reason') == 'Meninggal dunia' ? 'selected' : '' }}>Meninggal dunia</option>
                    <option value="Melakukan pelanggaran tata tertib dan disiplin" {{ old('termination_reason') == 'Melakukan pelanggaran tata tertib dan disiplin' ? 'selected' : '' }}>Melakukan pelanggaran tata tertib dan disiplin</option>
                    <option value="Merugikan perusahaan" {{ old('termination_reason') == 'Merugikan perusahaan' ? 'selected' : '' }}>Merugikan perusahaan</option>
                    <option value="Terlibat tindakan pidana" {{ old('termination_reason') == 'Terlibat tindakan pidana' ? 'selected' : '' }}>Terlibat tindakan pidana</option>
                </select>
                @error('termination_reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 📘 Keterangan Tambahan --}}
            <div class="mb-4">
                <label for="termination_notes" class="form-label fw-bold">Keterangan Tambahan</label>
                <textarea id="termination_notes" 
                          name="termination_notes" 
                          rows="3" 
                          class="form-control @error('termination_notes') is-invalid @enderror"
                          placeholder="Tambahkan keterangan tambahan jika diperlukan...">{{ old('termination_notes') }}</textarea>
                @error('termination_notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 🔘 Tombol --}}
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

                <button type="button" 
                        class="btn btn-danger" 
                        onclick="showDeleteModal('deactivate-employee-{{ $employee->id }}')">
                    <i class="fas fa-ban"></i> Deactivate & Save
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Deactivate --}}
<x-delete-modal 
    modalId="deactivate-employee-{{ $employee->id }}" 
    :action="route('employees.deactivate', $employee)" 
    method="POST" 
    title="Konfirmasi Nonaktifkan Karyawan"
    message="Apakah Anda yakin ingin menonaktifkan karyawan ini?"
    iconClass="tab-close-inactive"
/>
@endsection
