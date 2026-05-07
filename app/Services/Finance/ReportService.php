<?php

namespace App\Services\Finance;

use App\Models\User;
use App\Services\AI\AIChatAssistantService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    public function __construct(
        private readonly AIChatAssistantService $aiService
    ) {}

    public function generateMonthlyReport(User $user, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;
        $date = Carbon::createFromDate($year, $month, 1);

        $cacheKey = "monthly_report_{$user->id}_{$year}_{$month}";

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($user, $date) {
            $data = $this->getFinancialData($user, $date);
            
            $prompt = [
                ['role' => 'user', 'content' => "Buatlah laporan analisis keuangan bulanan untuk bulan {$date->format('F Y')}.
                Data Keuangan:
                - Total Pemasukan: IDR {$data['income']}
                - Total Pengeluaran: IDR {$data['expense']}
                - Sisa Saldo: IDR " . ($data['income'] - $data['expense']) . "
                - Kategori Terbesar: {$data['top_category']}
                - Persentase Budget Terpakai: {$data['budget_utilization']}%
                
                Tugas Anda:
                1. Berikan analisis singkat tentang rasio pengeluaran vs pemasukan.
                2. Berikan 3 poin rekomendasi spesifik untuk bulan depan.
                3. Berikan skor kesehatan keuangan (1-100).
                Format jawaban dalam Markdown yang indah."]
            ];

            return [
                'period' => $date->format('F Y'),
                'summary' => $data,
                'ai_analysis' => $this->aiService->chat($user, $prompt),
                'generated_at' => now()->toDateTimeString()
            ];
        });
    }

    private function getFinancialData(User $user, Carbon $date)
    {
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        $transactions = $user->transactions()
            ->whereBetween('date', [$start, $end])
            ->get();

        $income = $transactions->where('type', 'income')->sum('amount');
        $expense = $transactions->where('type', 'expense')->sum('amount');
        
        $topCategory = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('date', [$start, $end])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->first();

        $budgetTotal = $user->budgets()->sum('amount');
        $spentTotal = $user->budgets()->sum('spent');
        $utilization = $budgetTotal > 0 ? round(($spentTotal / $budgetTotal) * 100, 2) : 0;

        return [
            'income' => $income,
            'expense' => $expense,
            'top_category' => $topCategory ? $topCategory->category->name : 'N/A',
            'budget_utilization' => $utilization
        ];
    }
}
