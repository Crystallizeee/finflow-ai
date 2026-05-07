<?php

namespace App\Services\Finance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getMonthlyTrend(User $user, int $months = 6)
    {
        $data = $user->transactions()
            ->select(
                DB::raw("to_char(date, 'Mon') as month"),
                DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense"),
                DB::raw("MIN(date) as sort_date")
            )
            ->where('date', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('sort_date')
            ->get();

        return [
            'labels' => $data->pluck('month'),
            'income' => $data->pluck('income')->map(fn($v) => (float) $v),
            'expense' => $data->pluck('expense')->map(fn($v) => (float) $v),
        ];
    }

    public function getCategoryDistribution(User $user)
    {
        $data = $user->transactions()
            ->with('category')
            ->select(
                'category_id',
                DB::raw("SUM(amount) as total")
            )
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->groupBy('category_id')
            ->get();

        return $data->map(fn($item) => [
            'name' => $item->category?->name ?? 'Lainnya',
            'value' => (float) $item->total,
            'color' => $item->category?->color ?? '#cbd5e1',
        ]);
    }

    public function getForecast(User $user)
    {
        // Simple linear forecast based on average spending
        $avgExpense = $user->transactions()
            ->where('type', 'expense')
            ->where('date', '>=', now()->subMonths(3))
            ->sum('amount') / 3;

        $currentBalance = $user->accounts()->sum('balance');
        $daysInMonth = now()->daysInMonth;
        $currentDay = now()->day;
        $daysRemaining = $daysInMonth - $currentDay;

        $dailyAvg = $avgExpense / 30;
        $projectedSpending = $dailyAvg * $daysRemaining;

        return [
            'projected_end_balance' => max(0, $currentBalance - $projectedSpending),
            'projected_spending' => (float) $projectedSpending,
            'is_risky' => ($currentBalance - $projectedSpending) < ($currentBalance * 0.1),
        ];
    }
}
