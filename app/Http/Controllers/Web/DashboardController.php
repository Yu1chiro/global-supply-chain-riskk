<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\RiskScore;
use App\Models\NewsCache;
use App\Models\Port;
use App\Services\ExchangeRateService;
use App\Services\WorldBankService;
use App\Services\WeatherService;
use App\Services\GeographyService;
use App\Services\RestCountriesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

// Kelas DashboardController: dashboard controller
class DashboardController extends Controller
{
    protected ExchangeRateService $exchangeRateService;
    protected WorldBankService $worldBankService;
    protected WeatherService $weatherService;
    protected GeographyService $geographyService;
    protected RestCountriesService $restCountriesService;

    // inisialisasi objek
    public function __construct(
        ExchangeRateService $exchangeRateService,
        WorldBankService $worldBankService,
        WeatherService $weatherService,
        GeographyService $geographyService,
        RestCountriesService $restCountriesService
    ) {
        $this->exchangeRateService = $exchangeRateService;
        $this->worldBankService = $worldBankService;
        $this->weatherService = $weatherService;
        $this->geographyService = $geographyService;
        $this->restCountriesService = $restCountriesService;
    }

    
    // index
    public function index()
    {
        $riskSummary = $this->getRiskSummary();
        $latestRates = $this->exchangeRateService->getExchangeRates('USD');

        $activePorts = Port::where('status', 'active')->count();
        $totalPorts = Port::count();

        $latestNews = NewsCache::with('country')->latest('published_at')->limit(5)->get();
        $sentimentSummary = $this->getSentimentSummary();

        $currencyOverview = collect($this->getCurrencyData())->take(6);

        
        
        
        
        
        $recentActivities = class_exists(\App\Models\ActivityLog::class)
            ? \App\Models\ActivityLog::with('user')
                ->where('user_id', Auth::id())
                ->latest()
                ->limit(8)
                ->get()
            : collect();

        $highRiskCountries = Country::with('latestRiskScore')
            ->whereHas('latestRiskScore', fn($q) => $q->where('risk_level', 'High'))
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'riskSummary',
            'latestRates',
            'activePorts',
            'totalPorts',
            'latestNews',
            'sentimentSummary',
            'currencyOverview',
            'recentActivities',
            'highRiskCountries'
        ));
    }

    
    // countries index
    public function countriesIndex()
    {
        $countries = Country::with('latestRiskScore')->get();
        $riskSummary = $this->getRiskSummary();
        $latestRates = $this->exchangeRateService->getExchangeRates('USD');

        return view('countries.index', compact('countries', 'riskSummary', 'latestRates'));
    }

    
    // analytics
    public function analytics()
    {
        $riskSummary = $this->getRiskSummary();
        $topRiskCountries = Country::with('latestRiskScore')
            ->whereHas('latestRiskScore')
            ->get()
            ->sortByDesc(fn($c) => $c->latestRiskScore->total_risk ?? 0)
            ->take(10)
            ->values();

        return view('analytics.index', compact('riskSummary', 'topRiskCountries'));
    }

    // country dashboard
    public function countryDashboard(int $id)
    {
        $country = Country::with(['latestRiskScore', 'riskScores' => function($query) {
            $query->latest()->limit(30);
        }, 'newsCache' => function($query) {
            $query->latest('published_at')->limit(10);
        }])->findOrFail($id);

        
        
        
        \App\Models\ActivityLog::record('country.viewed', $country, Auth::id(), [
            'name' => $country->name,
            'code' => $country->code,
        ]);

        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $needsBackfill = !$country->gdp || !$country->inflation || !$country->population
            || !$country->exports || !$country->imports || !$country->reserves
            || !$country->region || !$country->subregion || empty($country->languages);

        if ($needsBackfill || ($country->latitude && $country->longitude)) {
            dispatch(function () use ($country) {
                $country->refresh();

                if ($country->latitude && $country->longitude) {
                    $weatherData = $this->weatherService->getWeather(
                        (float) $country->latitude,
                        (float) $country->longitude
                    );
                    if ($weatherData) {
                        $country->weather_data = $weatherData;
                    }
                }

                if (!$country->gdp) {
                    $gdp = $this->worldBankService->getGDP($country->code);
                    if ($gdp) $country->gdp = $gdp;
                }
                if (!$country->inflation) {
                    $inflation = $this->worldBankService->getInflation($country->code);
                    if ($inflation) $country->inflation = $inflation;
                }
                if (!$country->population) {
                    $population = $this->worldBankService->getPopulation($country->code);
                    if ($population) $country->population = $population;
                }
                if (!$country->exports) {
                    $exports = $this->worldBankService->getExports($country->code);
                    if ($exports) $country->exports = $exports;
                }
                if (!$country->imports) {
                    $imports = $this->worldBankService->getImports($country->code);
                    if ($imports) $country->imports = $imports;
                }
                if (!$country->reserves) {
                    $reserves = $this->worldBankService->getReserves($country->code);
                    if ($reserves) $country->reserves = $reserves;
                }

                if (!$country->region || !$country->subregion || empty($country->languages)) {
                    $info = $this->restCountriesService->getCountryInfo($country->code);
                    if ($info) {
                        $country->region = $info['region'] ?? $country->region;
                        $country->subregion = $info['subregion'] ?? $country->subregion;
                        $country->languages = !empty($info['languages']) ? $info['languages'] : $country->languages;
                        $country->capital = $country->capital ?? ($info['capital'] ?? null);
                        $country->flag_url = $country->flag_url ?? ($info['flag_url'] ?? null);
                        if (!$country->currency && $info['currency_code']) {
                            $country->currency = $info['currency_code'];
                        }
                        if (!$country->currency_symbol && $info['currency_symbol']) {
                            $country->currency_symbol = $info['currency_symbol'];
                        }
                        if (!$country->population && $info['population']) {
                            $country->population = $info['population'];
                        }
                    }
                }

                $country->save();
            })->afterResponse();
        }

        
        
        
        
        $geoSummary = $this->geographyService->getSummary($country->name);

        
        $ports = $country->ports();

        $riskTrend = $country->riskScores->map(function($score) {
            return [
                'date' => $score->calculated_at->format('Y-m-d'),
                'risk' => $score->total_risk,
            ];
        });

        
        
        $economicHistory = Cache::remember("economic_history_{$country->code}", 86400, function () use ($country) {
            return [
                'gdp' => $this->worldBankService->getGDPHistory($country->code, 10),
                'inflation' => $this->worldBankService->getInflationHistory($country->code, 10),
            ];
        });

        $gdpTrend = $economicHistory['gdp'];
        $inflationTrend = $economicHistory['inflation'];

        return view('dashboard.country', compact('country', 'riskTrend', 'gdpTrend', 'inflationTrend', 'geoSummary', 'ports'));
    }

    // weather monitoring
    public function weatherMonitoring()
    {
        
        
        
        
        
        
        
        
        
        
        
        $countries = Country::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('latestRiskScore')
            ->get();

        return view('dashboard.weather', compact('countries'));
    }

    // currency dashboard
    public function currencyDashboard()
    {
        $rates = $this->exchangeRateService->getExchangeRates('USD');
        $currencies = $this->getCurrencyData();

        return view('dashboard.currency', compact('rates', 'currencies'));
    }

    // news dashboard
    public function newsDashboard()
    {
        $news = NewsCache::with('country')
            ->latest('published_at')
            ->limit(50)
            ->get();

        $sentimentSummary = $this->getSentimentSummary();

        
        
        
        $articles = \App\Models\Article::where('is_published', true)
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.news', compact('news', 'sentimentSummary', 'articles'));
    }

    // news by category
    public function newsByCategory(string $category)
    {
        $news = NewsCache::with('country')
            ->where('category', $category)
            ->latest('published_at')
            ->limit(50)
            ->get();

        $sentimentSummary = $this->getSentimentSummary();

        $articles = \App\Models\Article::where('is_published', true)
            ->where('category', $category)
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.news', compact('news', 'sentimentSummary', 'articles'));
    }

    // port dashboard
    public function portDashboard()
    {
        return view('dashboard.ports');
    }

    // comparison
    public function comparison()
    {
        $countries = Country::all();
        return view('dashboard.comparison', compact('countries'));
    }

    // comparison data
    public function comparisonData(Request $request)
    {
        $request->validate([
            'country1' => 'required|exists:countries,id',
            'country2' => 'required|exists:countries,id',
        ]);

        $country1 = Country::with('latestRiskScore')->find($request->country1);
        $country2 = Country::with('latestRiskScore')->find($request->country2);

        return view('dashboard.comparison-result', compact('country1', 'country2'));
    }

    // watchlist
    public function watchlist()
    {
        $user = Auth::user();
        $watchlist = [];

        if ($user) {
            $watchlist = $user->watchlists()->with('country')->get();
        }

        $allCountries = Country::orderBy('name')->get();
        $watchedIds = collect($watchlist)->pluck('country_id')->all();

        return view('dashboard.watchlist', compact('watchlist', 'allCountries', 'watchedIds'));
    }

    // get risk summary
    protected function getRiskSummary(): array
    {
        return [
            'total' => Country::count(),
            'low' => RiskScore::where('risk_level', 'Low')->distinct('country_id')->count(),
            'medium' => RiskScore::where('risk_level', 'Medium')->distinct('country_id')->count(),
            'high' => RiskScore::where('risk_level', 'High')->distinct('country_id')->count(),
            'average' => RiskScore::avg('total_risk') ?? 0,
        ];
    }

    // get sentiment summary
    protected function getSentimentSummary(): array
    {
        $news = NewsCache::all();
        $counts = [
            'positive' => $news->where('sentiment_result', 'Positive')->count(),
            'neutral' => $news->where('sentiment_result', 'Neutral')->count(),
            'negative' => $news->where('sentiment_result', 'Negative')->count(),
        ];

        $total = array_sum($counts);
        if ($total > 0) {
            foreach ($counts as $key => $value) {
                $counts[$key . '_percent'] = round(($value / $total) * 100, 2);
            }
        } else {
            $counts['positive_percent'] = 0;
            $counts['neutral_percent'] = 0;
            $counts['negative_percent'] = 0;
        }

        return $counts;
    }

    // get currency data
    protected function getCurrencyData(): array
    {
        $countries = Country::whereNotNull('currency')->get();
        $data = [];

        foreach ($countries as $country) {
            $data[] = [
                'country' => $country->name,
                'currency' => $country->currency,
                'symbol' => $country->currency_symbol ?? $country->currency,
                'code' => $country->code,
            ];
        }

        return $data;
    }
}
