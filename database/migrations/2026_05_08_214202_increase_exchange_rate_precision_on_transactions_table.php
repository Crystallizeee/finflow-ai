<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we use raw SQL to modify column in partitioned table
        DB::statement("ALTER TABLE transactions ALTER COLUMN exchange_rate TYPE DECIMAL(15,6)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transactions ALTER COLUMN exchange_rate TYPE DECIMAL(10,6)");
    }
};
