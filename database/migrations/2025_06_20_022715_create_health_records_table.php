<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('height', 5, 1)->nullable();
            $table->decimal('weight', 5, 1)->nullable();
            $table->enum('blood_type', ['A', 'B', 'AB', 'O', '-'])->nullable();
            $table->string('known_allergies')->nullable();
            $table->string('chronic_diseases')->nullable();
            $table->date('last_checkup_date')->nullable();
            $table->text('checkup_loc')->nullable();
            $table->text('price_last_checkup')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};