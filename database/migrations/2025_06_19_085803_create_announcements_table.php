<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title'); 
            $table->enum('announcement_type', ['Umum', 'Divisi', 'Urgent', 'Informasi', 'Polling']);
            $table->string('label')->nullable()->comment('Label tujuan, contoh: HR, IT, Marketing, Umum, dsb');
            $table->text('content');
            $table->string('attachment_file')->nullable(); 
            $table->string('external_link')->nullable();   
            $table->unsignedBigInteger('created_by');     
            $table->timestamps();

            // Relasi foreign key
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
