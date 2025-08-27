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
        Schema::create('kpi_scoring_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_template_item_id')->constrained()->onDelete('cascade');
            $table->enum('operator', ['<', '<=', '=', '>=', '>', 'between']);
            $table->decimal('value1', 15, 2);
            $table->decimal('value2', 15, 2)->nullable();
            $table->decimal('score', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_scoring_rules');
    }
};
