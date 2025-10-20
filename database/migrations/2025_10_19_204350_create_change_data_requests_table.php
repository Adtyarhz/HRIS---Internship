<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('change_data_requests', function (Blueprint $table) {
            $table->id();
            $table->string('model');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('action'); // create, update, delete
            $table->json('changes')->nullable();

            // Status Lifecycle
            $table->string('status')->default('pending'); // pending, checked, approved, applied, rejected, failed
            $table->text('status_notes')->nullable(); // Alasan reject atau error saat apply

            // User & Timestamps for each step
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('checked_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('rejected_by')->nullable()->constrained('users');

            $table->timestamp('checked_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('failed_at')->nullable(); // Untuk mencatat waktu kegagalan apply
            
            $table->timestamp('expired_at')->nullable();
            $table->timestamps(); // created_at and updated_at

            $table->index(['model', 'model_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_data_requests');
    }
};