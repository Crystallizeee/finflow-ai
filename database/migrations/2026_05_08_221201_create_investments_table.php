<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. BBCA, Bitcoin, Emas Antam
            $table->string('ticker')->nullable(); // e.g. BBCA.JK, BTC
            $table->string('type'); // stock, crypto, gold, mutual_fund
            $table->decimal('units', 20, 8); // Number of shares/coins
            $table->decimal('average_buy_price', 20, 4);
            $table->string('currency')->default('IDR');
            $table->decimal('current_price', 20, 4)->nullable();
            $table->string('platform')->nullable(); // e.g. Ajaib, Binace, Pegadaian
            $table->timestamps();
            $table->softDeletes();
        });

        // Track price history for charting
        Schema::create('investment_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('investment_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 20, 4);
            $table->timestamp('recorded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_prices');
        Schema::dropIfExists('investments');
    }
};
