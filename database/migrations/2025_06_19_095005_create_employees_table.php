<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20)->unique();
            $table->string('nip', 20)->nullable()->unique();
            $table->string('npwp', 20)->nullable()->unique();
            $table->string('full_name', 100);
            $table->enum('gender', ['L', 'P']);
            $table->string('religion', 50);
            $table->string('birth_place', 50);
            $table->date('birth_date');
            $table->integer('age');
            $table->enum('marital_status', ['TK', 'K0', 'K1', 'K2', 'K3'])->default('TK');
            $table->text('ktp_address');
            $table->text('current_address');
            $table->string('city', 50);
            $table->string('province', 50);
            $table->string('phone_number', 20)->unique();
            $table->string('email', 100)->unique();
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->date('hire_date');
            $table->date('separation_date')->nullable();

            $table->foreignId('division_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('position_id')->nullable()->constrained()->onDelete('set null');

            $table->foreignId('user_id')->nullable()->unique()->constrained()->onDelete('casacade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};