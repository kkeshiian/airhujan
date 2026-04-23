<?php

namespace App\Console\Commands;

use App\Models\DeviceSetting;
use App\Models\SensorLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SimulatorAutoGenerateCommand extends Command
{
    private const MM_PER_TIP = 0.40;

    protected $signature = 'simulator:auto-generate';

    protected $description = 'Generate data simulasi ketinggian air otomatis dari input kedalaman air dan jarak permukaan ke sensor.';

    public function handle(): int
    {
        $setting = DeviceSetting::query()->first();

        if (!$setting || !$setting->sim_auto_enabled) {
            return self::SUCCESS;
        }

        $timezone = config('app.timezone', 'UTC');
        $now = now()->timezone($timezone)->second(0);
        $intervalMinutes = max(1, (int) ($setting->sim_interval_minutes ?? 15));

        if ($setting->sim_last_generated_at instanceof Carbon) {
            $lastGenerated = $setting->sim_last_generated_at->copy()->timezone($timezone);

            if ($now->diffInMinutes($lastGenerated) < $intervalMinutes) {
                return self::SUCCESS;
            }
        }

        $latestLog = SensorLog::query()
            ->where('device_code', 'alat_1')
            ->latest('recorded_at')
            ->first();

        $waterDepthCm = round((float) ($setting->sim_water_depth_cm ?? 0), 2);
        $ultrasonicDistanceCm = round((float) ($setting->sim_ultrasonic_distance_cm ?? 0), 2);
        $currentWaterLevel = (float) ($latestLog?->water_level_cm ?? 0);
        $nextWaterLevel = round(max(0, $waterDepthCm - $ultrasonicDistanceCm), 2);
        $dailyTipCount = $this->resolveDailyTipCount($now);
        $rainfallMm = round($dailyTipCount * self::MM_PER_TIP, 2);

        $recentTodayLog = SensorLog::query()
            ->where('device_code', 'alat_1')
            ->whereDate('recorded_at', $now->toDateString())
            ->latest('recorded_at')
            ->first();

        $previousTip = (int) ($recentTodayLog?->daily_tip_count ?? 0);
        $isRaining = $dailyTipCount > $previousTip;

        SensorLog::query()->create([
            'device_code' => 'alat_1',
            'rainfall_mm' => $rainfallMm,
            'water_level_cm' => $nextWaterLevel,
            'water_rise_cm' => round($nextWaterLevel - $currentWaterLevel, 2),
            'daily_tip_count' => $dailyTipCount,
            'sim_depth_cm' => $waterDepthCm,
            'sim_sensor_height_cm' => $ultrasonicDistanceCm,
            'sim_rain_state' => 'auto',
            'is_raining' => $isRaining,
            'force_rain' => false,
            'mm_per_tip' => self::MM_PER_TIP,
            'baseline_cm' => $waterDepthCm,
            'rain_status' => $isRaining ? 'Rain' : 'No Rain',
            'device_status' => sprintf(
                'sim-auto-water | interval=%dmin | depth=%.2fcm | ultrasonic_distance=%.2fcm',
                $intervalMinutes,
                $waterDepthCm,
                $ultrasonicDistanceCm
            ),
            'recorded_at' => $now,
        ]);

        $setting->update([
            'sim_last_generated_at' => $now,
        ]);

        return self::SUCCESS;
    }

    private function resolveDailyTipCount(Carbon $at): int
    {
        $start = $at->copy()->startOfDay();
        $end = $at->copy()->endOfDay();

        return (int) (SensorLog::query()
            ->where('device_code', 'alat_1')
            ->whereBetween('recorded_at', [$start, $end])
            ->latest('recorded_at')
            ->value('daily_tip_count') ?? 0);
    }
}
