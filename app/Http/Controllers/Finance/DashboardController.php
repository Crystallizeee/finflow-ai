<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics
    ) {}

    public function __invoke(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'accounts' => $user->accounts()->get(),
            'recentTransactions' => $user->transactions()
                ->with(['category', 'account'])
                ->latest('date')
                ->take(5)
                ->get(),
            'chartData' => $this->analytics->getMonthlySpendingChart($user),
            'categoryBreakdown' => $this->analytics->getCategoryBreakdown($user),
        ]);
    }
}
