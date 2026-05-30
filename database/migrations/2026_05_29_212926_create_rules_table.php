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
        Schema::create('rules', function (Blueprint $table) {
            $table->id('rul_id');
            $table->foreignId('rul_user_id')->constrained('users', 'use_id');
            $table->foreignId('rul_category_id')->constrained('categories', 'cat_id');
            $table->string('rul_pattern');
            $table->integer('rul_priority')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
