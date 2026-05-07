<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $budgets = $user->budgets()
            ->with('category')
            ->orderBy('is_active', 'desc')
            ->orderBy('end_date', 'asc')
            ->get();

        $categories = $user->categories()
            ->where('type', 'expense')
            ->get(['id', 'name']);

        return Inertia::render('Finance/Budgets', [
            'budgets' => $budgets->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'amount' => (float) $b->amount,
                'spent' => (float) $b->spent,
                'remaining' => (float) $b->remaining,
                'progress' => (float) $b->progress,
                'category' => $b->category?->name ?? 'Semua Pengeluaran',
                'period' => $b->period,
                'is_active' => $b->is_active,
                'end_date' => $b->end_date->format('d M Y'),
                'is_over' => $b->spent > $b->amount,
            ]),
            'categories' => $categories,
            'aiRecommendation' => $this->getAIRecommendation($user),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'period' => 'required|in:weekly,monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $request->user()->budgets()->create($validated);

        return redirect()->back();
    }

    private function getAIRecommendation($user)
    {
        // Simple logic for now: suggest 90% of last month's spending for food
        $lastMonth = now()->subMonth();
        $spent = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $lastMonth->month)
            ->sum('amount');

        if ($spent <= 0) return null;

        return [
            'text' => "Berdasarkan pengeluaran bulan lalu, kami sarankan total budget bulanan Anda di sekitar " . number_format($spent * 0.9, 0, ',', '.') . " untuk menghemat 10%.",
            'suggestedAmount' => round($spent * 0.9)
        ];
    }
}
