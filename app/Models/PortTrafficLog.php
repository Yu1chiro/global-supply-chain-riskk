<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Kelas PortTrafficLog: port traffic log
class PortTrafficLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'port_id', 'congestion_level', 'vessels_waiting',
        'average_delay_hours', 'notes', 'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    // port
    public function port()
    {
        return $this->belongsTo(Port::class);
    }
}
