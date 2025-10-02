<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOvertimeApplicationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_applications', function (Blueprint $table) {
            $table->id();
            // employee yang akan lembur (referensi ke tabel employees)
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            // siapa yang mengajukan (user akun, bisa manager/sectionhead)
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            // HC / Superadmin yang menyetujui (nullable sampai di-approve)
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->text('reason')->nullable();

            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            // index untuk query cepat
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_applications');
    }
}
