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
        'water_rise_cm',
        'daily_tip_count',
        'sim_tip_delta',
        'sim_depth_cm',
        'sim_sensor_height_cm',
        'sim_manual_water_level_cm',
        'sim_rain_state',
        'is_raining',
        'force_rain',
        'esp_mode',
        'esp_mode_name',
        'sleep_minutes',
        'awake_minutes',
        'rain_tip_threshold',
        'rain_stop_timeout_ms',
        'wifi_warmup_ms',
        'mm_per_tip',
        'baseline_cm',
        'day_key',
        'time_synced',
        'rain_status',
        'battery_percent',
        'solar_power_watts',
        'device_status',
        'recorded_at',
    ];

    protected $casts = [
        'rainfall_mm' => 'float',
        'water_level_cm' => 'float',
        'water_rise_cm' => 'float',
        'daily_tip_count' => 'integer',
        'sim_tip_delta' => 'integer',
        'sim_depth_cm' => 'float',
        'sim_sensor_height_cm' => 'float',
        'sim_manual_water_level_cm' => 'float',
        'is_raining' => 'boolean',
        'force_rain' => 'boolean',
        'esp_mode' => 'integer',
        'sleep_minutes' => 'integer',
        'awake_minutes' => 'integer',
        'rain_tip_threshold' => 'integer',
        'rain_stop_timeout_ms' => 'integer',
        'wifi_warmup_ms' => 'integer',
        'mm_per_tip' => 'float',
        'baseline_cm' => 'float',
        'day_key' => 'integer',
        'time_synced' => 'boolean',
        'battery_percent' => 'integer',
        'solar_power_watts' => 'integer',
        'recorded_at' => 'datetime',
    ];
}
