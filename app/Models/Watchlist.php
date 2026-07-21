<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas Watchlist: watchlist
class Watchlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'country_id', 'is_active', 'preferences'
    ];

    protected $casts = [
        'preferences' => 'array',
        'is_active' => 'boolean',
    ];

    // user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}