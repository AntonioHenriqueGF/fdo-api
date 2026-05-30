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
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('id', 'use_id');
            $table->renameColumn('name', 'use_name');
            $table->renameColumn('email', 'use_email');
            $table->renameColumn('password', 'use_password');
            $table->renameColumn('remember_token', 'use_remember_token');
            $table->renameColumn('created_at', 'use_created_at');
            $table->renameColumn('updated_at', 'use_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Deve ser vazio
    }
};
