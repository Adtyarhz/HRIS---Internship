<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pollings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade'); // ✅ diperbaiki
            $table->timestamp('deadline')->nullable(); // batas waktu voting
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });             
    }

    public function down(): void
    {
        Schema::dropIfExists('pollings');
    }
};
