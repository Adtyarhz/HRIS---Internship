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
        Schema::create('kpi_assessment_item_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_assessment_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('participant_id')->constrained('kpi_assessment_participants')->onDelete('cascade');
            $table->string('achievement_input')->nullable();
            $table->decimal('score', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_assessment_item_scores');
    }
};
