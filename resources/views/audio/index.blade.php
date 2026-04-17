<x-layouts.app :title="'Rekaman Audio'" :heading="'Rekaman Audio'" :subheading="'Akses rekaman hujan dari alat 2, putar dan unduh data.'">
    <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-3 py-2">Timestamp</th>
                        <th class="px-3 py-2">Perangkat</th>
                        <th class="px-3 py-2">Judul</th>
                        <th class="px-3 py-2">Durasi</th>
                        <th class="px-3 py-2">Pemutar</th>
                        <th class="px-3 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        @php
                            $formattedTitle = $record->recorded_at
                                ? 'hujan-'.$record->recorded_at->format('HisdmY')
                                : ($record->title ?? '-');
                        @endphp
                        <tr class="border-t border-slate-100 align-top">
                            <td class="px-3 py-2">{{ $record->recorded_at?->copy()->addHours(8)->format('Y-m-d H:i:s') ?? '-' }}</td>
                            <td class="px-3 py-2 uppercase tracking-[0.18em] text-xs text-slate-500">{{ strtoupper($record->device_code) }}</td>
                            <td class="px-3 py-2 font-medium text-ink">{{ $formattedTitle }}</td>
                            <td class="px-3 py-2">{{ $record->duration_seconds ?? '-' }} detik</td>
                            <td class="px-3 py-2 min-w-[280px]">
                                <audio controls class="w-full max-w-md">
                                    <source src="{{ asset('storage/'.$record->file_path) }}" type="audio/mpeg">
                                </audio>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('audio.download', $record) }}" class="rounded-md bg-ink px-3 py-1.5 text-xs font-semibold text-white">Download</a>
                                    @if(auth()->user()?->isAdmin())
                                        <form method="POST" action="{{ route('audio.destroy', $record) }}" onsubmit="return confirm('Hapus rekaman ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-md border border-red-300 px-2 py-1 text-xs text-red-700">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-slate-500">Belum ada rekaman audio.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $records->links() }}</div>
    </section>
</x-layouts.app>
