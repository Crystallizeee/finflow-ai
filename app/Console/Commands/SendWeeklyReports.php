<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Mail\WeeklyFinancialDigest;
use App\Services\Finance\AnalyticsService;
use App\Services\AI\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWeeklyReports extends Command
{
    protected $signature = 'app:send-weekly-reports';
    protected $description = 'Send AI-powered weekly financial reports to all active users';

    public function handle(AnalyticsService $analytics, GeminiService $gemini)
    {
        $users = User::all();
        $this->info("Processing weekly reports for " . $users->count() . " users...");

        foreach ($users as $user) {
            try {
                // 1. Get financial data for the last 7 days
                $weeklyData = $user->transactions()
                    ->where('date', '>=', now()->subDays(7))
                    ->selectRaw('type, SUM(base_amount) as total')
                    ->groupBy('type')
                    ->get()
                    ->pluck('total', 'type');

                $categorySpending = $user->transactions()
                    ->with('category')
                    ->where('type', 'expense')
                    ->where('date', '>=', now()->subDays(7))
                    ->selectRaw('category_id, SUM(base_amount) as total')
                    ->groupBy('category_id')
                    ->get()
                    ->map(fn($t) => [
                        'category' => $t->category?->name ?? 'Lainnya',
                        'total' => $t->total
                    ]);

                // 2. Generate AI Commentary
                $prompt = "Berikut adalah ringkasan keuangan mingguan saya:
                - Total Pemasukan: Rp " . number_format($weeklyData['income'] ?? 0, 0, ',', '.') . "
                - Total Pengeluaran: Rp " . number_format($weeklyData['expense'] ?? 0, 0, ',', '.') . "
                - Breakdown Kategori: " . json_encode($categorySpending) . "

                Tolong buatkan narasi singkat (maksimal 3 paragraf) yang personal, gaul (ala Gen-Z), dan memberikan saran konkret untuk minggu depan. Gunakan bahasa Indonesia.";

                $aiCommentary = $gemini->chat([['role' => 'user', 'content' => $prompt]]);

                // 3. Send Email
                Mail::to($user->email)->send(new WeeklyFinancialDigest($user, $weeklyData->toArray(), $categorySpending->toArray(), $aiCommentary));
                
                $this->line("Sent to: {$user->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send weekly report to {$user->email}: " . $e->getMessage());
                $this->error("Failed to send to: {$user->email}");
            }
        }

        $this->info("All weekly reports sent!");
    }
}
