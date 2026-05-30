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
        Schema::create('daily_balances', function (Blueprint $table) {
            $table->id('dba_id');
            $table->foreignId('dba_user_id')->constrained('users', 'use_id');
            $table->foreignId('dba_import_id')->constrained('imports', 'imp_id');
            $table->timestamp('dba_date')->index();
            $table->decimal('dba_closing_balance', 15, 2);

            // Indices

            $table->index([
                'dba_user_id',
                'dba_date'
            ]);

            $table->index([
                'dba_user_id',
                'dba_import_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_balances');
    }
};
