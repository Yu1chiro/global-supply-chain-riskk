<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas ApiSyncLog: api sync log
class ApiSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_name', 'endpoint', 'status', 'response_time_ms',
        'error_message', 'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    
    // record
    public static function record(string $apiName, ?string $endpoint, bool $success, float $startTime, ?string $errorMessage = null): self
    {
        return static::create([
            'api_name' => $apiName,
            'endpoint' => $endpoint,
            'status' => $success ? 'success' : 'failed',
            'response_time_ms' => (int) round((microtime(true) - $startTime) * 1000),
            'error_message' => $errorMessage,
            'synced_at' => now(),
        ]);
    }
}
