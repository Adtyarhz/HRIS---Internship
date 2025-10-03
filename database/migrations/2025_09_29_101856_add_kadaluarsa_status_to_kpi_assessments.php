<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kpi_assessments', function (Blueprint $table) {
            // Ubah definisi enum dengan menambahkan Kadaluarsa
            $table->enum('status', [
                'Draft',
                'Penyesuaian Target',
                'Penilaian Diri',
                'Penilaian Atasan Langsung',
                'Penilaian Atasan Tidak Langsung',
                'Selesai',
                'Kadaluarsa',
            ])->default('Penyesuaian Target')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_assessments', function (Blueprint $table) {
            // Rollback ke definisi awal
            $table->enum('status', [
                'Draft',
                'Penyesuaian Target',
                'Penilaian Diri',
                'Penilaian Atasan Langsung',
                'Penilaian Atasan Tidak Langsung',
                'Selesai',
            ])->default('Penyesuaian Target')->change();
        });
    }
};
