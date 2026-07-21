<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas Notification: notification
class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'country_id', 'type', 'title', 'message', 'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
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

    // scope unread
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
