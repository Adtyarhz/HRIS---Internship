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
        Schema::create('career_projections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('projected_position_id')->constrained('positions')->onDelete('restrict');
            $table->enum('timeline', ['1 Tahun', '3 Tahun', '5 Tahun']);
            $table->enum('status', ['Direncanakan', 'Disetujui', 'Tercapai', 'Dibatalkan'])->default('Direncanakan');
            $table->text('readiness_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_projections');
    }
};
