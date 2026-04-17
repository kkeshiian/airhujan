<?php

namespace App\Http\Controllers;

use App\Models\AudioRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
}
