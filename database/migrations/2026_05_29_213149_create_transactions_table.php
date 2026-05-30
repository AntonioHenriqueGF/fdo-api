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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('tra_id');
            $table->foreignId('tra_user_id')->constrained('users', 'use_id');
            $table->foreignId('tra_import_id')->constrained('imports', 'imp_id');
            $table->foreignId('tra_category_id')->nullable()->constrained('categories', 'cat_id');
            $table->foreignId('tra_matched_rule_id')->nullable()->constrained('rules', 'rul_id');
            $table->timestamp('tra_date')->index();
            $table->string('tra_description')->nullable();
            $table->decimal('tra_amount', 15, 2);

            // Adds constraint to user_id and date e user_id and category_id for faster lookups
            $table->index([
                'tra_user_id',
                'tra_date'
            ]);

            $table->index([
                'tra_user_id',
                'tra_category_id'
            ]);
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
