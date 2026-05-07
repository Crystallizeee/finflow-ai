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
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('account_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // income, expense
            $table->decimal('amount', 15, 2);
            $table->string('description', 500);
            $table->string('frequency'); // daily, weekly, monthly, etc.
            $table->smallInteger('day_of_month')->nullable();
            $table->smallInteger('day_of_week')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_occurrence')->index();
            $table->timestamp('last_processed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
