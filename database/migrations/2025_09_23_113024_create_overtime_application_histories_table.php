<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOvertimeApplicationHistoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_application_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overtime_application_id')
                  ->constrained('overtime_applications')
                  ->cascadeOnDelete();

            // siapa yang melakukan aksi
            $table->foreignId('action_by')->constrained('users')->restrictOnDelete();

            // aksi yg dilakukan -> enum (kamu bisa tambah nilai jika perlu)
            $table->string('action_type', 20);
            $table->text('description')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_application_histories');
    }
}
