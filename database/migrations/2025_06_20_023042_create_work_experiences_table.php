<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('company_name', 150);
            $table->text('company_address')->nullable();
            $table->string('company_phone', 20)->nullable();
            $table->string('position_title', 100);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('responsibilities')->nullable();
            $table->string('reason_to_leave')->nullable();
            $table->decimal('last_salary', 12, 2)->nullable();
            $table->string('reference_letter_file')->nullable();
            $table->string('salary_slip_file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
    }
};