<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurances', function (Blueprint $table) {
            $table->string('faskes_name')->nullable()->after('insurance_type');
            $table->string('faskes_address')->nullable()->after('faskes_name');
        });
    }

    public function down(): void
    {
        Schema::table('insurances', function (Blueprint $table) {
            $table->dropColumn(['faskes_name', 'faskes_address']);
        });
    }
};
