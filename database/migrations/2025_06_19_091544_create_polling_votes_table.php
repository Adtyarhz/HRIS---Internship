<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polling_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('polling_option_id')->constrained('polling_options')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // sebelumnya user_id
            $table->timestamps();
        });        
    }

    public function down(): void
    {
        Schema::dropIfExists('polling_votes');
    }
};
