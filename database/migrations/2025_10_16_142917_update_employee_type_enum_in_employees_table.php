<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1️⃣ Ubah kolom enum lama menjadi VARCHAR dulu supaya bisa diupdate bebas
        DB::statement("ALTER TABLE employees MODIFY COLUMN employee_type VARCHAR(50)");

        // 2️⃣ Update nilai lama ke format baru
        DB::statement("
            UPDATE employees 
            SET employee_type = CASE
                WHEN employee_type = 'Kontrak' THEN 'PKWT'
                WHEN employee_type = 'Fulltime' THEN 'PKWTT'
                WHEN employee_type = 'Masa Percobaan' THEN 'Probation'
                WHEN employee_type = 'Magang' THEN 'Intern'
                ELSE 'PKWT'
            END
        ");

        // 3️⃣ Baru ubah kembali ke ENUM baru
        DB::statement("
            ALTER TABLE employees 
            MODIFY COLUMN employee_type ENUM(
                'PKWT',
                'PKWTT',
                'Probation',
                'Intern'
            ) NOT NULL DEFAULT 'PKWT'
        ");
    }

    public function down(): void
    {
        // rollback: ubah jadi enum lama
        DB::statement("ALTER TABLE employees MODIFY COLUMN employee_type VARCHAR(50)");

        DB::statement("
            UPDATE employees 
            SET employee_type = CASE
                WHEN employee_type = 'PKWT' THEN 'Kontrak'
                WHEN employee_type = 'PKWTT' THEN 'Fulltime'
                WHEN employee_type = 'Probation' THEN 'Masa Percobaan'
                WHEN employee_type = 'Intern' THEN 'Magang'
                ELSE 'Kontrak'
            END
        ");

        DB::statement("
            ALTER TABLE employees 
            MODIFY COLUMN employee_type ENUM(
                'Kontrak',
                'Magang',
                'Masa Percobaan',
                'Fulltime'
            ) NOT NULL DEFAULT 'Kontrak'
        ");
    }
};
