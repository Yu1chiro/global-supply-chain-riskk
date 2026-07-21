<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;
use App\Models\Country;

// Kelas CurrencyController: currency controller
class CurrencyController extends Controller
{
    protected ExchangeRateService $exchangeRateService;

    // inisialisasi objek
    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    // get rates
    public function getRates(Request $request)
    {
        $base = $request->base ?? 'USD';
        $rates = $this->exchangeRateService->getExchangeRates($base);

        if (!$rates) {
            return response()->json(['error' => 'Failed to fetch exchange rates'], 500);
        }

        return response()->json($rates);
    }

    // get country rates
    public function getCountryRates(string $countryCode)
    {
        $country = Country::where('code', $countryCode)->first();

        if (!$country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        $baseCurrency = $country->currency ?? 'USD';
        $rates = $this->exchangeRateService->getExchangeRates($baseCurrency);

        if (!$rates) {
            return response()->json(['error' => 'Failed to fetch exchange rates'], 500);
        }

        return response()->json([
            'country' => $country->name,
            'base_currency' => $baseCurrency,
            'rates' => $rates['rates'] ?? [],
            'date' => $rates['date'] ?? now()->toDateString(),
        ]);
    }

    // get currency risk
    public function getCurrencyRisk(Request $request)
    {
        $request->validate([
            'base' => 'nullable|string|size:3',
        ]);

        $base = $request->base ?? 'USD';
        $rates = $this->exchangeRateService->getExchangeRates($base);

        if (!$rates) {
            return response()->json(['error' => 'Failed to fetch exchange rates'], 500);
        }

        $risk = $this->exchangeRateService->getCurrencyRisk($rates);

        return response()->json([
            'base_currency' => $base,
            'risk_score' => $risk,
            'risk_level' => $risk < 20 ? 'Low' : ($risk < 50 ? 'Medium' : 'High'),
            'rates' => $rates['rates'] ?? [],
        ]);
    }

    // get all currencies
    public function getAllCurrencies()
    {
        $countries = Country::select('code', 'name', 'currency', 'currency_symbol')->get();

        $currencies = [];
        foreach ($countries as $country) {
            if ($country->currency) {
                $currencies[$country->currency] = [
                    'country' => $country->name,
                    'code' => $country->code,
                    'symbol' => $country->currency_symbol,
                ];
            }
        }

        return response()->json($currencies);
    }
}