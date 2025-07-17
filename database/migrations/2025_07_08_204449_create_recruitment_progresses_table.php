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
        Schema::create('recruitment_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('applicants')->onDelete('cascade');
            $table->enum('stage', [
                'cv_screening',
                'general_knowledge_test',
                'user_assessment',
                'hc_interview',
                'bod_interview',
                'offering_letter',
                'rejected'
            ]);
            $table->enum('offering_status', ['accepted', 'rejected', 'in_progress'])->nullable();
            $table->dateTime('status_date');
            $table->text('notes')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->enum('contract_type', ['Contract', 'Internship', 'Probation', 'Full-time'])->nullable()->default('Contract');
            $table->text('test_result')->nullable();
            $table->string('result_file')->nullable();
            $table->string('score')->nullable();
            $table->text('slik_recap')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_progresses');
    }
};
