<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Port;

// Kelas Country: country
class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'capital', 'region', 'subregion', 'currency', 'currency_symbol',
        'languages', 'gdp', 'inflation', 'exports', 'imports', 'reserves', 'population', 'flag_url',
        'latitude', 'longitude', 'weather_data', 'exchange_rate_data', 'external_synced_at'
    ];

    protected $casts = [
        'weather_data' => 'array',
        'exchange_rate_data' => 'array',
        'languages' => 'array',
        'gdp' => 'decimal:2',
        'inflation' => 'decimal:2',
        'exports' => 'decimal:2',
        'imports' => 'decimal:2',
        'reserves' => 'decimal:2',
        'population' => 'decimal:0',
        'external_synced_at' => 'datetime',
    ];

    
    protected $appends = ['risk_level', 'flag_emoji'];

    // risk scores
    public function riskScores()
    {
        return $this->hasMany(RiskScore::class);
    }

    // latest risk score
    public function latestRiskScore()
    {
        return $this->hasOne(RiskScore::class)->latest('calculated_at');
    }

    // news cache
    public function newsCache()
    {
        return $this->hasMany(NewsCache::class);
    }

    // watchlists
    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    // currency rates
    public function currencyRates()
    {
        return $this->hasMany(CurrencyRate::class);
    }

    // comparisons as a
    public function comparisonsAsA()
    {
        return $this->hasMany(CountryComparison::class, 'country_a_id');
    }

    // comparisons as b
    public function comparisonsAsB()
    {
        return $this->hasMany(CountryComparison::class, 'country_b_id');
    }

    
    // ports
    public function ports()
    {
        return Port::where('country', $this->name)->get();
    }

    // get risk level attribute
    public function getRiskLevelAttribute()
    {
        $latest = $this->latestRiskScore;
        if (!$latest) return 'Unknown';

        $score = $latest->total_risk;
        if ($score < 30) return 'Low';
        if ($score < 60) return 'Medium';
        return 'High';
    }

    // get flag emoji attribute
    public function getFlagEmojiAttribute()
    {
        
        $code = strtoupper($this->code);
        if (strlen($code) !== 2) return '🏳️';

        $regionalOffset = 0x1F1E6;
        $asciiOffset = 65;
        $firstChar = ord($code[0]) - $asciiOffset + $regionalOffset;
        $secondChar = ord($code[1]) - $asciiOffset + $regionalOffset;

        return mb_convert_encoding(
            '&#x' . dechex($firstChar) . ';&#x' . dechex($secondChar) . ';',
            'UTF-8',
            'HTML-ENTITIES'
        );
    }
}