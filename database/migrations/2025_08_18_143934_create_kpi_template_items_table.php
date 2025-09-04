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
        Schema::create('kpi_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('kpi_indicator_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('type')->default(1)->comment('1: Routine, 2: Improvement, 3: Breakthrough');
            $table->decimal('weight', 5, 2);
            $table->string('default_target');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_template_items');
    }
};
