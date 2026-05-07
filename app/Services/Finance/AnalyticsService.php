<?php

namespace App\Services\Finance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getMonthlySpendingChart(User $user, int $days = 30): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->endOfDay();

        $transactions = $user->transactions()
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(transactions.date) as tx_date'),
                DB::raw('SUM(transactions.base_amount) as total')
            )
            ->groupBy(DB::raw('DATE(transactions.date)'))
            ->orderBy('tx_date')
            ->get();

        // Fill missing days with 0
        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $data[$date] = 0;
        }

        foreach ($transactions as $tx) {
            $data[$tx->tx_date] = (float) $tx->total;
        }

        return [
            'labels' => array_keys($data),
            'series' => [
                [
                    'name' => 'Pengeluaran',
                    'data' => array_values($data),
                ]
            ]
        ];
    }

    public function getCategoryBreakdown(User $user, int $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();

        $data = $user->transactions()
            ->where('transactions.type', 'expense')
            ->where('transactions.date', '>=', $startDate)
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name', 'categories.color', DB::raw('SUM(transactions.base_amount) as total'))
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return $data->toArray();
    }
}
