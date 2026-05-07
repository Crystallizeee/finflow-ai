<?php

namespace App\Services\AI;

use App\Contracts\AI\AIProviderInterface;
use App\Exceptions\AI\AIProviderException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService implements AIProviderInterface
{
    private string $model;
    private const BASE_URL = 'https://openrouter.ai/api/v1';
    private const TIMEOUT = 30;

    public function __construct(
        private readonly string $apiKey,
        string $model = 'anthropic/claude-3-5-sonnet',
    ) {
        $this->model = $model;
    }

    public function withModel(string $model): static
    {
        $clone = clone $this;
        $clone->model = $model;
        return $clone;
    }

    public function chat(array $messages, array $options = []): string
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->withHeaders([
                    'HTTP-Referer' => config('app.url'),
                    'X-OpenRouter-Title' => config('app.name'),
                ])
                ->timeout(self::TIMEOUT)
                ->post(self::BASE_URL . '/chat/completions', [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'max_tokens'  => $options['max_tokens'] ?? 1024,
                    'temperature' => $options['temperature'] ?? 0.3,
                ]);

            if ($response->failed()) {
                throw new AIProviderException("OpenRouter API error: " . $response->body());
            }

            return $response->json('choices.0.message.content') ?? throw new AIProviderException('Empty response from OpenRouter');
        } catch (\Exception $e) {
            Log::error("AI Chat Error: " . $e->getMessage());
            throw new AIProviderException("Failed to communicate with AI provider: " . $e->getMessage());
        }
    }

    public function parseReceipt(string $imageBase64, string $mimeType): array
    {
        $messages = [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:{$mimeType};base64,{$imageBase64}",
                        ],
                    ],
                    [
                        'type' => 'text',
                        'text' => $this->buildReceiptPrompt(),
                    ],
                ],
            ],
        ];

        $raw = $this->chat($messages, ['max_tokens' => 2048, 'temperature' => 0.1]);

        return $this->parseReceiptResponse($raw);
    }

    public function generateInsight(array $financialContext): string
    {
        $messages = [
            ['role' => 'system', 'content' => $this->buildInsightSystemPrompt()],
            ['role' => 'user', 'content' => json_encode($financialContext, JSON_UNESCAPED_UNICODE)],
        ];

        return $this->chat($messages, ['max_tokens' => 512, 'temperature' => 0.5]);
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    public function getProviderName(): string
    {
        return "OpenRouter ({$this->model})";
    }

    private function buildReceiptPrompt(): string
    {
        return "Analyze this receipt image. Extract information into a strict JSON format with these keys: 
        - merchant_name: string (e.g. 'PT LION SUPER INDO')
        - date: string (YYYY-MM-DD)
        - total_amount: number (raw numeric value, e.g. 1002500)
        - currency: string (3-letter code, e.g. 'IDR')
        - items: array of objects {name, price, quantity}
        
        IMPORTANT: Return ONLY the JSON object. Do not include any explanation or markdown blocks unless necessary. If data is missing, use null.";
    }

    private function parseReceiptResponse(string $raw): array
    {
        Log::info("Raw AI Receipt Response: " . $raw);

        // Remove markdown blocks if present
        $clean = preg_replace('/^```json\s*|\s*```$/m', '', $raw);
        
        // Find the first { and last } to isolate JSON
        $firstBrace = strpos($clean, '{');
        $lastBrace = strrpos($clean, '}');
        
        if ($firstBrace !== false && $lastBrace !== false) {
            $clean = substr($clean, $firstBrace, $lastBrace - $firstBrace + 1);
        }

        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Failed to decode AI response: " . json_last_error_msg());
            return [];
        }

        return $decoded;
    }

    private function buildInsightSystemPrompt(): string
    {
        return "You are a financial advisor for Gen-Z. Provide short, witty, and actionable financial insights based on the provided data.";
    }
}
