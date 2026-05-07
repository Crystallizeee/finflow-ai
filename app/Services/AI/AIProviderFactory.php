<?php

namespace App\Services\AI;

use App\Contracts\AI\AIProviderInterface;
use Illuminate\Support\Facades\Cache;

class AIProviderFactory
{
    private array $providers;
    private array $fallbackOrder = ['openrouter_claude', 'openrouter_gpt4', 'openrouter_gemini'];

    public function __construct(
        private readonly OpenRouterService $openRouter,
        private readonly GeminiService $gemini,
        private readonly OllamaService $ollama,
    ) {
        $this->providers = [
            'gemini_direct'     => fn() => $this->gemini->withModel('gemini-1.5-flash'),
            'ollama_kimi'       => fn() => $this->ollama,
            'openrouter_free'   => fn() => $this->openRouter->withModel('google/gemini-2.0-flash-001'),
            'openrouter_llama'  => fn() => $this->openRouter->withModel('meta-llama/llama-3.1-8b-instruct:free'),
            'openrouter_qwen'   => fn() => $this->openRouter->withModel('qwen/qwen-2.5-72b-instruct:free'),
        ];
    }

    public function getProvider(string $preferred = 'gemini_direct'): AIProviderInterface
    {
        $order = array_unique([$preferred, 'gemini_direct', 'openrouter_free', 'openrouter_claude']);

        foreach ($order as $key) {
            if ($this->isCircuitOpen($key)) continue;
            if (!isset($this->providers[$key])) continue;

            $provider = ($this->providers[$key])();
            if ($provider->isAvailable()) {
                return $provider;
            }
        }

        throw new \RuntimeException('All AI providers are unavailable');
    }

    private function isCircuitOpen(string $key): bool
    {
        return Cache::get("ai_circuit_open:{$key}", false);
    }

    public function tripCircuit(string $key): void
    {
        Cache::put("ai_circuit_open:{$key}", true, now()->addMinutes(5));
    }
}
