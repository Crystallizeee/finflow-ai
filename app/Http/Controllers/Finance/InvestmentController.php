<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestmentController extends Controller
{
    public function index(Request $request)
    {
        $investments = $request->user()->investments()
            ->latest()
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'name' => $inv->name,
                    'ticker' => $inv->ticker,
                    'type' => $inv->type,
                    'units' => $inv->units,
                    'average_buy_price' => $inv->average_buy_price,
                    'current_price' => $inv->current_price ?? $inv->average_buy_price,
                    'total_cost' => $inv->total_cost,
                    'current_value' => $inv->current_value,
                    'profit_loss' => $inv->profit_loss,
                    'profit_loss_percentage' => round($inv->profit_loss_percentage, 2),
                    'platform' => $inv->platform,
                ];
            });

        $totalInvestmentValue = $investments->sum('current_value');
        $totalProfitLoss = $investments->sum('profit_loss');
        
        return Inertia::render('Finance/Investments', [
            'investments' => $investments,
            'summary' => [
                'total_value' => $totalInvestmentValue,
                'total_pl' => $totalProfitLoss,
                'total_pl_percentage' => $totalInvestmentValue > 0 ? ($totalProfitLoss / ($totalInvestmentValue - $totalProfitLoss)) * 100 : 0,
            ],
            'allocation' => $investments->groupBy('type')->map(fn($group) => $group->sum('current_value')),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ticker' => 'nullable|string|max:20',
            'type' => 'required|in:stock,crypto,gold,mutual_fund',
            'units' => 'required|numeric|min:0.00000001',
            'average_buy_price' => 'required|numeric|min:0',
            'platform' => 'nullable|string|max:100',
        ]);

        $request->user()->investments()->create($validated);

        return redirect()->back()->with('success', 'Aset investasi berhasil ditambahkan! 🚀');
    }
}
