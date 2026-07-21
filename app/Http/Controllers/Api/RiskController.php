<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\RiskScore;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;

// Kelas RiskController: risk controller
class RiskController extends Controller
{
    protected $riskScoringService;

    // inisialisasi objek
    public function __construct(RiskScoringService $riskScoringService)
    {
        $this->riskScoringService = $riskScoringService;
    }

    // index
    public function index(Request $request)
    {
        $query = RiskScore::with('country');

        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->has('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        $scores = $query->latest('calculated_at')->paginate(20);
        return response()->json($scores);
    }

    // get latest risk
    public function getLatestRisk($countryId)
    {
        $risk = RiskScore::with('country')
            ->where('country_id', $countryId)
            ->latest('calculated_at')
            ->first();

        if (!$risk) {
            return response()->json(['error' => 'No risk data found'], 404);
        }

        return response()->json($risk);
    }

    // calculate
    public function calculate(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $country = Country::find($request->country_id);
        $riskScore = $this->riskScoringService->saveRiskScore($country);

        return response()->json($riskScore);
    }

    // calculate all
    public function calculateAll()
    {
        
        
        
        
        
        $countries = Country::all();
        $riskResults = $this->riskScoringService->calculateRiskBulk($countries);
        $results = [];

        foreach ($countries as $country) {
            $result = $riskResults[$country->id] ?? null;
            if (!$result) {
                continue;
            }

            $riskScore = $this->riskScoringService->saveRiskScoreFromResult($country, $result);
            $results[] = [
                'country' => $country->name,
                'risk' => $riskScore->total_risk,
                'level' => $riskScore->risk_level,
            ];
        }

        return response()->json([
            'message' => 'Risk scores calculated for all countries',
            'results' => $results,
        ]);
    }

    // predict
    public function predict(Request $request)
    {
        $request->validate([
            'weather' => 'nullable|numeric|min:0|max:100',
            'inflation' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|numeric|min:0|max:100',
            'news_sentiment' => 'nullable|numeric|min:0|max:100',
        ]);

        $prediction = $this->riskScoringService->predictRisk($request->all());

        return response()->json($prediction);
    }

    // get risk summary
    public function getRiskSummary()
    {
        $summary = [
            'total_countries' => Country::count(),
            'risks' => [
                'Low' => RiskScore::where('risk_level', 'Low')
                    ->latest('calculated_at')
                    ->distinct('country_id')
                    ->count(),
                'Medium' => RiskScore::where('risk_level', 'Medium')
                    ->latest('calculated_at')
                    ->distinct('country_id')
                    ->count(),
                'High' => RiskScore::where('risk_level', 'High')
                    ->latest('calculated_at')
                    ->distinct('country_id')
                    ->count(),
            ],
            'average_risk' => RiskScore::avg('total_risk'),
            'highest_risk' => RiskScore::with('country')
                ->latest('calculated_at')
                ->orderBy('total_risk', 'desc')
                ->first(),
            'lowest_risk' => RiskScore::with('country')
                ->latest('calculated_at')
                ->orderBy('total_risk', 'asc')
                ->first(),
        ];

        return response()->json($summary);
    }

    // get risk trend
    public function getRiskTrend($countryId)
    {
        
        
        
        
        
        $trend = RiskScore::where('country_id', $countryId)
            ->where('calculated_at', '>=', now()->subDays(30))
            ->orderBy('calculated_at')
            ->get(['calculated_at', 'total_risk', 'risk_level'])
            ->groupBy(fn ($row) => $row->calculated_at->format('Y-m-d'))
            ->map(function ($rows, $date) {
                $last = $rows->last();
                return [
                    'calculated_at' => $date,
                    'total_risk' => round($rows->avg('total_risk'), 1),
                    'risk_level' => $last->risk_level,
                ];
            })
            ->values();

        return response()->json($trend);
    }
}