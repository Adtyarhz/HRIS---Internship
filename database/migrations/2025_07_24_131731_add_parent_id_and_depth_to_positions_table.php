<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menggunakan Schema::table() untuk memodifikasi tabel yang sudah ada
        Schema::table('positions', function (Blueprint $table) {
            // Menambahkan kolom setelah kolom 'title' agar rapi
            $table->foreignId('parent_id')
                  ->nullable()
                  ->after('title')
                  ->constrained('positions')
                  ->onDelete('set null');
            
            $table->unsignedInteger('depth')
                  ->default(0)
                  ->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Method 'down' ini akan membatalkan perubahan jika Anda perlu rollback
        Schema::table('positions', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['parent_id']);
            
            // Hapus kolom yang ditambahkan
            $table->dropColumn(['parent_id', 'depth']);
        });
    }
};
