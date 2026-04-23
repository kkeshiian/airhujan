<?php

namespace App\Http\Controllers;

use App\Models\AudioRecord;
use App\Models\DeviceSetting;
use App\Models\SensorLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSimulatorController extends Controller
{
    private const MM_PER_TIP = 0.40;
    private const DEFAULT_AUTO_INTERVAL = 15;
    private const DEFAULT_AUTO_WATER_DEPTH = 120;
    private const DEFAULT_AUTO_ULTRASONIC_DISTANCE = 20;

    public function index(): View
    {
        $deviceSetting = DeviceSetting::query()->firstOrCreate([], [
            'deep_sleep_seconds' => 300,
            'sleep_minutes' => 5,
            'awake_minutes' => 1,
            'rain_tip_threshold' => 1,
            'rain_stop_timeout_ms' => 300000,
            'wifi_warmup_ms' => 2000,
            'mm_per_tip' => 0.3,
            'baseline_cm' => 120,
            'esp_mode' => 0,
            'force_rain' => false,
            'sim_auto_enabled' => false,
            'sim_interval_minutes' => self::DEFAULT_AUTO_INTERVAL,
            'sim_water_depth_cm' => self::DEFAULT_AUTO_WATER_DEPTH,
            'sim_ultrasonic_distance_cm' => self::DEFAULT_AUTO_ULTRASONIC_DISTANCE,
        ]);

        $latestSensorLog = SensorLog::query()
            ->where('device_code', 'alat_1')
            ->latest('recorded_at')
            ->first();

        $todayStart = now()->timezone(config('app.timezone'))->startOfDay();
        $todayEnd = now()->timezone(config('app.timezone'))->endOfDay();

        $currentDailyTip = (int) (SensorLog::query()
            ->where('device_code', 'alat_1')
            ->whereBetween('recorded_at', [$todayStart, $todayEnd])
            ->latest('recorded_at')
            ->value('daily_tip_count') ?? 0);

        return view('simulator.index', [
            'mmPerTip' => self::MM_PER_TIP,
            'currentDailyTip' => $currentDailyTip,
            'latestWaterLevel' => (float) ($latestSensorLog?->water_level_cm ?? 0),
            'defaultRecordedAt' => now()->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'defaultTipRecordedAt' => now()->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            'autoConfig' => [
                'enabled' => (bool) ($deviceSetting->sim_auto_enabled ?? false),
                'interval_minutes' => (int) ($deviceSetting->sim_interval_minutes ?? self::DEFAULT_AUTO_INTERVAL),
                'water_depth_cm' => (float) ($deviceSetting->sim_water_depth_cm ?? self::DEFAULT_AUTO_WATER_DEPTH),
                'ultrasonic_distance_cm' => (float) ($deviceSetting->sim_ultrasonic_distance_cm ?? self::DEFAULT_AUTO_ULTRASONIC_DISTANCE),
                'last_generated_at' => $deviceSetting->sim_last_generated_at?->copy()->timezone(config('app.timezone')),
            ],
        ]);
    }

    public function updateAutoGenerator(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'sim_auto_enabled' => ['required', Rule::in(['0', '1'])],
            'sim_interval_minutes' => ['required', 'integer', 'between:1,180'],
            'sim_water_depth_cm' => ['required', 'numeric', 'between:0,5000'],
            'sim_ultrasonic_distance_cm' => ['required', 'numeric', 'between:0,5000'],
        ]);

        $setting = DeviceSetting::query()->firstOrCreate([], [
            'deep_sleep_seconds' => 300,
        ]);

        $setting->update([
            'sim_auto_enabled' => ((int) $payload['sim_auto_enabled']) === 1,
            'sim_interval_minutes' => (int) $payload['sim_interval_minutes'],
            'sim_water_depth_cm' => round((float) $payload['sim_water_depth_cm'], 2),
            'sim_ultrasonic_distance_cm' => round((float) $payload['sim_ultrasonic_distance_cm'], 2),
            'updated_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Pengaturan auto-generator tinggi air berhasil disimpan.');
    }

    public function storeSensor(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'tip_delta' => ['required', 'integer', 'between:-20000,20000'],
            'rain_state' => ['required', Rule::in(['auto', 'rain', 'no_rain'])],
            'recorded_at' => ['required', 'date'],
            'device_code' => ['nullable', 'string', Rule::in(['alat_1'])],
        ]);

        $tipDelta = (int) $payload['tip_delta'];
        $recordedAt = Carbon::parse((string) $payload['recorded_at'], config('app.timezone'));

        $dayStart = $recordedAt->copy()->startOfDay();
        $dayEnd = $recordedAt->copy()->endOfDay();

        $existingDailyTip = (int) (SensorLog::query()
            ->where('device_code', 'alat_1')
            ->whereBetween('recorded_at', [$dayStart, $dayEnd])
            ->latest('recorded_at')
            ->value('daily_tip_count') ?? 0);

        $setting = DeviceSetting::query()->first();
        $depthCm = round((float) ($setting?->sim_water_depth_cm ?? 0), 2);
        $sensorDistanceCm = round((float) ($setting?->sim_ultrasonic_distance_cm ?? 0), 2);

        $tipCount = max(0, $existingDailyTip + $tipDelta);
        $rainfallMm = round($tipCount * self::MM_PER_TIP, 2);
        $waterLevelCm = round(max(0, $depthCm - $sensorDistanceCm), 2);

        $latestLog = SensorLog::query()
            ->where('device_code', 'alat_1')
            ->latest('recorded_at')
            ->first();

        $previousLevel = (float) ($latestLog?->water_level_cm ?? 0);
        $waterRiseCm = round($waterLevelCm - $previousLevel, 2);

        $isRaining = match ($payload['rain_state']) {
            'rain' => true,
            'no_rain' => false,
            default => $tipDelta > 0,
        };

        SensorLog::query()->create([
            'device_code' => 'alat_1',
            'rainfall_mm' => $rainfallMm,
            'water_level_cm' => $waterLevelCm,
            'water_rise_cm' => $waterRiseCm,
            'daily_tip_count' => $tipCount,
            'sim_tip_delta' => $tipDelta,
            'sim_depth_cm' => $depthCm,
            'sim_sensor_height_cm' => $sensorDistanceCm,
            'sim_rain_state' => (string) $payload['rain_state'],
            'is_raining' => $isRaining,
            'force_rain' => false,
            'mm_per_tip' => self::MM_PER_TIP,
            'baseline_cm' => $depthCm,
            'rain_status' => $isRaining ? 'Rain' : 'No Rain',
            'device_status' => sprintf(
                'simulated-by-admin | tip_delta=%+d | daily_tip=%d | depth=%.2fcm | ultrasonic_distance=%.2fcm',
                $tipDelta,
                $tipCount,
                $depthCm,
                $sensorDistanceCm
            ),
            'recorded_at' => $recordedAt,
        ]);

        return back()->with('status', 'Data simulasi sensor berhasil disimpan.');
    }

    public function storeAudio(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'audio_file' => ['required', 'file', 'max:20480', 'mimes:wav', 'mimetypes:audio/wav,audio/x-wav,audio/wave'],
            'title' => ['nullable', 'string', 'max:120'],
            'duration_seconds' => ['nullable', 'integer', 'between:1,3600'],
            'recorded_at' => ['required', 'date'],
            'device_code' => ['nullable', 'string', Rule::in(['alat_2'])],
        ]);

        $file = $request->file('audio_file');
        $recordedAt = Carbon::parse((string) $payload['recorded_at'], config('app.timezone'));

        $baseName = Str::of(pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME))
            ->replaceMatches('/[^A-Za-z0-9._-]/', '_')
            ->toString();

        if ($baseName === '') {
            $baseName = 'audio';
        }

        $storedName = $recordedAt->format('Ymd_His').'_sim_'.$baseName.'.wav';
        $relativePath = 'audio/uploads/'.$storedName;

        Storage::disk('public')->putFileAs('audio/uploads', $file, $storedName);

        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            $title = 'Simulasi hujan '.$recordedAt->format('Y-m-d H:i:s');
        }

        AudioRecord::query()->create([
            'title' => $title,
            'device_code' => 'alat_2',
            'file_path' => $relativePath,
            'duration_seconds' => $payload['duration_seconds'] ?? null,
            'device_status' => 'simulated-by-admin | audio upload manual',
            'recorded_at' => $recordedAt,
        ]);

        return back()->with('status', 'Audio WAV simulasi berhasil diupload.');
    }
}
