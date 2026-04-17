<x-layouts.app :title="'Log Data Sensor'" :heading="'Log Data Sensor'" :subheading="'Riwayat data sensor alat 1 dengan filter waktu otomatis.'">
    @php
        $isLatestSort = ($filters['sort'] ?? 'latest') === 'latest';
        $isFirstPage = $logs->currentPage() === 1;
    @endphp

    <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <form method="GET" class="grid gap-3 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Mulai</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" onchange="this.form.submit()" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Sampai</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" onchange="this.form.submit()" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Urutkan Timestamp</label>
                <select name="sort" onchange="this.form.submit()" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="latest" {{ ($filters['sort'] ?? 'latest') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' }}>Terlama</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <a href="{{ route('exports.sensor.csv', array_merge(request()->query(), ['device_code' => 'alat_1'])) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Download CSV</a>
                <a href="{{ route('logs.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-700">Reset</a>
            </div>
        </form>
    </section>

    <section class="mt-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-3 py-2">Timestamp (WITA)</th>
                        <th class="px-3 py-2">Curah Hujan</th>
                        <th class="px-3 py-2">Ketinggian Air</th>
                        <th class="px-3 py-2">Status</th>
                        @if(auth()->user()?->isAdmin())
                            <th class="px-3 py-2">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-t border-slate-100" data-log-id="{{ $log->id }}">
                            <td class="px-3 py-2">{{ $log->recorded_at?->copy()->addHours(8)->format('Y-m-d H:i:s') }}</td>
                            <td class="px-3 py-2">{{ number_format($log->rainfall_mm ?? 0, 1) }} mm</td>
                            <td class="px-3 py-2">{{ number_format($log->water_level_cm ?? 0, 1) }} cm</td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2 py-1 text-xs {{ $log->rain_status === 'Rain' ? 'bg-cyan-100 text-cyan-700' : 'bg-slate-100 text-slate-700' }}">{{ $log->rain_status }}</span>
                            </td>
                            @if(auth()->user()?->isAdmin())
                                <td class="px-3 py-2">
                                    <form method="POST" action="{{ route('logs.destroy', $log) }}" onsubmit="return confirm('Hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-red-300 px-2 py-1 text-xs text-red-700">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr data-empty-row="1">
                            <td colspan="5" class="px-3 py-6 text-center text-slate-500">Data belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </section>

    @if($isLatestSort && $isFirstPage)
        @push('scripts')
            <script>
                (function () {
                    const tbody = document.querySelector('table tbody');
                    if (!tbody) {
                        return;
                    }

                    const csrfToken = @json(csrf_token());
                    const liveUrl = new URL(@json(route('logs.live')));
                    const params = new URLSearchParams(window.location.search);
                    if (params.get('start_date')) {
                        liveUrl.searchParams.set('start_date', params.get('start_date'));
                    }
                    if (params.get('end_date')) {
                        liveUrl.searchParams.set('end_date', params.get('end_date'));
                    }

                    function getLatestId() {
                        const row = tbody.querySelector('tr[data-log-id]');
                        if (!row) {
                            return 0;
                        }

                        const id = Number(row.getAttribute('data-log-id'));
                        return Number.isFinite(id) ? id : 0;
                    }

                    function buildDeleteCell(destroyUrl) {
                        const cell = document.createElement('td');
                        cell.className = 'px-3 py-2';

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = destroyUrl;
                        form.setAttribute('onsubmit', "return confirm('Hapus data ini?')");

                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = csrfToken;

                        const method = document.createElement('input');
                        method.type = 'hidden';
                        method.name = '_method';
                        method.value = 'DELETE';

                        const button = document.createElement('button');
                        button.className = 'rounded-md border border-red-300 px-2 py-1 text-xs text-red-700';
                        button.textContent = 'Hapus';

                        form.appendChild(csrf);
                        form.appendChild(method);
                        form.appendChild(button);
                        cell.appendChild(form);

                        return cell;
                    }

                    function prependRows(logs) {
                        const emptyRow = tbody.querySelector('tr[data-empty-row="1"]');
                        if (emptyRow) {
                            emptyRow.remove();
                        }

                        logs.slice().reverse().forEach((item) => {
                            const tr = document.createElement('tr');
                            tr.className = 'border-t border-slate-100';
                            tr.setAttribute('data-log-id', String(item.id));

                            const timestamp = document.createElement('td');
                            timestamp.className = 'px-3 py-2';
                            timestamp.textContent = item.timestamp_wita ?? '-';

                            const rain = document.createElement('td');
                            rain.className = 'px-3 py-2';
                            rain.textContent = item.rainfall_text;

                            const water = document.createElement('td');
                            water.className = 'px-3 py-2';
                            water.textContent = item.water_level_text;

                            const status = document.createElement('td');
                            status.className = 'px-3 py-2';

                            const badge = document.createElement('span');
                            badge.className = 'rounded-full px-2 py-1 text-xs ' + (item.rain_status === 'Rain' ? 'bg-cyan-100 text-cyan-700' : 'bg-slate-100 text-slate-700');
                            badge.textContent = item.rain_status;
                            status.appendChild(badge);

                            tr.appendChild(timestamp);
                            tr.appendChild(rain);
                            tr.appendChild(water);
                            tr.appendChild(status);

                            if (item.can_delete) {
                                tr.appendChild(buildDeleteCell(item.destroy_url));
                            }

                            tbody.insertBefore(tr, tbody.firstChild);
                        });

                        const rows = Array.from(tbody.querySelectorAll('tr[data-log-id]'));
                        if (rows.length > 20) {
                            rows.slice(20).forEach((row) => row.remove());
                        }
                    }

                    async function pollLiveLogs() {
                        const afterId = getLatestId();
                        const url = new URL(liveUrl.toString());
                        if (afterId > 0) {
                            url.searchParams.set('after_id', String(afterId));
                        }

                        const response = await fetch(url.toString(), {
                            headers: {
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) {
                            return;
                        }

                        const data = await response.json();
                        if (!data || !Array.isArray(data.logs) || data.logs.length === 0) {
                            return;
                        }

                        prependRows(data.logs);
                    }

                    setInterval(() => {
                        pollLiveLogs().catch(() => {});
                    }, 3000);
                })();
            </script>
        @endpush
    @endif
</x-layouts.app>
