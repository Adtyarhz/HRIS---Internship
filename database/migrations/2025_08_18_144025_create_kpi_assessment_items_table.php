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
        Schema::create('kpi_assessment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('kpi_indicator_id')->constrained()->onDelete('restrict');
            $table->decimal('weight', 5, 2);
            $table->string('target');
            $table->string('achievement')->nullable();
            $table->decimal('final_item_score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_assessment_items');
    }
};
