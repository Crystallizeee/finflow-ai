<?php

namespace App\Services\AI;

use App\Contracts\AI\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService implements AIProviderInterface
{
    private string $model;
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct(
        private readonly string $apiKey,
        string $model = 'gemini-2.5-flash',
    ) {
        $this->model = $model;
    }

    public function chat(array $messages, array $options = []): string
    {
        try {
            $contents = [];
            foreach ($messages as $msg) {
                $contents[] = [
                    'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $msg['content']]],
                ];
            }

            $response = Http::timeout(120)->post(self::BASE_URL . "/models/{$this->model}:generateContent?key={$this->apiKey}", [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => $options['temperature'] ?? 0.3,
                    'maxOutputTokens' => $options['max_tokens'] ?? 2048,
                ],
            ]);

            if ($response->failed()) {
                throw new \RuntimeException("Gemini API error: " . $response->body());
            }

            return $response->json('candidates.0.content.parts.0.text') ?? '';
        } catch (\Exception $e) {
            Log::error("Gemini Chat Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function parseReceipt(string $imageBase64, string $mimeType): array
    {
        $prompt = "Analyze this receipt or transfer proof image carefully. 
        - Extract every single line item into the 'items' array.
        - For each item, capture EXACT name as written, price, and quantity.
        - Calculate raw numeric values for all amounts. DO NOT include currency symbols or separators in numeric fields.
        - Field 'category_suggestion' MUST be one of these standardized categories:
        'Makanan & Minuman', 'Kesehatan & Kecantikan', 'Kebersihan & Rumah Tangga', 'Bayi & Anak-anak', 'Hewan Peliharaan', 'Elektronik & Elektrik', 'Olahraga & Outdoor', 'Pakaian & Mode', 'Alat Tulis Kantor', 'Teknologi & Gadget', 'Otomotif', 'Lainnya'.
        
        Strict JSON format:
        {
          \"merchant_name\": \"string\",
          \"date\": \"YYYY-MM-DD\",
          \"subtotal_amount\": number,
          \"tax_amount\": number,
          \"admin_fee\": number,
          \"total_discount\": number,
          \"total_amount\": number,
          \"currency\": \"IDR\",
          \"type\": \"expense\"|\"income\",
          \"items\": [
            {\"name\": \"string\", \"category_suggestion\": \"string\", \"price_per_unit\": number, \"quantity\": number, \"discount\": number, \"total_price\": number}
          ]
        }
        Return ONLY the JSON object. Do not include markdown blocks or any other text.";

        try {
            $response = Http::timeout(180)->post(self::BASE_URL . "/models/{$this->model}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $imageBase64,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 4096, // Increase to handle long receipts
                ],
            ]);

            $fullJson = $response->json();
            
            if ($response->failed() || isset($fullJson['error'])) {
                $errMsg = $fullJson['error']['message'] ?? $response->body();
                Log::error("Gemini API Error Body: " . $response->body());
                throw new \RuntimeException("Gemini API Error: " . $errMsg);
            }
            
            $raw = data_get($fullJson, 'candidates.0.content.parts.0.text', '');
            Log::info("Raw Gemini Output (Length: " . strlen($raw) . "): " . $raw);

            // Robust JSON extraction
            $clean = trim($raw);
            $clean = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $clean);
            
            $firstBrace = strpos($clean, '{');
            $lastBrace = strrpos($clean, '}');
            
            if ($firstBrace !== false && $lastBrace !== false) {
                $clean = substr($clean, $firstBrace, $lastBrace - $firstBrace + 1);
            }

            $decoded = json_decode($clean, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("JSON Decode Error: " . json_last_error_msg() . " | Input Snippet: " . substr($clean, 0, 100) . "...");
                // Try simple fix: if it's truncated, AI might have stopped mid-JSON
                if (json_last_error() === JSON_ERROR_CTRL_CHAR || json_last_error() === JSON_ERROR_SYNTAX) {
                     Log::warning("Attempting to fix malformed JSON from AI...");
                }
                return [];
            }

            return $decoded ?? [];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gemini Parse Error: " . $e->getMessage());
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
        return !empty($this->apiKey);
    }

    public function getProviderName(): string
    {
        return "Google Gemini ({$this->model})";
    }

    public function withModel(string $model): static
    {
        $clone = clone $this;
        $clone->model = $model;
        return $clone;
    }
}
