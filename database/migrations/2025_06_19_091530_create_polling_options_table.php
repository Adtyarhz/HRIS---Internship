<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polling_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('polling_id')->constrained('pollings')->onDelete('cascade');
            $table->string('option_text');
            $table->timestamps();
        });        
    }

    public function down(): void
    {
        Schema::dropIfExists('polling_options');
    }
};
