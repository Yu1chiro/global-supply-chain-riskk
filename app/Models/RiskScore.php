<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas RiskScore: risk score
class RiskScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id', 'weather_risk', 'inflation_risk', 'currency_risk',
        'political_risk', 'news_sentiment_risk', 'total_risk',
        'risk_level', 'raw_data', 'calculated_at'
    ];

    protected $casts = [
        'raw_data' => 'array',
        'calculated_at' => 'datetime',
    ];

    // country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // get risk level attribute
    public function getRiskLevelAttribute($value)
    {
        if ($value) return $value;

        $total = $this->total_risk;
        if ($total < 30) return 'Low';
        if ($total < 60) return 'Medium';
        return 'High';
    }
}