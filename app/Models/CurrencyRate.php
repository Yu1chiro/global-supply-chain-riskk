<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas CurrencyRate: currency rate
class CurrencyRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id', 'currency_code', 'rate_to_usd', 'rate_date',
    ];

    protected $casts = [
        'rate_to_usd' => 'float',
        'rate_date' => 'date',
    ];

    // country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
