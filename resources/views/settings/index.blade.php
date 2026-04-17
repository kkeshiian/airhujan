<x-layouts.app :title="'Settings Deep Sleep'" :heading="'Settings Perangkat'" :subheading="'Atur durasi deep sleep dan koordinat lokasi alat.'">
    <div class="grid items-stretch gap-6 lg:grid-cols-2">
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 flex flex-col">
            <h3 class="font-semibold text-ink">Pengaturan Deep Sleep Alat 1</h3>

            <form method="POST" action="{{ route('settings.update') }}" class="mt-4 flex flex-1 flex-col">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Durasi Deep Sleep (detik)</label>
                    <input type="number" min="30" max="86400" name="deep_sleep_seconds" value="{{ old('deep_sleep_seconds', $setting->deep_sleep_seconds) }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2.5">
                </div>
                <div class="mt-4 flex min-h-16 items-center">
                    <button class="rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white">Simpan Pengaturan</button>
                </div>
            </form>
        </section>

        <!-- Atur Lokasi Alat -->
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 flex flex-col">
            <h3 class="font-semibold text-ink mb-3">Atur Lokasi Alat</h3>
            <form method="POST" action="{{ route('settings.locations.update') }}" class="flex flex-1 flex-col">
                @csrf
                @method('PUT')

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($locations as $location)
                        @php
                            $deviceCode = strtolower((string) $location->device_code);
                            $deviceLabel = $deviceCode === 'alat_1'
                                ? 'Alat Monitoring Hujan'
                                : ($deviceCode === 'alat_2' ? 'Alat Perekam Suara' : ($location->device_name ?? strtoupper($location->device_code)));
                        @endphp
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="mb-3 text-sm font-medium text-slate-700">{{ $deviceLabel }}</p>
                            <div>
                                <input
                                    type="text"
                                    name="locations[{{ $location->device_code }}][coordinates]"
                                    value="{{ old('locations.'.$location->device_code.'.coordinates', number_format((float) $location->latitude, 7, '.', '').','.number_format((float) $location->longitude, 7, '.', '')) }}"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                    placeholder="Latitude,Longitude (contoh: -6.2100000,106.8266660)"
                                >
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex min-h-16 items-center">
                    <button class="rounded-lg bg-ink px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Simpan lokasi</button>
                </div>
            </form>
        </section>
    </div>

    <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h3 class="font-semibold text-ink">Manajemen Penghapusan Data</h3>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="POST" action="{{ route('settings.purge') }}" class="rounded-xl border border-slate-200 p-4">
                @csrf
                <input type="hidden" name="target" value="logs">

                <h4 class="font-semibold text-ink">Hapus Database Log Data</h4>
                <div class="mt-3">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Rentang Waktu</label>
                    <select name="range" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        <option value="1d">1 hari terakhir</option>
                        <option value="1w">1 minggu terakhir</option>
                        <option value="1m">1 bulan terakhir</option>
                        <option value="all">Semua data</option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="mt-3 rounded-lg border border-red-300 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100"
                    onclick="return confirm('Yakin ingin menghapus data log sesuai rentang yang dipilih?')"
                >
                    Hapus data log
                </button>
            </form>

            <form method="POST" action="{{ route('settings.purge') }}" class="rounded-xl border border-slate-200 p-4">
                @csrf
                <input type="hidden" name="target" value="audio">

                <h4 class="font-semibold text-ink">Hapus Database Audio</h4>
                <div class="mt-3">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Rentang Waktu</label>
                    <select name="range" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                        <option value="1d">1 hari terakhir</option>
                        <option value="1w">1 minggu terakhir</option>
                        <option value="1m">1 bulan terakhir</option>
                        <option value="all">Semua data</option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="mt-3 rounded-lg border border-red-300 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100"
                    onclick="return confirm('Yakin ingin menghapus data audio sesuai rentang yang dipilih?')"
                >
                    Hapus data audio
                </button>
            </form>
        </div>
    </section>
</x-layouts.app>
