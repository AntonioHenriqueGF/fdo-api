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
        Schema::create('job_requests', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users', 'use_id');
            $table->string('type');
            $table->string('status')->default('pending')->index();
            // Stores aditional info about the job request in JSON format
            $table->json('additional_info')->nullable();
            // Stores error message if the job request fails
            $table->text('error_message')->nullable();
            // Started at and completed at timestamps for the job request
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Adds constraint to user_id and status for faster lookups
            $table->index([
                'user_id',
                'status'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_requests');
    }
};
