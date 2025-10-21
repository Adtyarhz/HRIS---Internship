<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1️⃣ Ubah dulu jadi VARCHAR biar bisa update tanpa error ENUM
        DB::statement("ALTER TABLE recruitment_progresses MODIFY COLUMN contract_type VARCHAR(50) NULL");

        // 2️⃣ Update nilai lama ke versi baru
        DB::statement("
            UPDATE recruitment_progresses 
            SET contract_type = CASE
                WHEN contract_type = 'Contract' THEN 'PKWT'
                WHEN contract_type = 'Full-time' THEN 'PKWTT'
                WHEN contract_type = 'Probation' THEN 'Probation'
                WHEN contract_type = 'Internship' THEN 'Intern'
                ELSE NULL
            END
        ");

        // 3️⃣ Ubah kembali ke ENUM baru agar konsisten dengan employees
        DB::statement("
            ALTER TABLE recruitment_progresses 
            MODIFY COLUMN contract_type ENUM(
                'PKWT',
                'PKWTT',
                'Probation',
                'Intern'
            ) NULL DEFAULT 'PKWT'
        ");
    }

    public function down(): void
    {
        // rollback ke enum lama
        DB::statement("ALTER TABLE recruitment_progresses MODIFY COLUMN contract_type VARCHAR(50) NULL");

        DB::statement("
            UPDATE recruitment_progresses 
            SET contract_type = CASE
                WHEN contract_type = 'PKWT' THEN 'Contract'
                WHEN contract_type = 'PKWTT' THEN 'Full-time'
                WHEN contract_type = 'Probation' THEN 'Probation'
                WHEN contract_type = 'Intern' THEN 'Internship'
                ELSE NULL
            END
        ");

        DB::statement("
            ALTER TABLE recruitment_progresses 
            MODIFY COLUMN contract_type ENUM(
                'Contract',
                'Internship',
                'Probation',
                'Full-time'
            ) NULL DEFAULT 'Contract'
        ");
    }
};
