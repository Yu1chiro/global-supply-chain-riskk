<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas PositiveWord: positive word
class PositiveWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'word', 'weight'
    ];

    protected $casts = [
        'weight' => 'integer',
    ];
}
