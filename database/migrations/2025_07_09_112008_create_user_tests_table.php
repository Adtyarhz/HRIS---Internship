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
        Schema::create('user_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_progress_id')->constrained('recruitment_progresses')->onDelete('cascade');
            $table->string('test_name');
            $table->string('score')->nullable();
            $table->enum('status', ['pending', 'done'])->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('test_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tests');
    }
};
