<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_assessment_item_scoring_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_assessment_item_id')->constrained()->onDelete('cascade');
            $table->string('operator'); // <, <=, =, >=, >, between
            $table->decimal('value1', 15, 2);
            $table->decimal('value2', 15, 2)->nullable();
            $table->decimal('score', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_assessment_item_scoring_rules');
    }
};