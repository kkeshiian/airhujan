<?php

namespace App\Http\Controllers;

use App\Models\AudioRecord;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AudioController extends Controller
{
    public function index(): View
    {
        $records = AudioRecord::query()->latest('recorded_at')->paginate(12);

        return view('audio.index', [
            'records' => $records,
        ]);
    }

    public function download(AudioRecord $audioRecord): StreamedResponse
    {
        if (Storage::disk('public')->exists($audioRecord->file_path)) {
            return Storage::disk('public')->download($audioRecord->file_path, basename($audioRecord->file_path));
        }

        $content = "Simulasi file audio untuk {$audioRecord->title}.";

        return response()->streamDownload(function () use ($content): void {
            echo $content;
        }, str_replace(' ', '-', strtolower($audioRecord->title)).'.txt');
    }

    public function destroy(Request $request, AudioRecord $audioRecord): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $audioRecord->delete();

        return back()->with('status', 'Rekaman audio berhasil dihapus.');
    }

    public function uploadRaw(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'device_id' => ['nullable', 'string', 'max:50'],
            'trigger_device' => ['nullable', 'string', 'max:50'],
            'duration_sec' => ['nullable', 'integer', 'between:1,600'],
            'rain_status' => ['nullable', 'integer', 'in:0,1'],
            'filename' => ['required', 'string', 'max:120'],
        ]);

        $binary = $request->getContent();

        if (!is_string($binary) || $binary === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Body audio kosong.',
            ], 422);
        }

        $rawName = basename((string) $payload['filename']);
        $safeName = Str::of($rawName)
            ->replaceMatches('/[^A-Za-z0-9._-]/', '_')
            ->toString();

        if (!Str::endsWith(Str::lower($safeName), '.wav')) {
            $safeName .= '.wav';
        }

        $storedName = now()->format('Ymd_His').'_'.$safeName;
        $relativePath = 'audio/uploads/'.$storedName;

        Storage::disk('public')->put($relativePath, $binary);

        $recordedAt = $this->extractRecordedAtFromFilename($safeName);
        $deviceCode = (string) ($payload['device_id'] ?? 'alat_2');
        $triggerDevice = (string) ($payload['trigger_device'] ?? 'alat_1');
        $isRaining = ((int) ($payload['rain_status'] ?? 0)) === 1;

        $audioRecord = AudioRecord::query()->create([
            'title' => 'Rekaman '.$deviceCode.' '.Str::of($safeName)->replace('.wav', '')->replace('_', ' '),
            'device_code' => $deviceCode,
            'file_path' => $relativePath,
            'duration_seconds' => (int) ($payload['duration_sec'] ?? 60),
            'device_status' => sprintf(
                'upload raw dari %s | trigger %s | rain=%s',
                $deviceCode,
                $triggerDevice,
                $isRaining ? 'yes' : 'no'
            ),
            'recorded_at' => $recordedAt,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Upload audio berhasil.',
            'id' => $audioRecord->id,
            'file_path' => $relativePath,
            'size_bytes' => strlen($binary),
        ], 201);
    }

    private function extractRecordedAtFromFilename(string $filename): Carbon
    {
        $name = Str::of($filename)->replace('.wav', '')->toString();

        if (preg_match('/^(\d{6})_(\d{8})(?:_\d+)?$/', $name, $matches) === 1) {
            $time = $matches[1];
            $date = $matches[2];

            try {
                return Carbon::createFromFormat('His_dmY', $time.'_'.$date, 'Asia/Makassar')
                    ->timezone(config('app.timezone', 'UTC'));
            } catch (\Throwable) {
                return now();
            }
        }

        return now();
    }
}
