<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

// Kelas ExchangeRateService: exchange rate service
class ExchangeRateService
{
    protected string $baseUrl = 'https://api.exchangerate-api.com/v4/latest';

    
    // get exchange rates
    public function getExchangeRates(string $baseCurrency = 'USD'): ?array
    {
        $cacheKey = "exchange_rates_{$baseCurrency}";

        return Cache::remember($cacheKey, 3600, function () use ($baseCurrency) {
            try {
                $response = Http::get($this->baseUrl . "/{$baseCurrency}");

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'base' => $data['base'] ?? 'USD',
                        'rates' => $data['rates'] ?? [],
                        'date' => $data['date'] ?? now()->toDateString(),
                    ];
                }

                return null;
            } catch (\Exception $e) {
                Log::error('ExchangeRate API Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    
    // get exchange rates bulk
    public function getExchangeRatesBulk(array $currencies): array
    {
        $results = [];
        $toFetch = []; 

        foreach ($currencies as $countryId => $currency) {
            $currency = $currency ?: 'USD';
            $cacheKey = "exchange_rates_{$currency}";

            if (Cache::has($cacheKey)) {
                $results[$countryId] = Cache::get($cacheKey);
            } else {
                $toFetch[$currency] = true; 
                $results[$countryId] = null; 
            }
        }

        $uniqueCurrencies = array_keys($toFetch);

        if (!empty($uniqueCurrencies)) {
            $responses = Http::pool(fn ($pool) => array_map(
                fn ($currency) => $pool->as($currency)->get("{$this->baseUrl}/{$currency}"),
                $uniqueCurrencies
            ));

            $fetchedData = [];
            foreach ($uniqueCurrencies as $currency) {
                try {
                    $response = $responses[$currency];
                    if ($response instanceof \Throwable || !$response->successful()) {
                        continue;
                    }

                    $data = $response->json();
                    $parsed = [
                        'base' => $data['base'] ?? $currency,
                        'rates' => $data['rates'] ?? [],
                        'date' => $data['date'] ?? now()->toDateString(),
                    ];

                    Cache::put("exchange_rates_{$currency}", $parsed, 3600);
                    $fetchedData[$currency] = $parsed;
                } catch (\Exception $e) {
                    Log::error("ExchangeRate bulk error ({$currency}): " . $e->getMessage());
                }
            }

            
            foreach ($currencies as $countryId => $currency) {
                $currency = $currency ?: 'USD';
                if ($results[$countryId] === null && isset($fetchedData[$currency])) {
                    $results[$countryId] = $fetchedData[$currency];
                }
            }
        }

        return $results;
    }

    // get currency risk
    public function getCurrencyRisk(?array $rateData): int
    {
        if (!$rateData || !isset($rateData['rates'])) {
            return 0;
        }

        $rates = $rateData['rates'];
        $risk = 0;

        foreach ($rates as $currency => $rate) {
            if ($rate > 100) $risk += 5;
            if ($rate < 0.01) $risk += 10;
        }

        $values = array_values($rates);
        if (count($values) > 1) {
            $mean = array_sum($values) / count($values);
            $variance = 0;
            foreach ($values as $value) {
                $variance += pow($value - $mean, 2);
            }
            $stdDev = sqrt($variance / count($values));
            $volatility = ($stdDev / $mean) * 100;

            if ($volatility > 10) $risk += 30;
            elseif ($volatility > 5) $risk += 15;
            elseif ($volatility > 2) $risk += 5;
        }

        return min($risk, 100);
    }

    // get currency trend
    public function getCurrencyTrend(array $historicalData): string
    {
        if (!$historicalData || count($historicalData) < 2) {
            return 'Stable';
        }

        $last = $historicalData[count($historicalData) - 1] ?? 0;
        $first = $historicalData[0] ?? 0;

        if ($first == 0) return 'Stable';

        $change = (($last - $first) / $first) * 100;

        if ($change > 5) return 'Strengthening';
        if ($change > 1) return 'Slight Strengthening';
        if ($change > -1) return 'Stable';
        if ($change > -5) return 'Slight Weakening';
        return 'Weakening';
    }
}
