<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorLog extends Model
{
    protected $table = 'sensor_logs';

    protected $fillable = [
        'gas_value',
        'flame_detected',
    ];

    protected $casts = [
        'flame_detected' => 'boolean',
        'gas_value' => 'integer',
    ];
}
