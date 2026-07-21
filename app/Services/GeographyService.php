<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Kelas GeographyService: geography service
class GeographyService
{
    protected string $baseUrl = 'https://countriesnow.space/api/v0.1/countries';

    
    // get states
    public function getStates(string $countryName): array
    {
        $cacheKey = 'geo_states_' . strtolower($countryName);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($countryName) {
            try {
                $response = Http::timeout(10)->post("{$this->baseUrl}/states", [
                    'country' => $countryName,
                ]);

                if (!$response->successful()) {
                    return [];
                }

                $data = $response->json();
                $states = $data['data']['states'] ?? [];

                return array_map(fn ($s) => $s['name'] ?? $s, $states);
            } catch (\Exception $e) {
                Log::warning("GeographyService: gagal ambil states untuk {$countryName}: " . $e->getMessage());
                return [];
            }
        });
    }

    
    // get cities
    public function getCities(string $countryName): array
    {
        $cacheKey = 'geo_cities_' . strtolower($countryName);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($countryName) {
            try {
                $response = Http::timeout(15)->post("{$this->baseUrl}/cities", [
                    'country' => $countryName,
                ]);

                if (!$response->successful()) {
                    return [];
                }

                $data = $response->json();

                return $data['data'] ?? [];
            } catch (\Exception $e) {
                Log::warning("GeographyService: gagal ambil cities untuk {$countryName}: " . $e->getMessage());
                return [];
            }
        });
    }

    
    // get summary
    public function getSummary(string $countryName): array
    {
        $statesCacheKey = 'geo_states_' . strtolower($countryName);
        $citiesCacheKey = 'geo_cities_' . strtolower($countryName);

        $states = Cache::get($statesCacheKey);
        $cities = Cache::get($citiesCacheKey);

        
        if ($states !== null && $cities !== null) {
            return ['provinces' => count($states), 'cities' => count($cities)];
        }

        $jobs = [];
        if ($states === null) $jobs['states'] = true;
        if ($cities === null) $jobs['cities'] = true;

        $responses = Http::pool(fn ($pool) => array_map(
            fn ($job) => $job === 'states'
                ? $pool->as('states')->timeout(10)->post("{$this->baseUrl}/states", ['country' => $countryName])
                : $pool->as('cities')->timeout(15)->post("{$this->baseUrl}/cities", ['country' => $countryName]),
            array_keys($jobs)
        ));

        if ($states === null) {
            $states = $this->parseStatesResponse($responses['states'] ?? null, $countryName);
            Cache::put($statesCacheKey, $states, now()->addDays(7));
        }

        if ($cities === null) {
            $cities = $this->parseCitiesResponse($responses['cities'] ?? null, $countryName);
            Cache::put($citiesCacheKey, $cities, now()->addDays(7));
        }

        return ['provinces' => count($states), 'cities' => count($cities)];
    }

    // parse states response
    private function parseStatesResponse($response, string $countryName): array
    {
        try {
            if (!$response || $response instanceof \Throwable || !$response->successful()) {
                return [];
            }
            $data = $response->json();
            $states = $data['data']['states'] ?? [];
            return array_map(fn ($s) => $s['name'] ?? $s, $states);
        } catch (\Exception $e) {
            Log::warning("GeographyService: gagal parse states untuk {$countryName}: " . $e->getMessage());
            return [];
        }
    }

    // parse cities response
    private function parseCitiesResponse($response, string $countryName): array
    {
        try {
            if (!$response || $response instanceof \Throwable || !$response->successful()) {
                return [];
            }
            $data = $response->json();
            return $data['data'] ?? [];
        } catch (\Exception $e) {
            Log::warning("GeographyService: gagal parse cities untuk {$countryName}: " . $e->getMessage());
            return [];
        }
    }
}
