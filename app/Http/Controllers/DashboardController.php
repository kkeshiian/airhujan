<?php

namespace App\Http\Controllers;

use App\Models\AudioRecord;
use App\Models\DeviceLocation;
use App\Models\SensorLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
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
        ]);
    }

    public function chartData(): JsonResponse
    {
        return response()->json($this->buildChartSeries());
    }

    public function live(): JsonResponse
    {
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
            'latest_rain_status' => $latestAlat1?->rain_status ?? 'No Rain',
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
            'chart' => $this->buildChartSeries(),
        ]);
    }

    private function buildChartSeries(): array
    {
        $sensorPoints = SensorLog::query()
            ->where('device_code', 'alat_1')
            ->latest('recorded_at')
            ->limit(24)
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

        $sensorPoints = $sensorPoints->sortBy('recorded_at')->values();

        $rangeStart = $sensorPoints->first()->recorded_at?->copy()->startOfMinute() ?? now()->subHours(1);
        $rangeEnd = $sensorPoints->last()->recorded_at?->copy()->endOfMinute() ?? now();

        $audioChartData = AudioRecord::query()
            ->whereBetween('recorded_at', [$rangeStart, $rangeEnd])
            ->orderBy('recorded_at')
            ->get();

        $audioByMinute = $audioChartData->groupBy(
            fn ($row) => $row->recorded_at->copy()->startOfMinute()->format('Y-m-d H:i:s')
        );

        $chartLabels = $sensorPoints->map(fn ($row) => $row->recorded_at->format('H:i:s'))->values();
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
