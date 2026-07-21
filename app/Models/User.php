<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Kelas User: user
class User extends Authenticatable
{
    
    use HasFactory, Notifiable;

    
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'is_admin',
    ];

    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    
    // casts
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    // watchlists
    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    // in app notifications
    public function inAppNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    // activity logs
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
