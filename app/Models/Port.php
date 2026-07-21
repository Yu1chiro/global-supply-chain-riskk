<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas Port: port
class Port extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'country', 'code', 'latitude', 'longitude',
        'type', 'status', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // traffic logs
    public function trafficLogs()
    {
        return $this->hasMany(PortTrafficLog::class);
    }

    // latest traffic log
    public function latestTrafficLog()
    {
        return $this->hasOne(PortTrafficLog::class)->latest('recorded_at');
    }
}
