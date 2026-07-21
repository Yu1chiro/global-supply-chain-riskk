<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\WorldBankService;
use App\Services\WeatherService;
use App\Services\RestCountriesService;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

// Kelas CountryController: country controller
class CountryController extends Controller
{
    protected WorldBankService $worldBankService;
    protected WeatherService $weatherService;
    protected RestCountriesService $restCountriesService;
    protected RiskScoringService $riskScoringService;

    // inisialisasi objek
    public function __construct(
        WorldBankService $worldBankService,
        WeatherService $weatherService,
        RestCountriesService $restCountriesService,
        RiskScoringService $riskScoringService
    ) {
        $this->worldBankService = $worldBankService;
        $this->weatherService = $weatherService;
        $this->restCountriesService = $restCountriesService;
        $this->riskScoringService = $riskScoringService;
    }

    
    // apply rest countries data
    protected function applyRestCountriesData(Country $country): void
    {
        $info = $this->restCountriesService->getCountryInfo($country->code);
        if (!$info) {
            return;
        }

        $country->region = $info['region'] ?? $country->region;
        $country->subregion = $info['subregion'] ?? $country->subregion;
        $country->languages = $info['languages'] ?? $country->languages;
        $country->capital = $info['capital'] ?? $country->capital;
        $country->flag_url = $info['flag_url'] ?? $country->flag_url;

        
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

    
    // apply world bank data
    protected function applyWorldBankData(Country $country, bool $force = false): void
    {
        $needsFetch = $force
            || !$country->gdp
            || !$country->inflation
            || !$country->population
            || !$country->exports
            || !$country->imports
            || !$country->reserves;

        if (!$needsFetch) {
            return;
        }

        
        
        $indicators = $this->worldBankService->getAllIndicators($country->code);

        if ($force || !$country->gdp) {
            if ($indicators['gdp']) $country->gdp = $indicators['gdp'];
        }
        if ($force || !$country->inflation) {
            if ($indicators['inflation']) $country->inflation = $indicators['inflation'];
        }
        if ($force || !$country->population) {
            if ($indicators['population']) $country->population = $indicators['population'];
        }
        if ($force || !$country->exports) {
            if ($indicators['exports']) $country->exports = $indicators['exports'];
        }
        if ($force || !$country->imports) {
            if ($indicators['imports']) $country->imports = $indicators['imports'];
        }
        if ($force || !$country->reserves) {
            if ($indicators['reserves']) $country->reserves = $indicators['reserves'];
        }
    }

    // index
    public function index()
    {
        $countries = Country::with('latestRiskScore')->get();
        return response()->json($countries);
    }

    
    // search
    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);
        $q = $request->query('q');

        
        
        
        $localMatches = Country::where('name', 'like', "%{$q}%")
            ->orWhere('code', 'like', "%{$q}%")
            ->limit(15)
            ->get();

        $results = $localMatches->map(function ($country) {
            return [
                'name' => $country->name,
                'code' => $country->code,
                'capital' => $country->capital ?? null,
                'region' => $country->region ?? null,
                'flag_url' => $country->flag_url ?? null,
                'latitude' => $country->latitude,
                'longitude' => $country->longitude,
                'already_added' => true,
                'country_id' => $country->id,
            ];
        })->all();

        
        
        
        
        if (empty($results)) {
            $remoteResults = $this->restCountriesService->searchByName($q);

            $existingCodes = Country::whereIn('code', array_column($remoteResults, 'code'))
                ->pluck('id', 'code');

            $results = array_map(function ($item) use ($existingCodes) {
                $item['already_added'] = $existingCodes->has($item['code']);
                $item['country_id'] = $existingCodes->get($item['code']);
                return $item;
            }, $remoteResults);
        }

        return response()->json($results);
    }

    
    // add by code
    public function addByCode(string $code)
    {
        $code = strtoupper($code);

        $country = Country::whereCode($code)->first();

        if ($country) {
            return response()->json($country);
        }

        $info = $this->restCountriesService->getCountryInfo($code);

        if (!$info || !$info['name']) {
            return response()->json(['error' => 'Negara tidak ditemukan'], 404);
        }

        $country = new Country();
        $country->code = $code;
        $country->name = $info['name'];
        $country->capital = $info['capital'];
        $country->region = $info['region'];
        $country->subregion = $info['subregion'];
        $country->languages = $info['languages'];
        $country->currency = $info['currency_code'];
        $country->currency_symbol = $info['currency_symbol'];
        $country->population = $info['population'];
        $country->flag_url = $info['flag_url'];
        $country->latitude = $info['latitude'];
        $country->longitude = $info['longitude'];
        $country->external_synced_at = now();
        $country->save();

        
        $this->applyWorldBankData($country, force: true);

        
        if ($country->latitude && $country->longitude) {
            $weather = $this->weatherService->getWeather((float) $country->latitude, (float) $country->longitude);
            if ($weather) {
                $country->weather_data = $weather;
            }
        }

        $country->save();

        
        $this->riskScoringService->saveRiskScore($country);

        return response()->json($country, 201);
    }

    // show
    public function show(int $id)
    {
        $country = Country::with(['latestRiskScore', 'riskScores' => function($query) {
            $query->latest()->limit(30);
        }])->findOrFail($id);

        return response()->json($country);
    }

    // get country data
    public function getCountryData(string $code)
    {
        $country = Country::whereCode($code)->first();

        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        
        if ($country->latitude && $country->longitude) {
            $weatherData = $this->weatherService->getWeather(
                (float) $country->latitude,
                (float) $country->longitude
            );
            if ($weatherData) {
                $country->weather_data = $weatherData;
                $country->save();
            }
        }

        
        $this->applyWorldBankData($country);

        
        
        if (!$country->external_synced_at || $country->external_synced_at->lt(now()->subDays(7))) {
            $this->applyRestCountriesData($country);
            $country->external_synced_at = now();
        }

        $country->save();

        return response()->json($country);
    }

    
    // get economic history
    public function getEconomicHistory(int $id)
    {
        $country = Country::findOrFail($id);

        $cacheKey = "economic_history_{$country->code}";

        $history = Cache::remember($cacheKey, 86400, function () use ($country) {
            return [
                'gdp' => $this->worldBankService->getGDPHistory($country->code, 10),
                'inflation' => $this->worldBankService->getInflationHistory($country->code, 10),
            ];
        });

        return response()->json($history);
    }

    // sync all countries
    public function syncAllCountries()
    {
        $countries = Country::all();
        $results = [];

        
        
        
        
        
        
        
        
        
        
        
        

        $coords = [];
        $codes = [];
        foreach ($countries as $country) {
            if ($country->latitude && $country->longitude) {
                $coords[$country->id] = ['lat' => (float) $country->latitude, 'lon' => (float) $country->longitude];
            }
            $codes[$country->id] = $country->code;
        }

        $weatherResults = $this->weatherService->getWeatherBulk($coords);
        $worldBankResults = $this->worldBankService->getAllIndicatorsBulk($codes);

        foreach ($countries as $country) {
            if (isset($weatherResults[$country->id]) && $weatherResults[$country->id]) {
                $country->weather_data = $weatherResults[$country->id];
            }

            
            $indicators = $worldBankResults[$country->id] ?? null;
            if ($indicators) {
                if ($indicators['gdp']) $country->gdp = $indicators['gdp'];
                if ($indicators['inflation']) $country->inflation = $indicators['inflation'];
                if ($indicators['population']) $country->population = $indicators['population'];
                if ($indicators['exports']) $country->exports = $indicators['exports'];
                if ($indicators['imports']) $country->imports = $indicators['imports'];
                if ($indicators['reserves']) $country->reserves = $indicators['reserves'];
            }

            
            $this->applyRestCountriesData($country);
            $country->external_synced_at = now();

            $country->save();

            $results[] = $country->name;
        }

        
        
        
        $riskResults = $this->riskScoringService->calculateRiskBulk($countries);
        foreach ($countries as $country) {
            if (isset($riskResults[$country->id])) {
                $this->riskScoringService->saveRiskScoreFromResult($country, $riskResults[$country->id]);
            }
        }

        return response()->json([
            'message' => 'Sync completed',
            'countries' => $results
        ]);
    }

    // compare
    public function compare(Request $request)
    {
        $request->validate([
            'country1' => 'required|exists:countries,code',
            'country2' => 'required|exists:countries,code',
        ]);

        $country1 = Country::with('latestRiskScore')->whereCode($request->country1)->first();
        $country2 = Country::with('latestRiskScore')->whereCode($request->country2)->first();

        return response()->json([
            'country1' => $country1,
            'country2' => $country2,
            'comparison' => $this->compareCountries($country1, $country2),
        ]);
    }

    // compare countries
    protected function compareCountries(Country $c1, Country $c2): array
    {
        $fields = ['name', 'gdp', 'inflation', 'population', 'exports', 'imports', 'currency', 'region'];

        $comparison = [];
        foreach ($fields as $field) {
            $comparison[$field] = [
                'country1' => $c1->$field,
                'country2' => $c2->$field,
            ];
        }

        
        $risk1 = $c1->latestRiskScore;
        $risk2 = $c2->latestRiskScore;

        $comparison['risk'] = [
            'country1' => $risk1 ? $risk1->total_risk : null,
            'country2' => $risk2 ? $risk2->total_risk : null,
        ];

        $comparison['risk_level'] = [
            'country1' => $risk1 ? $risk1->risk_level : 'Unknown',
            'country2' => $risk2 ? $risk2->risk_level : 'Unknown',
        ];

        return $comparison;
    }
}
