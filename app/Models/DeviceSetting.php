<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'deep_sleep_seconds',
        'sleep_minutes',
        'awake_minutes',
        'rain_tip_threshold',
        'rain_stop_timeout_ms',
        'wifi_warmup_ms',
        'mm_per_tip',
        'baseline_cm',
        'esp_mode',
        'force_rain',
        'sim_auto_enabled',
        'sim_interval_minutes',
        'sim_water_depth_cm',
        'sim_ultrasonic_distance_cm',
        'sim_night_rise_cm',
        'sim_day_drop_cm',
        'sim_rain_boost_cm',
        'sim_last_generated_at',
        'last_published_at',
        'updated_by',
    ];

    protected $casts = [
        'sleep_minutes' => 'integer',
        'awake_minutes' => 'integer',
        'rain_tip_threshold' => 'integer',
        'rain_stop_timeout_ms' => 'integer',
        'wifi_warmup_ms' => 'integer',
        'mm_per_tip' => 'float',
        'baseline_cm' => 'float',
        'esp_mode' => 'integer',
        'force_rain' => 'boolean',
        'sim_auto_enabled' => 'boolean',
        'sim_interval_minutes' => 'integer',
        'sim_water_depth_cm' => 'float',
        'sim_ultrasonic_distance_cm' => 'float',
        'sim_night_rise_cm' => 'float',
        'sim_day_drop_cm' => 'float',
        'sim_rain_boost_cm' => 'float',
        'sim_last_generated_at' => 'datetime',
        'last_published_at' => 'datetime',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
