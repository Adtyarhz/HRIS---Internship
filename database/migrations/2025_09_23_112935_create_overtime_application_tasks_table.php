<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOvertimeApplicationTasksTable extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_application_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overtime_application_id')
                  ->constrained('overtime_applications')
                  ->cascadeOnDelete(); // jika aplikasi dihapus, task ikut dihapus

            $table->string('task_description');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_application_tasks');
    }
}
