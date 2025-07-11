<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah opsi 'in progress' dan jadikan kolom NOT NULL
        DB::statement("ALTER TABLE recruitment_progresses 
            MODIFY offering_status ENUM('accepted', 'rejected', 'in progress') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum awal (tanpa 'in progress'), masih NOT NULL
        DB::statement("ALTER TABLE recruitment_progresses 
            MODIFY offering_status ENUM('accepted', 'rejected') NOT NULL");
    }
};
