<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // ✅ Kolom untuk data penonaktifan (deactivation)
            $table->date('deactivation_date')->nullable()->after('separation_date');
            $table->string('termination_reason')->nullable()->after('deactivation_date');
            $table->text('termination_notes')->nullable()->after('termination_reason');
        });
    }

    /**
     * Kembalikan perubahan.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['deactivation_date', 'termination_reason', 'termination_notes']);
        });
    }
};
