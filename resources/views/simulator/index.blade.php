<x-layouts.app :title="'Simulator MQTT Admin'" :heading="'Simulator MQTT Admin'" :subheading="'Input manual untuk mensimulasikan data MQTT sensor dan audio.'">
    <section class="mb-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-semibold text-ink">Auto Ketinggian Air (Ultrasonic)</h3>
            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ ($autoConfig['enabled'] ?? false) ? 'bg-emerald-100 text-emerald-800 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                {{ ($autoConfig['enabled'] ?? false) ? 'AKTIF' : 'NONAKTIF' }}
            </span>
        </div>

        <form method="POST" action="{{ route('simulator.auto.update') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @csrf
            @method('PUT')

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Mode Auto Generator</span>
                <select name="sim_auto_enabled" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
                    <option value="1" @selected((int) old('sim_auto_enabled', ($autoConfig['enabled'] ?? false) ? 1 : 0) === 1)>Aktif</option>
                    <option value="0" @selected((int) old('sim_auto_enabled', ($autoConfig['enabled'] ?? false) ? 1 : 0) === 0)>Nonaktif</option>
                </select>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Interval Insert Otomatis (menit)</span>
                <input type="number" min="1" max="180" step="1" name="sim_interval_minutes" value="{{ old('sim_interval_minutes', (int) ($autoConfig['interval_minutes'] ?? 15)) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Kedalaman Air (cm)</span>
                <input id="autoDepthInput" type="number" min="0" max="5000" step="0.01" name="sim_water_depth_cm" value="{{ old('sim_water_depth_cm', number_format((float) ($autoConfig['water_depth_cm'] ?? 0), 2, '.', '')) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Jarak Permukaan Air ke Sensor Ultrasonic (cm)</span>
                <input id="autoDistanceInput" type="number" min="0" max="5000" step="0.01" name="sim_ultrasonic_distance_cm" value="{{ old('sim_ultrasonic_distance_cm', number_format((float) ($autoConfig['ultrasonic_distance_cm'] ?? 0), 2, '.', '')) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Target Malam (Pasang) (cm)</span>
                <input id="autoNightTargetInput" type="number" min="0" max="5000" step="0.01" name="sim_night_target_cm" value="{{ old('sim_night_target_cm', number_format((float) ($autoConfig['night_target_cm'] ?? 120), 2, '.', '')) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Target Siang (Surut) (cm)</span>
                <input id="autoNoonTargetInput" type="number" min="0" max="5000" step="0.01" name="sim_noon_peak_target_cm" value="{{ old('sim_noon_peak_target_cm', number_format((float) ($autoConfig['noon_peak_target_cm'] ?? 95), 2, '.', '')) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Laju Naik per Interval (cm)</span>
                <input id="autoNightRiseInput" type="number" min="0.01" max="100" step="0.001" name="sim_night_rise_cm" value="{{ old('sim_night_rise_cm', number_format((float) ($autoConfig['night_rise_cm'] ?? 0.25), 3, '.', '')) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Laju Turun per Interval (cm)</span>
                <input id="autoDayDropInput" type="number" min="0.01" max="100" step="0.001" name="sim_day_drop_cm" value="{{ old('sim_day_drop_cm', number_format((float) ($autoConfig['day_drop_cm'] ?? 0.20), 3, '.', '')) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
            </label>

            <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200 md:col-span-2 xl:col-span-2">
                <p class="text-xs text-slate-500">Terakhir Auto Insert</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">
                    {{ ($autoConfig['last_generated_at'] ?? null)?->format('Y-m-d H:i:s') ?? '-' }}
                </p>
                <p class="mt-2 text-xs text-slate-500">Mode target: malam menuju target pasang, pagi-siang menuju target surut, sore kembali ke target pasang. Boost hujan dihitung otomatis dari tip baru.</p>
                <div class="mt-2 grid gap-2 text-sm md:grid-cols-2">
                    <p class="font-semibold text-slate-700">Level Saat Ini (Log Terakhir): <span id="autoCurrentLevelPreview">0.00</span> cm</p>
                    <p class="font-semibold text-indigo-700">Target Saat Ini: <span id="autoCurrentTargetPreview">0.00</span> cm</p>
                    <p class="font-semibold text-cyan-700">Boost Tip/Hujan: <span id="autoRainBoostPreview">0.00</span> cm</p>
                    <p class="font-semibold text-emerald-700">Air Menuju: <span id="autoTowardsPreview">0.00</span> cm</p>
                    <p class="font-semibold text-slate-700">Prediksi 1 Interval: <span id="autoNextLevelPreview">0.00</span> cm</p>
                </div>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Simpan Pengaturan Auto</button>
            </div>
        </form>
    </section>

    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-semibold text-ink">Tip Hujan Manual</h3>
            <span class="inline-flex rounded-full bg-cyan-100 px-2.5 py-1 text-xs font-medium text-cyan-800 ring-1 ring-cyan-200">1 tip = {{ number_format($mmPerTip, 2) }} mm</span>
        </div>

        <form method="POST" action="{{ route('simulator.sensor.store') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @csrf

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Kontrol Tip Hujan (pakai +/-)</span>
                <div class="rounded-xl border border-slate-300 p-2">
                    <div class="flex items-center gap-2">
                        <button id="tipMinusButton" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-300 bg-white text-xl font-semibold text-slate-700 hover:bg-slate-50">-</button>
                        <button id="tipPlusButton" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-300 bg-white text-xl font-semibold text-slate-700 hover:bg-slate-50">+</button>
                        <div class="flex-1 rounded-lg bg-slate-50 px-3 py-2 ring-1 ring-slate-200">
                            <p class="text-[11px] text-slate-500">Perubahan Tip pada Submit Ini</p>
                            <p class="text-base font-semibold text-slate-900"><span id="tipDeltaPreview">0</span></p>
                        </div>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-lg bg-slate-50 px-3 py-2 ring-1 ring-slate-200">
                            <p class="text-slate-500">Tip Harian Saat Ini</p>
                            <p class="font-semibold text-slate-900"><span id="baseDailyTip">{{ (int) $currentDailyTip }}</span></p>
                        </div>
                        <div class="rounded-lg bg-slate-50 px-3 py-2 ring-1 ring-slate-200">
                            <p class="text-slate-500">Tip Harian Setelah Submit</p>
                            <p class="font-semibold text-slate-900"><span id="nextDailyTipPreview">{{ (int) $currentDailyTip }}</span></p>
                        </div>
                    </div>
                </div>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Status Hujan</span>
                <select name="rain_state" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
                    <option value="auto" @selected(old('rain_state', 'auto') === 'auto')>Auto dari tip</option>
                    <option value="rain" @selected(old('rain_state') === 'rain')>Paksa Rain</option>
                    <option value="no_rain" @selected(old('rain_state') === 'no_rain')>Paksa No Rain</option>
                </select>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Ketinggian Air Manual (cm)</span>
                <input id="manualWaterLevelInput" type="number" min="0" max="5000" step="0.01" name="water_level_cm" value="{{ old('water_level_cm', number_format((float) $defaultManualWaterLevel, 2, '.', '')) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Waktu Data (custom)</span>
                <input type="datetime-local" step="1" name="recorded_at" value="{{ old('recorded_at', $defaultTipRecordedAt) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
            </label>

            <input id="tipDeltaInput" type="hidden" name="tip_delta" value="{{ old('tip_delta', 0) }}">
            <input type="hidden" name="device_code" value="alat_1">

            <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-200">
                <p class="text-xs text-slate-500">Preview Curah Hujan</p>
                <p class="mt-1 text-lg font-semibold text-cyan-700"><span id="rainfallPreview">0.00</span> mm</p>
                <p class="mt-3 text-xs text-slate-500">Ketinggian Air yang Disimpan</p>
                <p class="mt-1 text-lg font-semibold text-emerald-700"><span id="manualWaterLevelPreview">0.00</span> cm</p>
                <p class="mt-3 text-xs text-slate-500">Tip harian dihitung per tanggal input.</p>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Simpan Simulasi Sensor</button>
            </div>
        </form>
    </section>

    <section class="mt-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-semibold text-ink">Upload Audio WAV (Simulasi ALAT 2)</h3>
            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">Format wajib: .wav</span>
        </div>

        <form method="POST" action="{{ route('simulator.audio.store') }}" enctype="multipart/form-data" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @csrf

            <label class="block text-sm md:col-span-2 xl:col-span-1">
                <span class="mb-1 block text-xs font-medium text-slate-600">File Audio WAV</span>
                <input type="file" name="audio_file" accept=".wav,audio/wav" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" required>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Judul Rekaman (opsional)</span>
                <input type="text" name="title" maxlength="120" value="{{ old('title') }}" placeholder="Kosongkan untuk auto-title" class="w-full rounded-xl border border-slate-300 px-3 py-2">
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Durasi (detik, opsional)</span>
                <input type="number" min="1" max="3600" step="1" name="duration_seconds" value="{{ old('duration_seconds') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">Waktu Data (custom)</span>
                <input type="datetime-local" step="1" name="recorded_at" value="{{ old('recorded_at', $defaultRecordedAt) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2" required>
            </label>

            <input type="hidden" name="device_code" value="alat_2">

            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Upload Simulasi Audio</button>
            </div>
        </form>
    </section>

    @push('scripts')
        <script>
            (function () {
                const mmPerTip = {{ json_encode((float) $mmPerTip) }};
                const baseDailyTip = {{ json_encode((int) $currentDailyTip) }};
                const latestWaterLevel = {{ json_encode((float) $latestWaterLevel) }};
                const autoDepthInput = document.getElementById('autoDepthInput');
                const autoDistanceInput = document.getElementById('autoDistanceInput');
                const autoNightTargetInput = document.getElementById('autoNightTargetInput');
                const autoNoonTargetInput = document.getElementById('autoNoonTargetInput');
                const autoNightRiseInput = document.getElementById('autoNightRiseInput');
                const autoDayDropInput = document.getElementById('autoDayDropInput');
                const autoCurrentLevelPreview = document.getElementById('autoCurrentLevelPreview');
                const autoCurrentTargetPreview = document.getElementById('autoCurrentTargetPreview');
                const autoRainBoostPreview = document.getElementById('autoRainBoostPreview');
                const autoTowardsPreview = document.getElementById('autoTowardsPreview');
                const autoNextLevelPreview = document.getElementById('autoNextLevelPreview');
                const tipDeltaInput = document.getElementById('tipDeltaInput');
                const tipMinusButton = document.getElementById('tipMinusButton');
                const tipPlusButton = document.getElementById('tipPlusButton');
                const tipDeltaPreview = document.getElementById('tipDeltaPreview');
                const nextDailyTipPreview = document.getElementById('nextDailyTipPreview');
                const baseDailyTipEl = document.getElementById('baseDailyTip');
                const rainfallPreview = document.getElementById('rainfallPreview');
                const manualWaterLevelInput = document.getElementById('manualWaterLevelInput');
                const manualWaterLevelPreview = document.getElementById('manualWaterLevelPreview');
                const recordedAtInput = document.querySelector('input[name="recorded_at"]');

                if (!tipDeltaInput || !tipMinusButton || !tipPlusButton || !tipDeltaPreview || !nextDailyTipPreview || !baseDailyTipEl || !rainfallPreview || !manualWaterLevelInput || !manualWaterLevelPreview || !recordedAtInput || !autoDepthInput || !autoDistanceInput || !autoNightTargetInput || !autoNoonTargetInput || !autoNightRiseInput || !autoDayDropInput || !autoCurrentLevelPreview || !autoCurrentTargetPreview || !autoRainBoostPreview || !autoTowardsPreview || !autoNextLevelPreview) {
                    return;
                }

                function resolveAutoBaseTarget(hourFraction, nightTarget, noonTarget) {
                    if (hourFraction >= 18 || hourFraction < 5) {
                        return nightTarget;
                    }

                    if (hourFraction < 12) {
                        const progress = (hourFraction - 5) / 7;
                        return nightTarget + ((noonTarget - nightTarget) * progress);
                    }

                    const progress = (hourFraction - 12) / 6;
                    return noonTarget + ((nightTarget - noonTarget) * progress);
                }

                function asNumber(value, fallback = 0) {
                    const parsed = Number(value);
                    return Number.isFinite(parsed) ? parsed : fallback;
                }

                function resolveRainBoostCm(rainfallIncreaseMm) {
                    if (rainfallIncreaseMm <= 0) {
                        return 0;
                    }

                    if (rainfallIncreaseMm <= 2) {
                        return rainfallIncreaseMm * 0.10;
                    }

                    if (rainfallIncreaseMm <= 10) {
                        return (2 * 0.10) + ((rainfallIncreaseMm - 2) * 0.16);
                    }

                    return (2 * 0.10) + (8 * 0.16) + ((rainfallIncreaseMm - 10) * 0.22);
                }

                function resolveTipProjection() {
                    const delta = asNumber(tipDeltaInput.value, 0);
                    const selectedDate = new Date(recordedAtInput.value);
                    const now = new Date();
                    const isSameDay = !Number.isNaN(selectedDate.getTime())
                        && selectedDate.getFullYear() === now.getFullYear()
                        && selectedDate.getMonth() === now.getMonth()
                        && selectedDate.getDate() === now.getDate();
                    const dailyBase = isSameDay ? baseDailyTip : 0;
                    const nextDailyTip = Math.max(0, dailyBase + delta);
                    const newTipCount = Math.max(0, nextDailyTip - dailyBase);
                    const rainfall = nextDailyTip * mmPerTip;

                    return {
                        delta,
                        dailyBase,
                        nextDailyTip,
                        rainfall,
                        rainfallIncreaseMm: newTipCount * mmPerTip,
                    };
                }

                function refreshPreview() {
                    const projection = resolveTipProjection();

                    baseDailyTipEl.textContent = String(projection.dailyBase);
                    tipDeltaPreview.textContent = String(projection.delta);
                    nextDailyTipPreview.textContent = String(projection.nextDailyTip);
                    rainfallPreview.textContent = projection.rainfall.toFixed(2);
                    manualWaterLevelPreview.textContent = asNumber(manualWaterLevelInput.value, 0).toFixed(2);
                }

                function refreshAutoPreview() {
                    const depth = Math.max(0, asNumber(autoDepthInput.value, 0));
                    const distance = Math.max(0, asNumber(autoDistanceInput.value, 0));
                    const nightTarget = Math.max(0, asNumber(autoNightTargetInput.value, 0));
                    const noonTarget = Math.max(0, asNumber(autoNoonTargetInput.value, 0));
                    const riseStep = Math.max(0.01, asNumber(autoNightRiseInput.value, 0.25));
                    const dropStep = Math.max(0.01, asNumber(autoDayDropInput.value, 0.2));
                    const fallbackLevel = Math.max(0, depth - distance);
                    const currentLevel = Math.max(0, asNumber(latestWaterLevel, fallbackLevel));
                    const projection = resolveTipProjection();

                    const now = new Date();
                    const hourFraction = now.getHours() + (now.getMinutes() / 60);
                    const baseTarget = resolveAutoBaseTarget(hourFraction, nightTarget, noonTarget);
                    const rainBoost = resolveRainBoostCm(projection.rainfallIncreaseMm);
                    const effectiveTarget = Math.min(depth, Math.max(0, baseTarget + rainBoost));

                    let nextLevel = currentLevel;
                    if (effectiveTarget >= currentLevel) {
                        nextLevel = Math.min(effectiveTarget, currentLevel + riseStep);
                    } else {
                        nextLevel = Math.max(effectiveTarget, currentLevel - dropStep);
                    }

                    autoCurrentLevelPreview.textContent = currentLevel.toFixed(2);
                    autoCurrentTargetPreview.textContent = baseTarget.toFixed(2);
                    autoRainBoostPreview.textContent = rainBoost.toFixed(2);
                    autoTowardsPreview.textContent = effectiveTarget.toFixed(2);
                    autoNextLevelPreview.textContent = nextLevel.toFixed(2);
                }

                function addDelta(step) {
                    const current = asNumber(tipDeltaInput.value, 0);
                    tipDeltaInput.value = String(current + step);
                    refreshPreview();
                    refreshAutoPreview();
                }

                ['input', 'change'].forEach((eventName) => {
                    recordedAtInput.addEventListener(eventName, refreshPreview);
                    manualWaterLevelInput.addEventListener(eventName, refreshPreview);
                    autoDepthInput.addEventListener(eventName, refreshAutoPreview);
                    autoDistanceInput.addEventListener(eventName, refreshAutoPreview);
                    autoNightTargetInput.addEventListener(eventName, refreshAutoPreview);
                    autoNoonTargetInput.addEventListener(eventName, refreshAutoPreview);
                    autoNightRiseInput.addEventListener(eventName, refreshAutoPreview);
                    autoDayDropInput.addEventListener(eventName, refreshAutoPreview);
                });

                tipPlusButton.addEventListener('click', function () {
                    addDelta(1);
                });

                tipMinusButton.addEventListener('click', function () {
                    addDelta(-1);
                });

                refreshPreview();
                refreshAutoPreview();
            })();
        </script>
    @endpush
</x-layouts.app>
