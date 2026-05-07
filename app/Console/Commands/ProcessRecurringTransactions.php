<?php

namespace App\Console\Commands;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'app:process-recurring-transactions';
    protected $description = 'Process all recurring transactions that are due today';

    public function handle()
    {
        $today = Carbon::today();
        
        $recurringTransactions = RecurringTransaction::where('is_active', true)
            ->where('next_occurrence', '<=', $today)
            ->get();

        if ($recurringTransactions->isEmpty()) {
            $this->info('No recurring transactions to process today.');
            return;
        }

        foreach ($recurringTransactions as $recurring) {
            DB::transaction(function () use ($recurring, $today) {
                // Create the actual transaction
                Transaction::create([
                    'ulid' => (string) Str::ulid(),
                    'user_id' => $recurring->user_id,
                    'account_id' => $recurring->account_id,
                    'category_id' => $recurring->category_id,
                    'type' => $recurring->type,
                    'amount' => $recurring->amount,
                    'base_amount' => $recurring->amount,
                    'description' => $recurring->description . ' (Otomatis)',
                    'date' => $today,
                ]);

                // Calculate next occurrence
                $next = $this->calculateNextOccurrence($recurring);
                
                $recurring->update([
                    'last_processed_at' => now(),
                    'next_occurrence' => $next,
                ]);
            });

            $this->info("Processed recurring transaction: {$recurring->description}");
        }

        $this->info('All due recurring transactions have been processed.');
    }

    private function calculateNextOccurrence(RecurringTransaction $recurring)
    {
        $current = Carbon::parse($recurring->next_occurrence);

        return match ($recurring->frequency) {
            'daily' => $current->addDay(),
            'weekly' => $current->addWeek(),
            'monthly' => $current->addMonth(),
            'yearly' => $current->addYear(),
            default => $current->addMonth(),
        };
    }
}
