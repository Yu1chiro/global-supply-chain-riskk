<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

// Kelas WeatherService: weather service
class WeatherService
{
    protected string $baseUrl = 'https://api.open-meteo.com/v1';

    
    // get weather
    public function getWeather(float $lat, float $lon): ?array
    {
        
        
        $cacheKey = 'weather_' . round($lat, 2) . '_' . round($lon, 2);

        return Cache::remember($cacheKey, 1800, function () use ($lat, $lon) {
            try {
                $response = Http::get($this->baseUrl . '/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'current_weather' => true,
                    'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,windspeed_10m_max',
                    'timezone' => 'auto'
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            } catch (\Exception $e) {
                Log::error('Weather API Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    
    // get weather bulk
    public function getWeatherBulk(array $coords): array
    {
        $results = [];
        $toFetch = [];

        
        foreach ($coords as $countryId => $coord) {
            $cacheKey = 'weather_' . round($coord['lat'], 2) . '_' . round($coord['lon'], 2);
            if (Cache::has($cacheKey)) {
                $results[$countryId] = Cache::get($cacheKey);
            } else {
                $toFetch[$countryId] = $coord;
            }
        }

        if (empty($toFetch)) {
            return $results;
        }

        $responses = Http::pool(fn ($pool) => array_map(
            fn ($countryId) => $pool->as($countryId)->get($this->baseUrl . '/forecast', [
                'latitude' => $toFetch[$countryId]['lat'],
                'longitude' => $toFetch[$countryId]['lon'],
                'current_weather' => true,
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,windspeed_10m_max',
                'timezone' => 'auto'
            ]),
            array_keys($toFetch)
        ));

        foreach ($toFetch as $countryId => $coord) {
            try {
                $response = $responses[$countryId];

                if ($response instanceof \Throwable || !$response->successful()) {
                    $results[$countryId] = null;
                    continue;
                }

                $data = $response->json();
                $results[$countryId] = $data;

                $cacheKey = 'weather_' . round($coord['lat'], 2) . '_' . round($coord['lon'], 2);
                Cache::put($cacheKey, $data, 1800);
            } catch (\Exception $e) {
                Log::error('Weather Bulk API Error: ' . $e->getMessage());
                $results[$countryId] = null;
            }
        }

        return $results;
    }

    // get weather risk
    public function getWeatherRisk(?array $weatherData): int
    {
        if (!$weatherData || !isset($weatherData['current_weather'])) {
            return 0;
        }

        $risk = 0;
        $current = $weatherData['current_weather'];

        $windSpeed = $current['windspeed'] ?? 0;
        if ($windSpeed > 60) $risk += 40;
        elseif ($windSpeed > 40) $risk += 25;
        elseif ($windSpeed > 20) $risk += 10;

        $temp = $current['temperature'] ?? 0;
        if ($temp > 40 || $temp < -10) $risk += 20;
        elseif ($temp > 35 || $temp < -5) $risk += 10;

        $daily = $weatherData['daily'] ?? [];
        if (isset($daily['precipitation_sum']) && !empty($daily['precipitation_sum'])) {
            $maxPrecip = max($daily['precipitation_sum']);
            if ($maxPrecip > 50) $risk += 30;
            elseif ($maxPrecip > 25) $risk += 15;
            elseif ($maxPrecip > 10) $risk += 5;
        }

        return min($risk, 100);
    }

    // get storm risk
    public function getStormRisk(?array $weatherData): string
    {
        if (!$weatherData) return 'No Risk';

        $current = $weatherData['current_weather'] ?? [];
        $windSpeed = $current['windspeed'] ?? 0;

        if ($windSpeed > 80) return 'Extreme Storm Risk';
        if ($windSpeed > 60) return 'High Storm Risk';
        if ($windSpeed > 40) return 'Moderate Storm Risk';
        if ($windSpeed > 20) return 'Low Storm Risk';
        return 'No Storm Risk';
    }

    // get weather description
    public function getWeatherDescription(?array $weatherData): string
    {
        if (!$weatherData || !isset($weatherData['current_weather'])) {
            return 'Weather data unavailable';
        }

        $current = $weatherData['current_weather'];
        $temp = $current['temperature'] ?? 0;
        $wind = $current['windspeed'] ?? 0;

        $desc = "Temperature: {$temp}°C, Wind: {$wind} km/h";

        if ($wind > 40) $desc .= " ⚠️ Storm Warning!";
        elseif ($temp > 35) $desc .= " ☀️ Extreme Heat!";
        elseif ($temp < 0) $desc .= " ❄️ Freezing!";

        return $desc;
    }
}
