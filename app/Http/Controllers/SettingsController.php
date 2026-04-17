<?php

namespace App\Http\Controllers;

use App\Models\AudioRecord;
use App\Models\SensorLog;
use App\Models\DeviceSetting;
use App\Models\DeviceLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $setting = DeviceSetting::firstOrCreate([], [
            'deep_sleep_seconds' => 300,
        ]);

        $locations = DeviceLocation::all();

        return view('settings.index', [
            'setting' => $setting,
            'locations' => $locations,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'deep_sleep_seconds' => ['required', 'integer', 'between:30,86400'],
        ]);

        $setting = DeviceSetting::firstOrCreate([], [
            'deep_sleep_seconds' => 300,
        ]);

        $setting->update([
            'deep_sleep_seconds' => $payload['deep_sleep_seconds'],
            'last_published_at' => now(),
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Pengaturan deep sleep diperbarui dan dikirim ke perangkat (simulasi MQTT).');
    }

    public function updateLocations(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'locations' => ['required', 'array', 'min:1'],
            'locations.*.coordinates' => ['required', 'string', 'max:80'],
        ]);

        foreach ($payload['locations'] as $deviceCode => $locationPayload) {
            [$latitude, $longitude] = $this->parseCoordinates((string) $locationPayload['coordinates']);

            DeviceLocation::updateOrCreate(
                ['device_code' => $deviceCode],
                [
                    'device_name' => $this->resolveDeviceName((string) $deviceCode),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'updated_by' => $request->user()->id,
                ]
            );
        }

        return back()->with('status', 'Semua lokasi alat berhasil diperbarui.');
    }

    public function purgeData(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'target' => ['required', Rule::in(['logs', 'audio'])],
            'range' => ['required', Rule::in(['1d', '1w', '1m', 'all'])],
        ]);

        $target = (string) $payload['target'];
        $range = (string) $payload['range'];

        $query = $target === 'logs'
            ? SensorLog::query()->where('device_code', 'alat_1')
            : AudioRecord::query();

        if ($range !== 'all') {
            $threshold = match ($range) {
                '1d' => now()->subDay(),
                '1w' => now()->subWeek(),
                '1m' => now()->subMonth(),
                default => now(),
            };

            $query->where('recorded_at', '>=', $threshold);
        }

        $deleted = $query->delete();

        $targetLabel = $target === 'logs' ? 'log data' : 'audio';
        $rangeLabel = match ($range) {
            '1d' => '1 hari terakhir',
            '1w' => '1 minggu terakhir',
            '1m' => '1 bulan terakhir',
            default => 'semua data',
        };

        return back()->with('status', "Berhasil menghapus {$deleted} data {$targetLabel} ({$rangeLabel}).");
    }

    private function parseCoordinates(string $coordinates): array
    {
        $input = trim($coordinates);

        if ($input === '') {
            throw ValidationException::withMessages([
                'locations' => 'Koordinat tidak boleh kosong.',
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
                'locations' => 'Format koordinat tidak valid. Gunakan lat,long. Contoh: -6.2100000,106.8266660',
            ]);
        }

        $latitude = (float) $parts[0];
        $longitude = (float) $parts[1];

        if ($latitude < -90 || $latitude > 90) {
            throw ValidationException::withMessages([
                'locations' => 'Latitude harus di antara -90 sampai 90.',
            ]);
        }

        if ($longitude < -180 || $longitude > 180) {
            throw ValidationException::withMessages([
                'locations' => 'Longitude harus di antara -180 sampai 180.',
            ]);
        }

        return [$latitude, $longitude];
    }

    private function resolveDeviceName(string $deviceCode): string
    {
        return match (strtolower($deviceCode)) {
            'alat_1' => 'Alat Monitoring Hujan',
            'alat_2' => 'Alat Perekam Suara',
            default => strtoupper($deviceCode),
        };
    }
}
