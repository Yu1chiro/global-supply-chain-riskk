<?php

namespace App\Services;

use App\Models\Country;
use App\Models\RiskScore;
use App\Services\WeatherService;
use App\Services\ExchangeRateService;
use App\Services\SentimentAnalysisService;
use App\Services\NewsService;

// Kelas RiskScoringService: risk scoring service
class RiskScoringService
{
    protected WeatherService $weatherService;
    protected ExchangeRateService $exchangeRateService;
    protected SentimentAnalysisService $sentimentService;
    protected NewsService $newsService;

    
    protected array $weights = [
        'weather' => 30,
        'inflation' => 20,
        'political_news' => 40,
        'currency' => 10,
    ];

    // inisialisasi objek
    public function __construct(
        WeatherService $weatherService,
        ExchangeRateService $exchangeRateService,
        SentimentAnalysisService $sentimentService,
        NewsService $newsService
    ) {
        $this->weatherService = $weatherService;
        $this->exchangeRateService = $exchangeRateService;
        $this->sentimentService = $sentimentService;
        $this->newsService = $newsService;
    }

    // calculate risk
    public function calculateRisk(Country $country): array
    {
        
        
        
        $weatherData = $country->weather_data;
        if ($country->latitude && $country->longitude) {
            $liveWeather = $this->weatherService->getWeather(
                (float) $country->latitude,
                (float) $country->longitude
            );
            if ($liveWeather) {
                $weatherData = $liveWeather;
                $country->weather_data = $liveWeather;
                $country->save();
            }
        }
        $weatherRisk = $this->weatherService->getWeatherRisk($weatherData);

        
        $inflationRisk = $this->calculateInflationRisk($country->inflation);

        
        $exchangeData = $this->exchangeRateService->getExchangeRates($country->currency ?? 'USD');
        $currencyRisk = $this->exchangeRateService->getCurrencyRisk($exchangeData);

        
        $newsRisk = $this->calculateNewsRisk($country);

        
        $totalRisk = (
            ($weatherRisk * $this->weights['weather'] / 100) +
            ($inflationRisk * $this->weights['inflation'] / 100) +
            ($newsRisk * $this->weights['political_news'] / 100) +
            ($currencyRisk * $this->weights['currency'] / 100)
        );

        $riskLevel = $this->getRiskLevel($totalRisk);

        return [
            'weather_risk' => round($weatherRisk, 2),
            'inflation_risk' => round($inflationRisk, 2),
            'currency_risk' => round($currencyRisk, 2),
            'political_risk' => round($newsRisk, 2),
            'news_sentiment_risk' => round($newsRisk, 2),
            'total_risk' => round($totalRisk, 2),
            'risk_level' => $riskLevel,
            'weights' => $this->weights,
            'raw_data' => [
                'weather' => $weatherData,
                'exchange' => $exchangeData,
                'inflation' => $country->inflation,
            ]
        ];
    }

    // calculate inflation risk
    protected function calculateInflationRisk(?float $inflationRate): float
    {
        if ($inflationRate === null) {
            return 0;
        }

        if ($inflationRate > 20) return 100;
        if ($inflationRate > 15) return 80;
        if ($inflationRate > 10) return 60;
        if ($inflationRate > 5) return 40;
        if ($inflationRate > 3) return 20;
        if ($inflationRate > 0) return 10;
        return 0;
    }

    // calculate news risk
    protected function calculateNewsRisk(Country $country): float
    {
        $news = $this->newsService->getCountryNews($country->code, 5);

        if (empty($news)) {
            return 0;
        }

        $texts = [];
        foreach ($news as $article) {
            $title = $article['title'] ?? '';
            $description = $article['description'] ?? '';
            $texts[] = $title . ' ' . $description;
        }

        $distribution = $this->sentimentService->getSentimentDistribution($texts);

        
        $negativeSentiment = $distribution['Negative'] ?? 0;
        $risk = $negativeSentiment * 1.5; 

        return min($risk, 100);
    }

    
    // calculate risk bulk
    public function calculateRiskBulk($countries): array
    {
        $coords = [];
        $currencies = [];
        $codes = [];

        foreach ($countries as $country) {
            if ($country->latitude && $country->longitude) {
                $coords[$country->id] = ['lat' => (float) $country->latitude, 'lon' => (float) $country->longitude];
            }
            $currencies[$country->id] = $country->currency ?? 'USD';
            $codes[$country->id] = $country->code;
        }

        $weatherResults = $this->weatherService->getWeatherBulk($coords);
        $exchangeResults = $this->exchangeRateService->getExchangeRatesBulk($currencies);
        $newsResults = $this->newsService->getCountryNewsBulk($codes, 5);

        $results = [];

        foreach ($countries as $country) {
            $weatherData = $weatherResults[$country->id] ?? $country->weather_data;
            if ($weatherData) {
                $country->weather_data = $weatherData;
                $country->save();
            }
            $weatherRisk = $this->weatherService->getWeatherRisk($weatherData);

            $inflationRisk = $this->calculateInflationRisk($country->inflation);

            $exchangeData = $exchangeResults[$country->id] ?? null;
            $currencyRisk = $this->exchangeRateService->getCurrencyRisk($exchangeData);

            $news = $newsResults[$country->id] ?? [];
            $newsRisk = $this->calculateNewsRiskFromArticles($news);

            $totalRisk = (
                ($weatherRisk * $this->weights['weather'] / 100) +
                ($inflationRisk * $this->weights['inflation'] / 100) +
                ($newsRisk * $this->weights['political_news'] / 100) +
                ($currencyRisk * $this->weights['currency'] / 100)
            );

            $results[$country->id] = [
                'weather_risk' => round($weatherRisk, 2),
                'inflation_risk' => round($inflationRisk, 2),
                'currency_risk' => round($currencyRisk, 2),
                'political_risk' => round($newsRisk, 2),
                'news_sentiment_risk' => round($newsRisk, 2),
                'total_risk' => round($totalRisk, 2),
                'risk_level' => $this->getRiskLevel($totalRisk),
                'weights' => $this->weights,
                'raw_data' => [
                    'weather' => $weatherData,
                    'exchange' => $exchangeData,
                    'inflation' => $country->inflation,
                ]
            ];
        }

        return $results;
    }

    
    // save risk score from result
    public function saveRiskScoreFromResult(Country $country, array $result): RiskScore
    {
        return RiskScore::create([
            'country_id' => $country->id,
            'weather_risk' => $result['weather_risk'],
            'inflation_risk' => $result['inflation_risk'],
            'currency_risk' => $result['currency_risk'],
            'political_risk' => $result['political_risk'],
            'news_sentiment_risk' => $result['news_sentiment_risk'],
            'total_risk' => $result['total_risk'],
            'risk_level' => $result['risk_level'],
            'raw_data' => $result['raw_data'],
            'calculated_at' => now(),
        ]);
    }

    
    // calculate news risk from articles
    protected function calculateNewsRiskFromArticles(array $news): float
    {
        if (empty($news)) {
            return 0;
        }

        $texts = [];
        foreach ($news as $article) {
            $title = $article['title'] ?? '';
            $description = $article['description'] ?? '';
            $texts[] = $title . ' ' . $description;
        }

        $distribution = $this->sentimentService->getSentimentDistribution($texts);
        $negativeSentiment = $distribution['Negative'] ?? 0;
        $risk = $negativeSentiment * 1.5;

        return min($risk, 100);
    }

    // get risk level
    protected function getRiskLevel(float $score): string
    {
        if ($score < 30) return 'Low';
        if ($score < 60) return 'Medium';
        return 'High';
    }

    // save risk score
    public function saveRiskScore(Country $country): RiskScore
    {
        $result = $this->calculateRisk($country);

        $riskScore = RiskScore::create([
            'country_id' => $country->id,
            'weather_risk' => $result['weather_risk'],
            'inflation_risk' => $result['inflation_risk'],
            'currency_risk' => $result['currency_risk'],
            'political_risk' => $result['political_risk'],
            'news_sentiment_risk' => $result['news_sentiment_risk'],
            'total_risk' => $result['total_risk'],
            'risk_level' => $result['risk_level'],
            'raw_data' => $result['raw_data'],
            'calculated_at' => now(),
        ]);

        return $riskScore;
    }

    // predict risk
    public function predictRisk(array $data): array
    {
        
        $score = 0;

        if (isset($data['weather'])) {
            $score += $data['weather'] * $this->weights['weather'] / 100;
        }

        if (isset($data['inflation'])) {
            $inflationRisk = $this->calculateInflationRisk($data['inflation']);
            $score += $inflationRisk * $this->weights['inflation'] / 100;
        }

        if (isset($data['currency'])) {
            $currencyRisk = $data['currency'];
            $score += $currencyRisk * $this->weights['currency'] / 100;
        }

        if (isset($data['news_sentiment'])) {
            $score += $data['news_sentiment'] * $this->weights['political_news'] / 100;
        }

        return [
            'total_risk' => round($score, 2),
            'risk_level' => $this->getRiskLevel($score),
            'weights_used' => $this->weights,
        ];
    }

    
    // get weights
    public function getWeights(): array
    {
        return $this->weights;
    }

    
    // set weights
    public function setWeights(array $weights): void
    {
        $this->weights = $weights;
    }
}
