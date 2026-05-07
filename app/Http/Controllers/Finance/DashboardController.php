<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\AnalyticsService;
use App\Services\Finance\AdvisorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
        private readonly AdvisorService $advisorService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'totalBalance' => $user->accounts()->sum('balance'),
            'recentTransactions' => $user->transactions()
                ->with(['category', 'account'])
                ->latest('date')
                ->take(5)
                ->get(),
            'monthlySpending' => $this->analyticsService->getMonthlyTrend($user),
            'categoryBreakdown' => $this->analyticsService->getCategoryDistribution($user),
            'aiInsight' => $this->advisorService->getMonthlyInsight($user),
            'activeGoals' => $user->goals()
                ->where('is_completed', false)
                ->latest()
                ->take(3)
                ->get()
                ->map(fn($g) => [
                    'name' => $g->name,
                    'progress' => (float) $g->progress,
                    'icon' => $g->icon ?? '🎯'
                ]),
            'upcomingSubscriptions' => $user->subscriptions()
                ->where('is_active', true)
                ->orderBy('next_billing_date')
                ->take(3)
                ->get()
                ->map(fn($s) => [
                    'name' => $s->name,
                    'amount' => (float) $s->amount,
                    'next_date' => $s->next_billing_date->format('d M')
                ]),
        ]);
    }
}
