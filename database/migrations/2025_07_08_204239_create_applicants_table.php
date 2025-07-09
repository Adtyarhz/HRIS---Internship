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
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('address');
            $table->string('resume_file');
            $table->string('applied_position');
            $table->string('last_education');
            $table->string('origin');
            $table->string('gpa_score');
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onDelete('set null');
            $table->timestamps();
        });      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
