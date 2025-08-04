<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->unsignedBigInteger('indirect_supervisor_id')->nullable()->after('parent_id');
            $table->foreign('indirect_supervisor_id')->references('id')->on('positions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['indirect_supervisor_id']);
            $table->dropColumn('indirect_supervisor_id');
        });
    }
};
