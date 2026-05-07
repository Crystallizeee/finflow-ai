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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('merchant', 200)->index();
            $table->decimal('amount', 15, 2);
            $table->char('currency', 3);
            $table->string('billing_cycle'); // weekly, monthly, quarterly, yearly
            $table->date('next_billing_date')->index();
            $table->boolean('is_active')->default(true);
            $table->boolean('detected_by_ai')->default(false);
            $table->decimal('detection_confidence', 3, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
