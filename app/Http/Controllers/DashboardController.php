<?php

namespace App\Http\Controllers;

use App\Models\AudioRecord;
use App\Models\DeviceLocation;
use App\Models\DeviceSetting;
use App\Models\SensorLog;
use App\Services\MqttPublisher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function index(): View
    {
        $setting = DeviceSetting::query()->firstOrCreate([], [
            'deep_sleep_seconds' => 300,
            'sleep_minutes' => 5,
            'awake_minutes' => 1,
            'rain_tip_threshold' => 1,
            'rain_stop_timeout_ms' => 300000,
            'wifi_warmup_ms' => 2000,
            'mm_per_tip' => 0.3,
            'baseline_cm' => 0,
            'esp_mode' => 0,
            'force_rain' => false,
        ]);

        $locations = DeviceLocation::query()->orderBy('device_code')->get();
        $latestRainStatus = SensorLog::query()
            ->where('device_code', 'alat_1')
            ->latest('recorded_at')
            ->first();

        $latestAlat1 = SensorLog::query()
            ->where('device_code', 'alat_1')
            ->latest('recorded_at')
            ->first();

        $latestAlat2 = SensorLog::query()
            ->where('device_code', 'alat_2')
            ->latest('recorded_at')
            ->first();

        $recentLogs = SensorLog::query()
            ->latest('recorded_at')
            ->limit(8)
            ->get();

        $latestAudioRecord = AudioRecord::query()
            ->latest('recorded_at')
            ->first();

        $chartSeries = $this->buildChartSeries();

        // Latest power info
        $latestBattery = $latestAlat1?->battery_percent ?? 0;
        $latestSolar = $latestAlat1?->solar_power_watts ?? 0;

        $alat1RuntimeStatus = $this->resolveRuntimeStatus(
            $latestAlat1?->device_status,
            'ALAT 1'
        );
        $alat2RuntimeStatus = $this->resolveRuntimeStatus(
            $latestAlat2?->device_status ?? $latestAudioRecord?->device_status,
            'ALAT 2'
        );

        return view('dashboard.index', [
            'locations' => $locations,
            'latestRainStatus' => $latestRainStatus?->rain_status ?? 'No Rain',
            'latestAlat1' => $latestAlat1,
            'latestAlat2' => $latestAlat2,
            'deviceSetting' => $setting,
            'alat1RuntimeStatus' => $alat1RuntimeStatus,
            'alat2RuntimeStatus' => $alat2RuntimeStatus,
            'recentLogs' => $recentLogs,
            'chartLabels' => $chartSeries['chartLabels'],
            'rainfallData' => $chartSeries['rainfallData'],
            'waterLevelData' => $chartSeries['waterLevelData'],
            'batteryData' => $chartSeries['batteryData'],
            'solarData' => $chartSeries['solarData'],
            'audioFrequencyData' => $chartSeries['audioFrequencyData'],
            'latestBattery' => $latestBattery,
            'latestSolar' => $latestSolar,
            'mqtt' => [
                'ws_host' => config('mqtt.ws_host'),
                'ws_port' => (int) config('mqtt.ws_port'),
                'ws_path' => config('mqtt.ws_path'),
                'ws_protocol' => config('mqtt.ws_protocol', 'wss'),
                'data_topic' => config('mqtt.sensor_topic'),
                'status_topic' => config('mqtt.status_topic'),
                'cmd_topic' => config('mqtt.cmd_topic'),
                'config_topic' => config('mqtt.config_topic'),
            ],
        ]);
    }

    public function chartData(): JsonResponse
    {
        return response()->json($this->buildChartSeries());
    }

    public function live(): JsonResponse
    {
        $setting = DeviceSetting::query()->firstOrCreate([], [
            'deep_sleep_seconds' => 300,
            'sleep_minutes' => 5,
            'awake_minutes' => 1,
            'rain_tip_threshold' => 1,
            'rain_stop_timeout_ms' => 300000,
            'wifi_warmup_ms' => 2000,
            'mm_per_tip' => 0.3,
            'baseline_cm' => 0,
            'esp_mode' => 0,
            'force_rain' => false,
        ]);

        $latestAlat1 = SensorLog::query()
            ->where('device_code', 'alat_1')
            ->latest('recorded_at')
            ->first();

        $latestAlat2 = SensorLog::query()
            ->where('device_code', 'alat_2')
            ->latest('recorded_at')
            ->first();

        $latestAudioRecord = AudioRecord::query()
            ->latest('recorded_at')
            ->first();

        return response()->json([
            'latest_rainfall_mm' => $latestAlat1?->rainfall_mm,
            'latest_water_level_cm' => $latestAlat1?->water_level_cm,
            'latest_water_rise_cm' => $latestAlat1?->water_rise_cm,
            'latest_daily_tip_count' => $latestAlat1?->daily_tip_count,
            'latest_rain_status' => $latestAlat1?->rain_status ?? 'No Rain',
            'latest_is_raining' => $latestAlat1?->is_raining,
            'latest_force_rain' => $latestAlat1?->force_rain,
            'latest_esp_mode' => $latestAlat1?->esp_mode,
            'latest_esp_mode_name' => $latestAlat1?->esp_mode_name,
            'latest_sleep_minutes' => $latestAlat1?->sleep_minutes,
            'latest_awake_minutes' => $latestAlat1?->awake_minutes,
            'latest_rain_tip_threshold' => $latestAlat1?->rain_tip_threshold,
            'latest_rain_stop_timeout_ms' => $latestAlat1?->rain_stop_timeout_ms,
            'latest_wifi_warmup_ms' => $latestAlat1?->wifi_warmup_ms,
            'latest_mm_per_tip' => $latestAlat1?->mm_per_tip,
            'latest_baseline_cm' => $latestAlat1?->baseline_cm,
            'latest_day_key' => $latestAlat1?->day_key,
            'latest_time_synced' => $latestAlat1?->time_synced,
            'latest_battery_percent' => $latestAlat1?->battery_percent ?? 0,
            'latest_solar_power_watts' => $latestAlat1?->solar_power_watts ?? 0,
            'alat1_runtime_status' => $this->resolveRuntimeStatus(
                $latestAlat1?->device_status,
                'ALAT 1'
            ),
            'alat2_runtime_status' => $this->resolveRuntimeStatus(
                $latestAlat2?->device_status ?? $latestAudioRecord?->device_status,
                'ALAT 2'
            ),
            'alat2_status_text' => $latestAudioRecord?->title ? 'Rekaman terbaru: '.$latestAudioRecord->title : 'Siap Merekam',
            'device_config' => [
                'sleep_minutes' => $setting->sleep_minutes,
                'awake_minutes' => $setting->awake_minutes,
                'rain_tip_threshold' => $setting->rain_tip_threshold,
                'rain_stop_timeout_ms' => $setting->rain_stop_timeout_ms,
                'wifi_warmup_ms' => $setting->wifi_warmup_ms,
                'mm_per_tip' => $setting->mm_per_tip,
                'baseline_cm' => $setting->baseline_cm,
                'esp_mode' => $setting->esp_mode,
                'force_rain' => $setting->force_rain,
            ],
            'chart' => $this->buildChartSeries(),
        ]);
    }

    public function sendCommand(Request $request, MqttPublisher $publisher): JsonResponse
    {
        $payload = $request->validate([
            'command' => ['required', 'string', 'in:RESET_HUJAN,FORCE_RAIN_ON,FORCE_RAIN_OFF,SYNC_TIME'],
        ]);

        try {
            $publisher->publish((string) config('mqtt.cmd_topic'), [
                'command' => $payload['command'],
                'sent_at' => now()->toIso8601String(),
                'from' => 'web-dashboard',
            ], (int) config('mqtt.qos', 0), false);

            return response()->json([
                'message' => 'Command berhasil dikirim ke perangkat.',
                'command' => $payload['command'],
            ]);
        } catch (Throwable $e) {
            Log::error('Publish MQTT command gagal', [
                'command' => $payload['command'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal mengirim command ke perangkat.',
            ], 500);
        }
    }

    public function updateDeviceConfig(Request $request, MqttPublisher $publisher): JsonResponse
    {
        $payload = $request->validate([
            'sleep_minutes' => ['required', 'integer', 'between:1,1440'],
            'awake_minutes' => ['required', 'integer', 'between:1,240'],
            'rain_tip_threshold' => ['required', 'integer', 'between:1,200'],
            'rain_stop_timeout_ms' => ['required', 'integer', 'between:1000,1800000'],
            'wifi_warmup_ms' => ['required', 'integer', 'min:100'],
            'mm_per_tip' => ['required', 'numeric', 'between:0.01,20'],
            'baseline_cm' => ['required', 'numeric', 'between:0,1000'],
            'esp_mode' => ['required', 'integer', 'in:0,1'],
            'force_rain' => ['required', 'boolean'],
        ]);

        $setting = DeviceSetting::query()->firstOrCreate([], [
            'deep_sleep_seconds' => 300,
        ]);

        $savePayload = [
            'deep_sleep_seconds' => ((int) $payload['sleep_minutes']) * 60,
            'sleep_minutes' => (int) $payload['sleep_minutes'],
            'awake_minutes' => (int) $payload['awake_minutes'],
            'rain_tip_threshold' => (int) $payload['rain_tip_threshold'],
            'rain_stop_timeout_ms' => (int) $payload['rain_stop_timeout_ms'],
            'wifi_warmup_ms' => (int) $payload['wifi_warmup_ms'],
            'mm_per_tip' => (float) $payload['mm_per_tip'],
            'baseline_cm' => (float) $payload['baseline_cm'],
            'esp_mode' => (int) $payload['esp_mode'],
            'force_rain' => (bool) $payload['force_rain'],
            'last_published_at' => now(),
            'updated_by' => $request->user()->id,
        ];

        $setting->update($savePayload);

        try {
            $configPayload = [
                'sleep_minutes' => (int) $payload['sleep_minutes'],
                'deep_sleep_seconds' => ((int) $payload['sleep_minutes']) * 60,
                'awake_minutes' => (int) $payload['awake_minutes'],
                'rain_tip_threshold' => (int) $payload['rain_tip_threshold'],
                'rain_stop_timeout_ms' => (int) $payload['rain_stop_timeout_ms'],
                'wifi_warmup_ms' => (int) $payload['wifi_warmup_ms'],
                'mm_per_tip' => (float) $payload['mm_per_tip'],
                'baseline_cm' => (float) $payload['baseline_cm'],
                'esp_mode' => (int) $payload['esp_mode'],
                'force_rain' => (bool) $payload['force_rain'],
                'sent_at' => now()->toIso8601String(),
                'from' => 'web-dashboard',
            ];

            $publisher->publish((string) config('mqtt.config_topic'), [
                ...$configPayload,
            ], (int) config('mqtt.config_qos', 1), (bool) config('mqtt.config_retain', true));

            return response()->json([
                'message' => 'Konfigurasi berhasil disimpan dan dipublish ke perangkat.',
                'config' => [
                    'sleep_minutes' => $setting->sleep_minutes,
                    'awake_minutes' => $setting->awake_minutes,
                    'rain_tip_threshold' => $setting->rain_tip_threshold,
                    'rain_stop_timeout_ms' => $setting->rain_stop_timeout_ms,
                    'wifi_warmup_ms' => $setting->wifi_warmup_ms,
                    'mm_per_tip' => $setting->mm_per_tip,
                    'baseline_cm' => $setting->baseline_cm,
                    'esp_mode' => $setting->esp_mode,
                    'force_rain' => $setting->force_rain,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Publish MQTT config gagal', [
                'config' => $payload,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Konfigurasi tersimpan, tetapi publish MQTT gagal.',
            ], 500);
        }
    }

    private function buildChartSeries(): array
    {
        $sensorPoints = SensorLog::query()
            ->where('device_code', 'alat_1')
            ->latest('recorded_at')
            ->limit(240)
            ->get();

        if ($sensorPoints->isEmpty()) {
            return [
                'chartLabels' => [],
                'rainfallData' => [],
                'waterLevelData' => [],
                'batteryData' => [],
                'solarData' => [],
                'audioFrequencyData' => [],
            ];
        }

        $minRainDelta = 0.05;
        $minWaterDelta = 0.50;

        $sensorPoints = $sensorPoints
            ->sortBy('recorded_at')
            ->values()
            ->reduce(function ($carry, $row) use ($minRainDelta, $minWaterDelta) {
                $previous = $carry->last();

                if ($previous === null) {
                    $carry->push($row);

                    return $carry;
                }

                $currentRainfall = round((float) ($row->rainfall_mm ?? 0), 2);
                $currentWaterLevel = round((float) ($row->water_level_cm ?? 0), 2);
                $previousRainfall = round((float) ($previous->rainfall_mm ?? 0), 2);
                $previousWaterLevel = round((float) ($previous->water_level_cm ?? 0), 2);

                $rainDelta = abs($currentRainfall - $previousRainfall);
                $waterDelta = abs($currentWaterLevel - $previousWaterLevel);

                if ($rainDelta >= $minRainDelta || $waterDelta >= $minWaterDelta) {
                    $carry->push($row);
                }

                return $carry;
            }, collect())
            ->values();

        if ($sensorPoints->isEmpty()) {
            return [
                'chartLabels' => [],
                'rainfallData' => [],
                'waterLevelData' => [],
                'batteryData' => [],
                'solarData' => [],
                'audioFrequencyData' => [],
            ];
        }

        $sensorPoints = $sensorPoints->take(-24)->values();

        $rangeStart = $sensorPoints->first()->recorded_at?->copy()->startOfMinute() ?? now()->subHours(1);
        $rangeEnd = $sensorPoints->last()->recorded_at?->copy()->endOfMinute() ?? now();

        $audioChartData = AudioRecord::query()
            ->whereBetween('recorded_at', [$rangeStart, $rangeEnd])
            ->orderBy('recorded_at')
            ->get();

        $audioByMinute = $audioChartData->groupBy(
            fn ($row) => $row->recorded_at->copy()->startOfMinute()->format('Y-m-d H:i:s')
        );

        $chartTimezone = 'Asia/Makassar';

        $chartLabels = $sensorPoints->map(
            fn ($row) => $row->recorded_at->copy()->timezone($chartTimezone)->format('H:i:s')
        )->values();
        $rainfallData = $sensorPoints->map(fn ($row) => round((float) ($row->rainfall_mm ?? 0), 2))->values();
        $waterLevelData = $sensorPoints->map(fn ($row) => round((float) ($row->water_level_cm ?? 0), 2))->values();
        $batteryData = $sensorPoints->map(fn ($row) => round((float) ($row->battery_percent ?? 0), 2))->values();
        $solarData = $sensorPoints->map(fn ($row) => round((float) ($row->solar_power_watts ?? 0), 2))->values();
        $audioFrequencyData = $sensorPoints->map(function ($row) use ($audioByMinute) {
            $minuteKey = $row->recorded_at->copy()->startOfMinute()->format('Y-m-d H:i:s');

            return $audioByMinute->get($minuteKey, collect())->count();
        })->values();

        return [
            'chartLabels' => $chartLabels->all(),
            'rainfallData' => $rainfallData->all(),
            'waterLevelData' => $waterLevelData->all(),
            'batteryData' => $batteryData->all(),
            'solarData' => $solarData->all(),
            'audioFrequencyData' => $audioFrequencyData->all(),
        ];
    }

    private function resolveRuntimeStatus(?string $rawStatus, string $deviceLabel): string
    {
        $status = trim((string) $rawStatus);

        if ($status !== '') {
            return $status;
        }

        return $deviceLabel . ': menunggu status dari MQTT';
    }

    public function updateLocation(Request $request, string $deviceCode): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $payload = $request->validate([
            'device_name' => ['required', 'string', 'max:100'],
            'coordinates' => ['nullable', 'string', 'max:80'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if (!empty($payload['coordinates'])) {
            [$latitude, $longitude] = $this->parseCoordinates((string) $payload['coordinates']);
        } elseif (isset($payload['latitude'], $payload['longitude'])) {
            $latitude = (float) $payload['latitude'];
            $longitude = (float) $payload['longitude'];
        } else {
            throw ValidationException::withMessages([
                'coordinates' => 'Koordinat wajib diisi dengan format lat,long. Contoh: -6.2100000,106.8266660',
            ]);
        }

        DeviceLocation::updateOrCreate(
            ['device_code' => $deviceCode],
            [
                'device_name' => $payload['device_name'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'updated_by' => $request->user()->id,
            ]
        );

        return back()->with('status', 'Lokasi alat berhasil diperbarui.');
    }

    private function parseCoordinates(string $coordinates): array
    {
        $input = trim($coordinates);

        if ($input === '') {
            throw ValidationException::withMessages([
                'coordinates' => 'Koordinat tidak boleh kosong.',
            ]);
        }

        if (str_contains($input, ';')) {
            $parts = array_map('trim', explode(';', $input));
            $parts = array_map(fn (string $value) => str_replace(',', '.', $value), $parts);
        } elseif (str_contains($input, ',')) {
            $parts = array_map('trim', explode(',', $input));
        } else {
            $parts = preg_split('/\s+/', $input) ?: [];
        }

        if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
            throw ValidationException::withMessages([
                'coordinates' => 'Format koordinat tidak valid. Gunakan lat,long. Contoh: -6.2100000,106.8266660',
            ]);
        }

        $latitude = (float) $parts[0];
        $longitude = (float) $parts[1];

        if ($latitude < -90 || $latitude > 90) {
            throw ValidationException::withMessages([
                'coordinates' => 'Latitude harus di antara -90 sampai 90.',
            ]);
        }

        if ($longitude < -180 || $longitude > 180) {
            throw ValidationException::withMessages([
                'coordinates' => 'Longitude harus di antara -180 sampai 180.',
            ]);
        }

        return [$latitude, $longitude];
    }
}
