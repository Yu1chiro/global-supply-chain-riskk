<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Kelas WorldBankService: world bank service
class WorldBankService
{
    protected $baseUrl = 'https://api.worldbank.org/v2/country';

    
    protected array $indicators = [
        'gdp'        => 'NY.GDP.MKTP.CD',
        'inflation'  => 'FP.CPI.TOTL.ZG',
        'population' => 'SP.POP.TOTL',
        'exports'    => 'NE.EXP.GNFS.CD',
        'imports'    => 'NE.IMP.GNFS.CD',
        'reserves'   => 'FI.RES.TOTL.CD',
    ];

    
    // get all indicators
    public function getAllIndicators(string $countryCode): array
    {
        $cacheKey = "worldbank_all_{$countryCode}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($countryCode) {
            $keys = array_keys($this->indicators);

            $responses = Http::pool(fn ($pool) => array_map(
                fn ($indicatorCode) => $pool->as($indicatorCode)
                    ->timeout(15)
                    ->get("{$this->baseUrl}/{$countryCode}/indicator/{$indicatorCode}", [
                        'format' => 'json',
                    ]),
                $this->indicators
            ));

            $result = [
                'gdp' => null,
                'inflation' => null,
                'population' => null,
                'exports' => null,
                'imports' => null,
                'reserves' => null,
            ];

            foreach ($this->indicators as $field => $indicatorCode) {
                try {
                    $response = $responses[$indicatorCode];

                    if ($response instanceof \Throwable) {
                        Log::error("WorldBank pool error ({$field}/{$countryCode}): " . $response->getMessage());
                        continue;
                    }

                    if (!$response->successful()) {
                        continue;
                    }

                    $data = $response->json();
                    if (isset($data[1]) && !empty($data[1])) {
                        $latest = $data[1][0]['value'] ?? null;
                        if ($latest !== null) {
                            $result[$field] = $field === 'population'
                                ? (int) $latest
                                : (float) $latest;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("WorldBank pool parse error ({$field}/{$countryCode}): " . $e->getMessage());
                }
            }

            return $result;
        });
    }

    
    // get all indicators bulk
    public function getAllIndicatorsBulk(array $countryCodes): array
    {
        $results = [];
        $toFetch = [];

        foreach ($countryCodes as $countryId => $code) {
            $cacheKey = "worldbank_all_{$code}";
            if (Cache::has($cacheKey)) {
                $results[$countryId] = Cache::get($cacheKey);
            } else {
                $toFetch[$countryId] = $code;
            }
        }

        if (empty($toFetch)) {
            return $results;
        }

        
        $jobs = [];
        foreach ($toFetch as $countryId => $code) {
            foreach ($this->indicators as $field => $indicatorCode) {
                $jobs["{$countryId}:{$field}"] = ['code' => $code, 'indicatorCode' => $indicatorCode];
            }
        }

        $responses = Http::pool(fn ($pool) => array_map(
            fn ($jobKey) => $pool->as($jobKey)
                ->timeout(15)
                ->get("{$this->baseUrl}/{$jobs[$jobKey]['code']}/indicator/{$jobs[$jobKey]['indicatorCode']}", [
                    'format' => 'json',
                ]),
            array_keys($jobs)
        ));

        foreach ($toFetch as $countryId => $code) {
            $result = [
                'gdp' => null, 'inflation' => null, 'population' => null,
                'exports' => null, 'imports' => null, 'reserves' => null,
            ];

            foreach ($this->indicators as $field => $indicatorCode) {
                $jobKey = "{$countryId}:{$field}";
                try {
                    $response = $responses[$jobKey];

                    if ($response instanceof \Throwable || !$response->successful()) {
                        continue;
                    }

                    $data = $response->json();
                    if (isset($data[1]) && !empty($data[1])) {
                        $latest = $data[1][0]['value'] ?? null;
                        if ($latest !== null) {
                            $result[$field] = $field === 'population' ? (int) $latest : (float) $latest;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("WorldBank bulk pool error ({$jobKey}): " . $e->getMessage());
                }
            }

            Cache::put("worldbank_all_{$code}", $result, now()->addDay());
            $results[$countryId] = $result;
        }

        return $results;
    }

    // get country data
    public function getCountryData($countryCode)
    {
        $cacheKey = "worldbank_country_meta_{$countryCode}";

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($countryCode) {
            try {
                $response = Http::timeout(15)->get($this->baseUrl . "/{$countryCode}?format=json");

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data[1][0])) {
                        return $data[1][0];
                    }
                }

                return null;
            } catch (\Exception $e) {
                Log::error('WorldBank API Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    // get g d p
    public function getGDP($countryCode)
    {
        return $this->getAllIndicators($countryCode)['gdp'] ?? null;
    }

    // get inflation
    public function getInflation($countryCode)
    {
        return $this->getAllIndicators($countryCode)['inflation'] ?? null;
    }

    
    // get population
    public function getPopulation($countryCode)
    {
        return $this->getAllIndicators($countryCode)['population'] ?? null;
    }

    
    // get exports
    public function getExports($countryCode)
    {
        return $this->getAllIndicators($countryCode)['exports'] ?? null;
    }

    
    // get imports
    public function getImports($countryCode)
    {
        return $this->getAllIndicators($countryCode)['imports'] ?? null;
    }

    
    // get reserves
    public function getReserves($countryCode)
    {
        return $this->getAllIndicators($countryCode)['reserves'] ?? null;
    }

    
    // get g d p history
    public function getGDPHistory($countryCode, int $years = 10): array
    {
        return $this->getIndicatorHistory($countryCode, 'NY.GDP.MKTP.CD', $years);
    }

    
    // get inflation history
    public function getInflationHistory($countryCode, int $years = 10): array
    {
        return $this->getIndicatorHistory($countryCode, 'FP.CPI.TOTL.ZG', $years);
    }

    
    // get indicator history
    private function getIndicatorHistory($countryCode, string $indicator, int $years): array
    {
        $cacheKey = "worldbank_history_{$indicator}_{$countryCode}_{$years}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($countryCode, $indicator, $years) {
            try {
                $response = Http::timeout(15)->get($this->baseUrl . "/{$countryCode}/indicator/{$indicator}", [
                    'format' => 'json',
                    'per_page' => $years * 2,
                ]);

                if (!$response->successful()) {
                    return [];
                }

                $data = $response->json();
                if (!isset($data[1]) || empty($data[1])) {
                    return [];
                }

                $history = [];
                foreach ($data[1] as $entry) {
                    if (isset($entry['value']) && $entry['value'] !== null) {
                        $history[] = [
                            'year' => $entry['date'],
                            'value' => (float) $entry['value'],
                        ];
                    }
                }

                $history = array_reverse($history);

                return array_slice($history, -$years);
            } catch (\Exception $e) {
                Log::error("WorldBank Indicator History Error ({$indicator}): " . $e->getMessage());
                return [];
            }
        });
    }
}
