<?php

namespace App\Services\AI;

use App\Contracts\AI\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService implements AIProviderInterface
{
    private string $model;
    private string $baseUrl;
    private string $apiKey;

    public function __construct(
        string $model = 'kimi-k2.6:cloud',
        string $baseUrl = 'http://localhost:11434/api',
        string $apiKey = ''
    ) {
        $this->model = $model;
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    public function chat(array $messages, array $options = []): string
    {
        try {
            $response = Http::timeout(120)
                ->withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}/chat", [
                    'model' => $this->model,
                    'messages' => $messages,
                    'stream' => false,
                ]);

            if ($response->failed() || isset($response->json()['error'])) {
                $errMsg = $response->json('error') ?? $response->body();
                throw new \RuntimeException("Ollama API Error: " . (is_array($errMsg) ? json_encode($errMsg) : $errMsg));
            }

            return $response->json('message.content') ?? '';
        } catch (\Exception $e) {
            Log::error("Ollama Chat Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function parseReceipt(string $imageBase64, string $mimeType): array
    {
        $prompt = "Analyze this receipt or transfer proof image. Extract information into a strict JSON format with these keys: 
        - merchant_name: string (e.g. 'PT LION SUPER INDO' or sender's name if transfer received)
        - date: string (YYYY-MM-DD)
        - subtotal_amount: number (raw numeric value before tax/admin)
        - tax_amount: number
        - admin_fee: number (including any service charge or admin fee)
        - total_discount: number
        - total_amount: number (raw numeric value, the final paid amount)
        - currency: string (3-letter code, e.g. 'IDR')
        - type: string (must be exactly 'expense' if it's a purchase/shopping, or 'income' if it's a transfer received/deposit)
        - items: array of objects {name, price_per_unit, quantity, discount, total_price}
        
        Return ONLY the JSON object. Do not include markdown blocks.";

        try {
            $response = Http::timeout(120)
                ->withHeaders($this->getHeaders())
                ->post("{$this->baseUrl}/chat", [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                            'images' => [$imageBase64]
                        ]
                    ],
                    'stream' => false,
                ]);

            $fullJson = $response->json();
            Log::info("Full Ollama Response: " . json_encode($fullJson));

            if ($response->failed() || isset($fullJson['error'])) {
                $errMsg = $fullJson['error'] ?? $response->body();
                throw new \RuntimeException("Ollama API Error: " . (is_array($errMsg) ? json_encode($errMsg) : $errMsg));
            }

            $raw = data_get($fullJson, 'message.content', '');

            // Clean JSON
            $clean = preg_replace('/^```json\s*|\s*```$/m', '', $raw);
            $firstBrace = strpos($clean, '{');
            $lastBrace = strrpos($clean, '}');
            if ($firstBrace !== false && $lastBrace !== false) {
                $clean = substr($clean, $firstBrace, $lastBrace - $firstBrace + 1);
            }

            return json_decode($clean, true) ?? [];
        } catch (\Exception $e) {
            Log::error("Ollama Parse Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function generateInsight(array $financialContext): string
    {
        return $this->chat([
            ['role' => 'user', 'content' => "Provide a short financial insight for: " . json_encode($financialContext)]
        ]);
    }

    public function isAvailable(): bool
    {
        return true; // Ollama can be running locally without an API key
    }

    public function getProviderName(): string
    {
        return "Ollama ({$this->model})";
    }

    public function withModel(string $model): static
    {
        $clone = clone $this;
        $clone->model = $model;
        return $clone;
    }

    private function getHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (!empty($this->apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        return $headers;
    }
}
