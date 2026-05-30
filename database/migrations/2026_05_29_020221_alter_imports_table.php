<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            ALTER TABLE imports
            DROP COLUMN imp_file_hash
        ');

        DB::statement('
            ALTER TABLE imports
            ADD imp_file_hash BINARY(32) NOT NULL
        ');

        DB::statement('
            ALTER TABLE imports
            ADD CONSTRAINT imports_user_hash_unique
            UNIQUE (imp_user_id, imp_file_hash)
        ');
    }

    public function down(): void
    {
        //
    }
};