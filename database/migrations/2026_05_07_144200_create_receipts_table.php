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
        Schema::create('receipts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('transaction_id')->nullable();
            $table->string('storage_path', 500);
            $table->string('status'); // processing, completed, failed
            $table->string('merchant_name')->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->date('date')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->jsonb('extracted_data')->default('{}');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
