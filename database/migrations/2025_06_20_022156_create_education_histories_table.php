<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('education_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('education_level', ['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3']);
            $table->string('institution_name', 150);
            $table->text('institution_address')->nullable();
            $table->string('major', 100)->nullable();
            $table->year('start_year');
            $table->year('end_year');
            $table->decimal('gpa_or_score', 4, 2)->nullable();
            $table->string('certificate_number', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_histories');
    }
};