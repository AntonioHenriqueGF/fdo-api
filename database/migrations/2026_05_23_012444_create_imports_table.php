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
        Schema::create('imports', function (Blueprint $table) {
            $table->id('imp_id');
            $table->foreignId('imp_user_id')->constrained('users', 'use_id');
            $table->string('imp_file_name');
            $table->string('imp_file_hash');
            $table->timestamp('imp_imported_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 
    }
};
