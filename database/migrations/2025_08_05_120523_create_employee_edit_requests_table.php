<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeEditRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('employee_edit_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); // ID dari user yang ingin mengubah data
            
            // Tambahan dari migration kedua
            $table->string('model'); // Nama model yang diubah
            $table->unsignedBigInteger('model_id')->nullable(); // ID record di model tersebut

            $table->string('method'); // update / delete
            $table->json('original_data')->nullable(); // data sebelum diubah
            $table->json('changed_data')->nullable();  // data setelah perubahan
            $table->enum('status', ['waiting', 'approved', 'rejected'])->default('waiting');
            $table->timestamp('requested_at')->nullable(); // tanggal request
            $table->unsignedBigInteger('requested_by')->nullable(); // user yang mengajukan request
            $table->unsignedBigInteger('approved_by')->nullable(); // user yang menyetujui
            $table->timestamps();

            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_edit_requests');
    }
}
