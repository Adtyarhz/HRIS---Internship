<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_dependents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('contact_name', 100);
            $table->string('relationship', 50);
            $table->string('phone_number', 20)->unique();
            $table->text('address');
            $table->string('city', 50);
            $table->string('province', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_dependents');
    }
};
