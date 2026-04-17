<?php

namespace App\Http\Controllers;

use App\Models\SensorLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataExportController extends Controller
{
    public function sensorCsv(Request $request): StreamedResponse
    {
        $query = SensorLog::query()->latest('recorded_at');

        if ($request->filled('device_code')) {
            $query->where('device_code', $request->string('device_code'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('recorded_at', '>=', $request->string('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('recorded_at', '<=', $request->string('end_date'));
        }

        $fileName = 'sensor-logs-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, ['Timestamp', 'Device', 'Rainfall (mm)', 'Water Level (cm)', 'Rain Status']);

            $query->chunk(200, function ($rows) use ($handle): void {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->recorded_at,
                        $row->device_code,
                        $row->rainfall_mm,
                        $row->water_level_cm,
                        $row->rain_status,
                    ]);
                }
            });

            fclose($handle);
        }, $fileName);
    }
}
