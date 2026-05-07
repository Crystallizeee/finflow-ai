<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DetectSubscriptions extends Command
{
    protected $signature = 'app:detect-subscriptions';
    protected $description = 'Detect potential recurring subscriptions from transaction history using pattern matching';

    public function handle()
    {
        $this->info('Starting subscription detection...');

        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                $this->processUser($user);
            }
        });

        $this->info('Subscription detection completed.');
    }

    private function processUser(User $user)
    {
        // Logic: Find transactions with same merchant and similar amount occurring monthly
        $potentialSubs = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereNotNull('merchant')
            ->select('merchant', 'amount', DB::raw('count(*) as count'))
            ->groupBy('merchant', 'amount')
            ->having('count', '>=', 2)
            ->get();

        foreach ($potentialSubs as $item) {
            // Check if already exist in subscriptions
            $exists = Subscription::where('user_id', $user->id)
                ->where('merchant', $item->merchant)
                ->exists();

            if (!$exists) {
                // Here we would ideally call AI to confirm if this merchant is a known subscription provider
                // For now, we flag it as a suggestion in the UI (handled by Controller logic)
                $this->line("Detected potential sub: {$item->merchant} - " . number_format($item->amount));
            }
        }
    }
}
