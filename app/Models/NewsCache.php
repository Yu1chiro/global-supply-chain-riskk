<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas NewsCache: news cache
class NewsCache extends Model
{
    use HasFactory;

    protected $table = 'news_cache';

    protected $fillable = [
        'title', 'description', 'content', 'url', 'source',
        'image_url', 'category', 'country_id', 'sentiment_data',
        'sentiment_result', 'published_at'
    ];

    protected $casts = [
        'sentiment_data' => 'array',
        'published_at' => 'datetime',
    ];

    // country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
