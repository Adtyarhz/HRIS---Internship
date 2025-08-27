<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kpi_assessment_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('assessor_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['self', 'direct_supervisor', 'indirect_supervisor']);
            $table->enum('status', ['Menunggu Penilaian', 'Selesai'])->default('Menunggu Penilaian');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_assessment_participants');
    }
};
