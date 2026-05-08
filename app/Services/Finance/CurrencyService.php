<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    /**
     * Get exchange rates with caching.
     * We use a free fallback if API key is not provided, 
     * but recommended to use a proper provider for production.
     */
    public function getRates(string $base = 'IDR'): array
    {
        return Cache::remember("exchange_rates_{$base}", now()->addHours(12), function () use ($base) {
            try {
                // Using ExchangeRate-API (Free Tier - No Key sometimes works, but better with key)
                $response = Http::timeout(10)->get("https://open.er-api.com/v6/latest/{$base}");

                if ($response->successful()) {
                    return $response->json()['rates'] ?? [];
                }

                Log::warning("Failed to fetch exchange rates for {$base}");
                return [];
            } catch (\Exception $e) {
                Log::error("Currency API Error: " . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Convert amount from one currency to another.
     */
    public function convert(float $amount, string $from, string $to = 'IDR'): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rates = $this->getRates($from);

        if (isset($rates[$to])) {
            return $amount * $rates[$to];
        }

        // If rate not found in $from base, try fetching from $to base and invert
        $reverseRates = $this->getRates($to);
        if (isset($reverseRates[$from]) && $reverseRates[$from] > 0) {
            return $amount / $reverseRates[$from];
        }

        Log::error("Conversion rate not found from {$from} to {$to}");
        return $amount; // Return original if conversion fails
    }

    /**
     * Get a list of supported currencies.
     */
    public function getSupportedCurrencies(): array
    {
        return [
            'IDR' => 'Indonesian Rupiah',
            'USD' => 'US Dollar',
            'SGD' => 'Singapore Dollar',
            'EUR' => 'Euro',
            'JPY' => 'Japanese Yen',
            'MYR' => 'Malaysian Ringgit',
            'AUD' => 'Australian Dollar',
            'GBP' => 'British Pound',
        ];
    }
}
