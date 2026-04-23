<?php

namespace App\Console\Commands;

use App\Models\DeviceSetting;
use App\Models\SensorLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SimulatorAutoGenerateCommand extends Command
{
    private const MM_PER_TIP = 0.40;
    private const DEFAULT_NIGHT_TARGET = 120.0;
    private const DEFAULT_NOON_TARGET = 95.0;
    private const DEFAULT_NIGHT_RISE = 0.250;
    private const DEFAULT_DAY_DROP = 0.200;
    private const SIMULATOR_TIMEZONE = 'Asia/Makassar';
    private const STORAGE_TIMEZONE = 'UTC';

    protected $signature = 'simulator:auto-generate';

    protected $description = 'Generate data simulasi ketinggian air otomatis berbasis target waktu (malam/siang) dan tip hujan.';

    public function handle(): int
    {
        $setting = DeviceSetting::query()->first();

        if (!$setting || !$setting->sim_auto_enabled) {
            return self::SUCCESS;
        }

        $now = now()->timezone(self::SIMULATOR_TIMEZONE)->second(0);
        $intervalMinutes = max(1, (int) ($setting->sim_interval_minutes ?? 15));

        if ($setting->sim_last_generated_at instanceof Carbon) {
            $lastGenerated = $setting->sim_last_generated_at->copy()->timezone(self::SIMULATOR_TIMEZONE);

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
        $nightTargetCm = round((float) ($setting->sim_night_target_cm ?? self::DEFAULT_NIGHT_TARGET), 2);
        $noonTargetCm = round((float) ($setting->sim_noon_peak_target_cm ?? self::DEFAULT_NOON_TARGET), 2);
        $nightRiseCm = round((float) ($setting->sim_night_rise_cm ?? self::DEFAULT_NIGHT_RISE), 3);
        $dayDropCm = round((float) ($setting->sim_day_drop_cm ?? self::DEFAULT_DAY_DROP), 3);
        $currentWaterLevel = (float) ($latestLog?->water_level_cm ?? 0);
        $maxWaterLevel = max(0, $waterDepthCm);
        $dailyTipCount = $this->resolveDailyTipCount($now);
        $rainfallMm = round($dailyTipCount * self::MM_PER_TIP, 2);

        $recentTodayLog = SensorLog::query()
            ->where('device_code', 'alat_1')
            ->whereDate('recorded_at', $now->toDateString())
            ->latest('recorded_at')
            ->first();

        $previousTip = (int) ($recentTodayLog?->daily_tip_count ?? 0);
        $deltaTip = max(0, $dailyTipCount - $previousTip);
        $isRaining = $deltaTip > 0;

        $baseTarget = $this->resolveTimeBasedTarget($now, $nightTargetCm, $noonTargetCm);
        $rainBoostTarget = $this->resolveRainBoostCm($deltaTip * self::MM_PER_TIP);
        $effectiveTarget = min($maxWaterLevel, max(0, $baseTarget + $rainBoostTarget));

        $intervalFactor = max(0.5, $intervalMinutes / 15);
        $riseStep = max(0.05, $nightRiseCm * $intervalFactor);
        $dropStep = max(0.05, $dayDropCm * $intervalFactor);

        if ($effectiveTarget >= $currentWaterLevel) {
            $nextWaterLevel = min($effectiveTarget, $currentWaterLevel + $riseStep);
        } else {
            $nextWaterLevel = max($effectiveTarget, $currentWaterLevel - $dropStep);
        }

        $nextWaterLevel = round($nextWaterLevel, 2);

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
                'sim-auto|i=%d|base=%.1f|target=%.1f|wl=%.1f',
                $intervalMinutes,
                $baseTarget,
                $effectiveTarget,
                $nextWaterLevel
            ),
            'recorded_at' => $now->copy()->setTimezone(self::STORAGE_TIMEZONE),
        ]);

        $setting->update([
            'sim_last_generated_at' => $now->copy()->setTimezone(self::STORAGE_TIMEZONE),
        ]);

        return self::SUCCESS;
    }

    private function resolveDailyTipCount(Carbon $at): int
    {
        $start = $at->copy()->startOfDay()->setTimezone(self::STORAGE_TIMEZONE);
        $end = $at->copy()->endOfDay()->setTimezone(self::STORAGE_TIMEZONE);

        return (int) (SensorLog::query()
            ->where('device_code', 'alat_1')
            ->whereBetween('recorded_at', [$start, $end])
            ->latest('recorded_at')
            ->value('daily_tip_count') ?? 0);
    }

    private function resolveTimeBasedTarget(Carbon $now, float $nightTargetCm, float $noonTargetCm): float
    {
        $hour = (int) $now->format('G');
        $minute = (int) $now->format('i');
        $hourFraction = $hour + ($minute / 60);

        // Night window: 18:00-05:00 keeps level near night target.
        if ($hourFraction >= 18 || $hourFraction < 5) {
            return $nightTargetCm;
        }

        // Morning ramp: 05:00-12:00 transitions from night target to noon target.
        if ($hourFraction < 12) {
            $progress = ($hourFraction - 5) / 7;

            return $nightTargetCm + (($noonTargetCm - $nightTargetCm) * $progress);
        }

        // Afternoon ramp: 12:00-18:00 transitions back from noon target to night target.
        $progress = ($hourFraction - 12) / 6;

        return $noonTargetCm + (($nightTargetCm - $noonTargetCm) * $progress);
    }

    private function resolveRainBoostCm(float $rainfallIncreaseMm): float
    {
        if ($rainfallIncreaseMm <= 0) {
            return 0;
        }

        if ($rainfallIncreaseMm <= 2) {
            return $rainfallIncreaseMm * 0.10;
        }

        if ($rainfallIncreaseMm <= 10) {
            return (2 * 0.10) + (($rainfallIncreaseMm - 2) * 0.16);
        }

        return (2 * 0.10) + (8 * 0.16) + (($rainfallIncreaseMm - 10) * 0.22);
    }
}
