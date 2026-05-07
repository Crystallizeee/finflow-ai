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
        - items: array of objects {name, category_suggestion, price_per_unit, quantity, discount, total_price}
        
        Field 'category_suggestion' MUST be one of these standardized categories if applicable:
        - 'Makanan & Minuman' (Food, drinks, fresh produce, snacks, frozen food)
        - 'Kesehatan & Kecantikan' (Medicine, skincare, hair care, toiletries)
        - 'Kebersihan & Rumah Tangga' (Detergents, cleaning tools, household needs)
        - 'Bayi & Anak-anak' (Formula, diapers, baby care, toys)
        - 'Hewan Peliharaan' (Pet food, pet care)
        - 'Elektronik & Elektrik' (Batteries, light bulbs, small appliances)
        - 'Olahraga & Outdoor' (Sporting goods, camping gear)
        - 'Pakaian & Mode' (Clothing, accessories, fashion)
        - 'Alat Tulis Kantor' (Pens, notebooks, office supplies)
        - 'Teknologi & Gadget' (Smartphone accessories, small gadgets)
        - 'Otomotif' (Engine oil, car care)
        
        Return ONLY the JSON object. Do not include markdown blocks.";

        try {
            $response = Http::timeout(120)->post(self::BASE_URL . "/models/{$this->model}:generateContent?key={$this->apiKey}", [
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
            ]);

            $fullJson = $response->json();
            \Illuminate\Support\Facades\Log::info("Full Gemini Response: " . json_encode($fullJson));
            
            if ($response->failed() || isset($fullJson['error'])) {
                $errMsg = $fullJson['error']['message'] ?? $response->body();
                throw new \RuntimeException("Gemini API Error: " . $errMsg);
            }
            
            $raw = data_get($fullJson, 'candidates.0.content.parts.0.text', '');

            // Clean JSON
            $clean = preg_replace('/^```json\s*|\s*```$/m', '', $raw);
            $firstBrace = strpos($clean, '{');
            $lastBrace = strrpos($clean, '}');
            if ($firstBrace !== false && $lastBrace !== false) {
                $clean = substr($clean, $firstBrace, $lastBrace - $firstBrace + 1);
            }

            return json_decode($clean, true) ?? [];
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
