<?php

namespace App\Services\Finance;

use App\Models\User;
use App\Services\AI\AIChatAssistantService;
use Illuminate\Support\Facades\Cache;

class AdvisorService
{
    public function __construct(
        private readonly AIChatAssistantService $aiService
    ) {}

    /**
     * Get a personalized financial insight for the user dashboard.
     */
    public function getMonthlyInsight(User $user): string
    {
        $cacheKey = "user_insight_{$user->id}_" . now()->format('Y-m-d');
        
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($user) {
            try {
                $prompt = [
                    ['role' => 'user', 'content' => "Berikan ringkasan singkat (maksimal 3 kalimat) tentang kondisi keuangan saya bulan ini berdasarkan data transaksi terakhir saya. Berikan satu saran praktis untuk berhemat."]
                ];

                return $this->aiService->chat($user, $prompt);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Advisor AI Error: " . $e->getMessage());
                return "AI Assistant sedang sibuk menganalisis data Anda. Silakan cek kembali nanti untuk mendapatkan saran keuangan personal.";
            }
        });
    }
}
