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
        Schema::create('kpi_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('kpi_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('primary_supervisor_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['Draft', 'Penyesuaian Target', 'Penilaian Diri', 'Penilaian Atasan Langsung', 'Penilaian Atasan Tidak Langsung', 'Selesai'])->default('Penyesuaian Target');
            $table->decimal('final_score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_assessments');
    }
};