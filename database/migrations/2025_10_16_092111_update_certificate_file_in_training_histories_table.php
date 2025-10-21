<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('training_histories', function (Blueprint $table) {
            // Jika sebelumnya kolom certificate_number masih ada, kita rename dulu
            if (Schema::hasColumn('training_histories', 'certificate_number')) {
                $table->renameColumn('certificate_number', 'certificate_file');
            }

            // Pastikan kolom certificate_file sesuai dengan tipe pada tabel certifications
            $table->string('certificate_file')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('training_histories', function (Blueprint $table) {
            $table->renameColumn('certificate_file', 'certificate_number');
        });
    }
};
