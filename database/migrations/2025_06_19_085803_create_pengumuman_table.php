<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('pengumuman', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('label')->nullable()->comment('Label tujuan: HR, IT, Marketing, Umum, dsb.');
        $table->string('judul');
        $table->text('isi');
        $table->enum('tipe', ['teks', 'polling'])->default('teks');
        if (!Schema::hasColumn('pengumuman', 'attachment')) {
            $table->string('attachment')->nullable();
        }
        $table->timestamps();
    });

}
    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
