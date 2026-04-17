<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_code',
        'rainfall_mm',
        'water_level_cm',
        'rain_status',
        'battery_percent',
        'solar_power_watts',
        'device_status',
        'recorded_at',
    ];

    protected $casts = [
        'rainfall_mm' => 'float',
        'water_level_cm' => 'float',
        'battery_percent' => 'integer',
        'solar_power_watts' => 'integer',
        'recorded_at' => 'datetime',
    ];
}
