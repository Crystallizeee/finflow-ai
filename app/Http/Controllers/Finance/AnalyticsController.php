<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Finance/Analytics', [
            'monthlyTrend' => $this->analyticsService->getMonthlyTrend($user),
            'categoryDistribution' => $this->analyticsService->getCategoryDistribution($user),
            'forecast' => $this->analyticsService->getForecast($user),
            'topMerchants' => $this->getTopMerchants($user),
        ]);
    }

    private function getTopMerchants($user)
    {
        return $user->transactions()
            ->select('merchant', \Illuminate\Support\Facades\DB::raw('count(*) as count'), \Illuminate\Support\Facades\DB::raw('sum(amount) as total'))
            ->whereNotNull('merchant')
            ->where('type', 'expense')
            ->groupBy('merchant')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }
}
