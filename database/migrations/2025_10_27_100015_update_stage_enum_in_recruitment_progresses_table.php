<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Ubah kolom 'stage' jadi VARCHAR sementara agar bisa diubah
        DB::statement("ALTER TABLE recruitment_progresses MODIFY COLUMN stage VARCHAR(50)");

        // 2️⃣ Pastikan data lama valid (hapus nilai yang tak ada di enum baru)
        DB::statement("
            UPDATE recruitment_progresses
            SET stage = 'general_knowledge_test'
            WHERE stage IN ('cv_screening', 'rejected')
        ");

        // 3️⃣ Ubah kolom 'stage' jadi ENUM baru sesuai urutan yang kamu mau
        DB::statement("
            ALTER TABLE recruitment_progresses 
            MODIFY COLUMN stage ENUM(
                'general_knowledge_test',
                'computer_skills_test',
                'hc_interview',
                'user_assessment',
                'bod_interview',
                'offering_letter'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        // Rollback ke enum lama
        DB::statement("ALTER TABLE recruitment_progresses MODIFY COLUMN stage VARCHAR(50)");

        DB::statement("
            ALTER TABLE recruitment_progresses 
            MODIFY COLUMN stage ENUM(
                'cv_screening',
                'general_knowledge_test',
                'user_assessment',
                'hc_interview',
                'bod_interview',
                'offering_letter',
                'rejected'
            ) NOT NULL
        ");
    }
};
