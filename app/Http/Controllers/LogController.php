<?php

namespace App\Http\Controllers;

use App\Models\SensorLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $request->string('sort')->toString();
        if (!in_array($sort, ['latest', 'oldest'], true)) {
            $sort = 'latest';
        }

        $query = $this->buildFilteredQuery($request, $sort);

        $logs = $query->paginate(20)->withQueryString();

        return view('logs.index', [
            'logs' => $logs,
            'filters' => array_merge($request->only(['start_date', 'end_date']), [
                'sort' => $sort,
            ]),
        ]);
    }

    public function live(Request $request): JsonResponse
    {
        $query = $this->buildFilteredQuery($request, 'latest');

        $afterId = (int) $request->integer('after_id', 0);
        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        }

        $logs = $query
            ->limit(30)
            ->get()
            ->map(fn (SensorLog $log) => [
                'id' => $log->id,
                'timestamp_wita' => $log->recorded_at?->copy()->addHours(8)->format('Y-m-d H:i:s'),
                'rainfall_text' => number_format($log->rainfall_mm ?? 0, 1).' mm',
                'water_level_text' => number_format($log->water_level_cm ?? 0, 1).' cm',
                'rain_status' => $log->rain_status,
                'can_delete' => (bool) $request->user()?->isAdmin(),
                'destroy_url' => route('logs.destroy', $log),
            ])
            ->values();

        return response()->json([
            'logs' => $logs,
        ]);
    }

    public function destroy(Request $request, SensorLog $sensorLog): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $sensorLog->delete();

        return back()->with('status', 'Data log berhasil dihapus.');
    }

    private function buildFilteredQuery(Request $request, string $sort): Builder
    {
        $query = SensorLog::query()
            ->where('device_code', 'alat_1');

        if ($sort === 'oldest') {
            $query->orderBy('recorded_at', 'asc');
        } else {
            $query->orderBy('recorded_at', 'desc');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('recorded_at', '>=', $request->string('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('recorded_at', '<=', $request->string('end_date'));
        }

        return $query;
    }
}
