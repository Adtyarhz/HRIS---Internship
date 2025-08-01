<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM(
            'superadmin', 
            'direksi', 
            'manager', 
            'section_head', 
            'staff_bisnis', 
            'staff_support', 
            'hc'
        ) NOT NULL DEFAULT 'staff_support'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM(
            'superadmin', 
            'direksi', 
            'manager', 
            'section_head', 
            'staff_bisnis', 
            'staff_support'
        ) NOT NULL DEFAULT 'staff_support'");
    }
};

