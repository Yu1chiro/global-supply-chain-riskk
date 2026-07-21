<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas Article: article
class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'content', 'author', 'category',
        'image_url', 'tags', 'is_published'
    ];

    protected $casts = [
        'tags' => 'array',
        'is_published' => 'boolean',
    ];
}