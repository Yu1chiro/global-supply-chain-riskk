<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas CountryComparison: country comparison
class CountryComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'country_a_id', 'country_b_id', 'comparison_result',
    ];

    protected $casts = [
        'comparison_result' => 'array',
    ];

    // user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // country a
    public function countryA()
    {
        return $this->belongsTo(Country::class, 'country_a_id');
    }

    // country b
    public function countryB()
    {
        return $this->belongsTo(Country::class, 'country_b_id');
    }
}
