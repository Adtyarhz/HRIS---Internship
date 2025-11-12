<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('insurances', function (Blueprint $table) {
            $table->string('insurance_type_new')->nullable()->after('insurance_type');
        });

        DB::table('insurances')->where('insurance_type', 'KES')->update(['insurance_type_new' => 'BPJS KESEHATAN']);
        DB::table('insurances')->where('insurance_type', 'TK')->update(['insurance_type_new' => 'BPJS KETENAGAKERJAAN']);
        DB::table('insurances')->where('insurance_type', 'ASURANSI SWASTA (N-BPJS)')->update(['insurance_type_new' => 'ASURANSI SWASTA (N-BPJS)']);

        Schema::table('insurances', function (Blueprint $table) {
            $table->dropColumn('insurance_type');
            $table->renameColumn('insurance_type_new', 'insurance_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurances', function (Blueprint $table) {
            $table->string('insurance_type_old')->nullable()->after('insurance_type');
        });

        DB::table('insurances')->where('insurance_type', 'BPJS KESEHATAN')->update(['insurance_type_old' => 'KES']);
        DB::table('insurances')->where('insurance_type', 'BPJS KETENAGAKERJAAN')->update(['insurance_type_old' => 'TK']);
        DB::table('insurances')->where('insurance_type', 'ASURANSI SWASTA (N-BPJS)')->update(['insurance_type_old' => 'ASURANSI SWASTA (N-BPJS)']);

        Schema::table('insurances', function (Blueprint $table) {
            $table->dropColumn('insurance_type');
            $table->renameColumn('insurance_type_old', 'insurance_type');
        });
    }
};
