<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOvertimeApplicationNotificationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_application_notifications', function (Blueprint $table) {
            $table->id();

            // FK ke overtime_applications dengan nama constraint pendek
            $table->unsignedBigInteger('overtime_application_id');
            $table->foreign('overtime_application_id', 'ot_notif_app_fk')
                  ->references('id')->on('overtime_applications')
                  ->cascadeOnDelete();

            // FK ke users dengan nama constraint pendek
            $table->unsignedBigInteger('recipient_id');
            $table->foreign('recipient_id', 'ot_notif_user_fk')
                  ->references('id')->on('users')
                  ->restrictOnDelete();

            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            // index dengan nama custom
            $table->index(['recipient_id', 'is_read'], 'ot_notif_read_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_application_notifications');
    }
}
