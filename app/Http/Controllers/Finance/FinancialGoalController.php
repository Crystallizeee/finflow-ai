<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinancialGoal;
use App\Services\AI\AIChatAssistantService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class FinancialGoalController extends Controller
{
    public function __construct(
        private readonly AIChatAssistantService $aiService
    ) {}

    public function index(Request $request)
    {
        $goals = $request->user()->goals()->with('account')->get();

        return Inertia::render('Finance/Goals', [
            'goals' => $goals->map(fn($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'target_amount' => $g->target_amount,
                'current_amount' => $g->current_amount,
                'progress' => $g->progress,
                'target_date' => $g->target_date->format('Y-m-d'),
                'icon' => $g->icon ?? '🎯',
                'color' => $g->color ?? '#4f46e5',
                'is_completed' => $g->is_completed,
            ])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'target_amount' => 'required|numeric|min:1',
            'target_date' => 'required|date|after:today',
            'account_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
        ]);

        $goal = $request->user()->goals()->create($validated);

        // Get AI Recommendation for monthly contribution
        $this->updateAiRecommendation($request->user(), $goal);

        return redirect()->back();
    }

    private function updateAiRecommendation(\App\Models\User $user, FinancialGoal $goal)
    {
        $monthsLeft = Carbon::parse($goal->target_date)->diffInMonths(now());
        if ($monthsLeft <= 0) $monthsLeft = 1;

        $prompt = "Target Keuangan: {$goal->name}
        Jumlah Target: IDR {$goal->target_amount}
        Sisa Waktu: {$monthsLeft} bulan
        Saldo Saat Ini: IDR {$goal->current_amount}
        
        Berapa jumlah yang harus disisihkan per bulan? Berikan jawaban dalam angka saja (IDR).";

        try {
            $recommendation = $this->aiService->chat($user, [['role' => 'user', 'content' => $prompt]]);
            
            // Extract number from AI response
            preg_match_all('!\d+!', str_replace(['.', ','], '', $recommendation), $matches);
            $amount = isset($matches[0][0]) ? (float) $matches[0][0] : ($goal->target_amount / $monthsLeft);

            $goal->update(['monthly_contribution' => $amount]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Goal AI Recommendation failed: " . $e->getMessage());
            // Fallback to simple calculation
            $goal->update(['monthly_contribution' => $goal->target_amount / $monthsLeft]);
        }
    }
}
