<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AI\AIProviderFactory;
use App\Services\Finance\AnalyticsService;
use Illuminate\Support\Facades\Log;

class AIChatAssistantService
{
    public function __construct(
        private readonly AIProviderFactory $aiFactory,
        // We'll mock the analytics context for now if AnalyticsService doesn't exist yet, but let's assume we can fetch basic data directly from User
    ) {}

    public function chat(User $user, array $conversationHistory): string
    {
        Log::info("Starting AI chat for user: " . $user->id);

        // Context injection: give the AI context about the user's financial state
        $systemPrompt = $this->buildSystemPrompt($user);

        // Merge system prompt into the LAST user message so the AI always has the latest context
        // and to avoid unsupported "system" roles in Gemini API
        $messages = [];
        $lastUserIndex = -1;
        foreach ($conversationHistory as $index => $msg) {
            if ($msg['role'] === 'user') {
                $lastUserIndex = $index;
            }
        }

        foreach ($conversationHistory as $index => $msg) {
            if ($index === $lastUserIndex) {
                $messages[] = [
                    'role' => 'user',
                    'content' => "[System Instructions (Do not reply to this part directly)]\n" . $systemPrompt . "\n\n[User's Message]\n" . $msg['content']
                ];
            } else {
                $messages[] = $msg;
            }
        }

        $providersToTry = ['gemini_direct', 'openrouter_free', 'openrouter_llama', 'openrouter_qwen'];

        foreach ($providersToTry as $providerKey) {
            try {
                $provider = $this->aiFactory->getProvider($providerKey);
                return $provider->chat($messages, [
                    'temperature' => 0.5,
                    'max_tokens' => 1024,
                ]);
            } catch (\Exception $e) {
                Log::warning("AI Chat failed with {$providerKey}: " . $e->getMessage());
                $this->aiFactory->tripCircuit($providerKey);
                // Continue to next provider
            }
        }

        Log::error("All AI providers failed for user: " . $user->id);
        throw new \RuntimeException("Semua server AI sedang penuh (High Demand). Silakan coba lagi dalam beberapa menit.");
    }

    private function buildSystemPrompt(User $user): string
    {
        // Gather basic financial context
        $accounts = $user->accounts()->get(['name', 'balance', 'currency']);
        $totalBalance = $accounts->sum('balance');
        $currency = $user->currency ?? 'IDR';
        
        $recentTransactions = $user->transactions()
            ->with('category')
            ->latest('date')
            ->take(5)
            ->get()
            ->map(function ($tx) {
                return "- {$tx->date->format('Y-m-d')}: {$tx->type} {$tx->amount} ({$tx->category->name}) - {$tx->description}";
            })->join("\n");

        $accountsText = $accounts->map(function ($acc) {
            return "- {$acc->name}: {$acc->currency} {$acc->balance}";
        })->join("\n");

        return <<<PROMPT
You are FinFlow AI, a highly intelligent, empathetic, and professional personal finance assistant.
Your goal is to help the user manage their finances, analyze their spending, and give actionable financial advice.

Context about the user:
- Name: {$user->name}
- Total Balance: {$currency} {$totalBalance}
- Active Accounts:
{$accountsText}

Recent Transactions (last 5):
{$recentTransactions}

Guidelines:
1. Be concise but helpful. Format your responses using markdown.
2. If asked about balances or transactions, use the context provided.
3. If you don't know the exact answer from the context, state what you know and give general advice.
4. Keep the tone friendly but professional. Use Indonesian language by default unless the user speaks in English.
PROMPT;
    }
}
