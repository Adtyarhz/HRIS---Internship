<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pollings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengumuman_id')->constrained('pengumuman')->onDelete('cascade');
            $table->timestamp('batas_waktu')->nullable(); // bisa pilih kapan berakhir
            $table->timestamps();
        });             
    }

    public function down(): void
    {
        Schema::dropIfExists('pollings');
    }
};
