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
        Schema::create('career_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('position_id')->constrained()->onDelete('restrict');
            $table->foreignId('division_id')->constrained()->onDelete('restrict');
            $table->string('employee_type'); 
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('type', ['Promosi', 'Mutasi', 'Demosi', 'Awal Masuk'])->comment('Jenis pergerakan karir');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_histories');
    }
};
