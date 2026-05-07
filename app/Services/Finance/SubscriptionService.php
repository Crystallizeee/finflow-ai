<?php

namespace App\Services\Finance;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Detect potential subscriptions from past transactions.
     */
    public function detectPotentialSubscriptions(User $user): array
    {
        // Get transactions grouped by merchant and amount
        // We look for patterns where the same merchant charged a similar amount multiple times
        $patterns = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->select(
                'merchant',
                'category_id',
                'amount',
                'currency',
                DB::raw('COUNT(*) as count'),
                DB::raw('MIN(date) as first_date'),
                DB::raw('MAX(date) as last_date')
            )
            ->whereNotNull('merchant')
            ->groupBy('merchant', 'category_id', 'amount', 'currency')
            ->havingRaw('COUNT(*) >= 2')
            ->get();

        $potentialSubscriptions = [];

        foreach ($patterns as $pattern) {
            $firstDate = Carbon::parse($pattern->first_date);
            $lastDate = Carbon::parse($pattern->last_date);
            
            // Calculate average gap in days
            $daysDiff = $firstDate->diffInDays($lastDate);
            $avgGap = $daysDiff / ($pattern->count - 1);

            // If the gap is roughly 30 days (monthly) or 7 days (weekly)
            $isMonthly = $avgGap >= 25 && $avgGap <= 35;
            $isWeekly = $avgGap >= 6 && $avgGap <= 8;

            if ($isMonthly || $isWeekly) {
                // Check if already exists in subscriptions
                $exists = $user->subscriptions()
                    ->where('merchant', $pattern->merchant)
                    ->where('amount', $pattern->amount)
                    ->exists();

                if (!$exists) {
                    $potentialSubscriptions[] = [
                        'name' => $pattern->merchant,
                        'merchant' => $pattern->merchant,
                        'category_id' => $pattern->category_id,
                        'amount' => $pattern->amount,
                        'currency' => $pattern->currency,
                        'billing_cycle' => $isMonthly ? 'monthly' : 'weekly',
                        'next_billing_date' => $lastDate->copy()->addDays(round($avgGap))->format('Y-m-d'),
                        'confidence' => 0.85,
                    ];
                }
            }
        }

        return $potentialSubscriptions;
    }
}
