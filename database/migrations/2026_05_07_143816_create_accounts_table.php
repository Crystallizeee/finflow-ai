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
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('type'); // bank, cash, ewallet, investment, crypto
            $table->char('currency', 3);
            $table->decimal('balance', 15, 2);
            $table->decimal('initial_balance', 15, 2);
            $table->string('color', 7);
            $table->string('icon', 50);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->jsonb('meta')->default('{}');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
