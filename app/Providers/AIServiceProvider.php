<?php

namespace App\Providers;

use App\Services\AI\OpenRouterService;
use App\Services\AI\AIProviderFactory;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OpenRouterService::class, function ($app) {
            return new OpenRouterService(
                apiKey: config('ai.openrouter.api_key'),
                model: config('ai.openrouter.default_model', 'anthropic/claude-3-5-sonnet')
            );
        });

        $this->app->singleton(\App\Services\AI\GeminiService::class, function ($app) {
            return new \App\Services\AI\GeminiService(config('ai.gemini_api_key'));
        });

        $this->app->singleton(\App\Services\AI\OllamaService::class, function ($app) {
            return new \App\Services\AI\OllamaService(
                'kimi-k2.6:cloud',
                'https://ollama.com/api',
                'be47fd64e6b1404ab4dc70114120b96f.Hk_D_rxrPWEmz_8wscM8hj1N'
            );
        });

        $this->app->singleton(AIProviderFactory::class, function ($app) {
            return new AIProviderFactory(
                $app->make(OpenRouterService::class),
                $app->make(\App\Services\AI\GeminiService::class),
                $app->make(\App\Services\AI\OllamaService::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
