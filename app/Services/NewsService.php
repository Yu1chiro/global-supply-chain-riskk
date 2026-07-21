<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

// Kelas NewsService: news service
class NewsService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://gnews.io/api/v4';

    // inisialisasi objek
    public function __construct()
    {
        $this->apiKey = env('GNEWS_API_KEY', '');
    }

    // get news
    public function getNews(string $query = 'logistics trade shipping economy', ?string $country = null, int $limit = 20): array
    {
        
        
        
        if (empty($this->apiKey)) {
            Log::warning('GNEWS_API_KEY is not set. Returning empty result (no dummy data).');
            return [];
        }

        $cacheKey = "news_" . md5($query . $country . $limit);

        return Cache::remember($cacheKey, 1800, function () use ($query, $country, $limit) {
            try {
                $params = [
                    'q' => $query,
                    'max' => $limit,
                    'apikey' => $this->apiKey,
                    'lang' => 'en',
                ];

                if ($country) {
                    $params['country'] = $country;
                }

                $response = Http::timeout(15)->get($this->baseUrl . '/search', $params);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['articles']) && count($data['articles']) > 0) {
                        return $data['articles'];
                    }

                    Log::info('No articles found for query: ' . $query);
                    return [];
                }

                
                
                
                Log::error('GNews API Error: ' . $response->status() . ' - ' . $response->body());
                return [];

            } catch (\Exception $e) {
                Log::error('News API Exception: ' . $e->getMessage());
                return [];
            }
        });
    }

    
    // get country news bulk
    public function getCountryNewsBulk(array $countryCodes, int $limit = 5): array
    {
        $results = [];

        if (empty($this->apiKey)) {
            foreach ($countryCodes as $countryId => $code) {
                $results[$countryId] = [];
            }
            return $results;
        }

        $countryNames = [
            'US' => 'United States', 'ID' => 'Indonesia', 'CN' => 'China',
            'DE' => 'Germany', 'AU' => 'Australia', 'GB' => 'United Kingdom',
            'JP' => 'Japan', 'BR' => 'Brazil', 'IN' => 'India', 'SG' => 'Singapore',
        ];

        $toFetch = [];
        foreach ($countryCodes as $countryId => $code) {
            $countryName = $countryNames[$code] ?? $code;
            $query = "{$countryName} economy trade logistics";
            $cacheKey = "news_" . md5($query . null . $limit);

            if (Cache::has($cacheKey)) {
                $results[$countryId] = Cache::get($cacheKey);
            } else {
                $toFetch[$countryId] = $query;
            }
        }

        if (empty($toFetch)) {
            return $results;
        }

        $responses = Http::pool(fn ($pool) => array_map(
            fn ($countryId) => $pool->as($countryId)->timeout(15)->get($this->baseUrl . '/search', [
                'q' => $toFetch[$countryId],
                'max' => $limit,
                'apikey' => $this->apiKey,
                'lang' => 'en',
            ]),
            array_keys($toFetch)
        ));

        foreach ($toFetch as $countryId => $query) {
            $articles = [];
            try {
                $response = $responses[$countryId];
                if (!($response instanceof \Throwable) && $response->successful()) {
                    $data = $response->json();
                    $articles = $data['articles'] ?? [];
                } elseif ($response instanceof \Throwable) {
                    Log::error("News bulk error (country_id {$countryId}): " . $response->getMessage());
                }
            } catch (\Exception $e) {
                Log::error("News bulk parse error (country_id {$countryId}): " . $e->getMessage());
            }

            $cacheKey = "news_" . md5($query . null . $limit);
            Cache::put($cacheKey, $articles, 1800);
            $results[$countryId] = $articles;
        }

        return $results;
    }

    // get logistics news
    public function getLogisticsNews(int $limit = 10): array
    {
        return $this->getNews('logistics shipping supply chain', null, $limit);
    }

    // get economy news
    public function getEconomyNews(int $limit = 10): array
    {
        return $this->getNews('economy trade business finance', null, $limit);
    }

    // get trade news
    public function getTradeNews(int $limit = 10): array
    {
        return $this->getNews('trade import export tariffs', null, $limit);
    }

    // get country news
    public function getCountryNews(string $countryCode, int $limit = 10): array
    {
        $countryNames = [
            'US' => 'United States', 'ID' => 'Indonesia', 'CN' => 'China',
            'DE' => 'Germany', 'AU' => 'Australia', 'GB' => 'United Kingdom',
            'JP' => 'Japan', 'BR' => 'Brazil', 'IN' => 'India', 'SG' => 'Singapore',
        ];

        $countryName = $countryNames[$countryCode] ?? $countryCode;
        return $this->getNews("{$countryName} economy trade logistics", null, $limit);
    }
}
