<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_schedules', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropColumn('interviewer');

            // Tambahkan kolom baru (wajib diisi)
            $table->foreignId('interviewer_id')
                ->constrained('users')
                ->cascadeOnDelete(); // opsional: hapus jadwal jika user interviewer dihapus
        });
    }

    public function down(): void
    {
        Schema::table('interview_schedules', function (Blueprint $table) {
            // Kembalikan ke kondisi awal
            $table->dropConstrainedForeignId('interviewer_id');
            $table->string('interviewer');
        });
    }
};
