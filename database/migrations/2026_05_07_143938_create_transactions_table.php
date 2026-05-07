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
        // Partitioned table via raw SQL for PostgreSQL
        DB::statement("
            CREATE TABLE transactions (
                id UUID DEFAULT gen_random_uuid(),
                ulid VARCHAR(26) NOT NULL,
                user_id UUID NOT NULL,
                account_id UUID NOT NULL,
                category_id UUID NOT NULL,
                transfer_account_id UUID NULL,
                recurring_transaction_id UUID NULL,
                receipt_id UUID NULL,
                type VARCHAR(20) NOT NULL CHECK (type IN ('income', 'expense', 'transfer')),
                amount DECIMAL(15,2) NOT NULL,
                currency CHAR(3) NOT NULL DEFAULT 'IDR',
                exchange_rate DECIMAL(10,6) NOT NULL DEFAULT 1.0,
                base_amount DECIMAL(15,2) NOT NULL,
                description VARCHAR(500) NOT NULL DEFAULT '',
                notes TEXT NULL,
                merchant VARCHAR(255) NULL,
                location VARCHAR(255) NULL,
                tags TEXT[] DEFAULT '{}',
                date DATE NOT NULL,
                transacted_at TIMESTAMPTZ NOT NULL,
                is_verified BOOLEAN DEFAULT FALSE,
                metadata JSONB DEFAULT '{}',
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW(),
                deleted_at TIMESTAMPTZ NULL,
                PRIMARY KEY (id, date)
            ) PARTITION BY RANGE (date)
        ");

        // Create initial partitions
        DB::statement("
            CREATE TABLE transactions_2025 PARTITION OF transactions
            FOR VALUES FROM ('2025-01-01') TO ('2026-01-01')
        ");

        DB::statement("
            CREATE TABLE transactions_2026 PARTITION OF transactions
            FOR VALUES FROM ('2026-01-01') TO ('2027-01-01')
        ");

        // Indexes on partitioned table
        DB::statement("CREATE UNIQUE INDEX ON transactions(ulid, date)");
        DB::statement("CREATE INDEX ON transactions(user_id, date DESC)");
        DB::statement("CREATE INDEX ON transactions(account_id, date DESC)");
        DB::statement("CREATE INDEX ON transactions(user_id, category_id, date DESC)");
        DB::statement("CREATE INDEX ON transactions USING gin(tags)");
        DB::statement("CREATE INDEX ON transactions USING gin(to_tsvector('simple', COALESCE(merchant, '') || ' ' || description))");
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
