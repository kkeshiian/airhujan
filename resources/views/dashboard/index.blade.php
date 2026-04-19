<x-layouts.app :title="'Dashboard IoT Curah Hujan'" :heading="'Dashboard IoT Curah Hujan'" :subheading="'Monitoring realtime, kontrol command, dan konfigurasi ESP32 via MQTT.'">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Status Hujan</p>
            <div class="mt-3 flex items-center gap-2">
                <span id="rainStatusDot" class="inline-block h-3 w-3 rounded-full {{ ($latestAlat1?->is_raining ?? false) ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                <p id="rainStatusText" class="text-lg font-semibold {{ ($latestAlat1?->is_raining ?? false) ? 'text-red-700' : 'text-emerald-700' }}">
                    {{ ($latestAlat1?->is_raining ?? false) ? 'HUJAN' : 'TIDAK HUJAN' }}
                </p>
            </div>
        </article>

        <article class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Curah Hujan Hari Ini</p>
            <p class="mt-3 text-3xl font-bold text-cyan-700"><span id="dailyRainMmValue">{{ number_format($latestAlat1?->rainfall_mm ?? 0, 2) }}</span> <span class="text-sm">mm</span></p>
        </article>

        <article class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Tinggi Air</p>
            <p class="mt-3 text-3xl font-bold text-indigo-700"><span id="waterLevelValue">{{ number_format($latestAlat1?->water_level_cm ?? 0, 0) }}</span> <span class="text-sm">cm</span></p>
        </article>
    </section>

    <section class="mt-5 grid gap-4 lg:grid-cols-2">
        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-semibold text-ink">Grafik Curah Hujan</h3>
                <select id="rainChartView" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                    <option value="24">24 titik terakhir</option>
                    <option value="12">12 titik terakhir</option>
                    <option value="6">6 titik terakhir</option>
                </select>
            </div>
            <div class="h-[260px]">
                <canvas id="rainChart"></canvas>
            </div>
        </article>

        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-semibold text-ink">Grafik Tinggi Air</h3>
                <select id="waterChartView" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                    <option value="24">24 titik terakhir</option>
                    <option value="12">12 titik terakhir</option>
                    <option value="6">6 titik terakhir</option>
                </select>
            </div>
            <div class="h-[260px]">
                <canvas id="waterChart"></canvas>
            </div>
        </article>
    </section>

    <section class="mt-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-semibold text-ink">Telemetri ESP32 Realtime</h3>
            <span id="lastDataInBadge" class="inline-flex rounded-full bg-cyan-100 px-2.5 py-1 text-xs font-medium text-cyan-800 ring-1 ring-cyan-200">Terakhir data masuk: - WITA</span>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Mode ESP</p>
                <p id="telemetryEspMode" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Boot Type</p>
                <p id="telemetryBootType" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Wake Reason</p>
                <p id="telemetryWakeReason" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Current State</p>
                <p id="telemetryCurrentState" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Deep Sleep Reason</p>
                <p id="telemetryDeepSleepReason" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Status Hujan Firmware</p>
                <p id="telemetryIsRain" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">WiFi State</p>
                <p id="telemetryWifiState" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Relay State</p>
                <p id="telemetryRelayState" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Startup Window</p>
                <p id="telemetryStartupWindow" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Startup With WiFi</p>
                <p id="telemetryStartupWithWifi" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">WiFi Mode Started</p>
                <p id="telemetryWifiModeStarted" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">WiFi Ready</p>
                <p id="telemetryWifiReady" class="mt-1 text-sm font-semibold text-slate-900">-</p>
            </div>
        </div>
    </section>

    <section class="mt-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-semibold text-ink">Configuration Panel ESP32</h3>
            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">Mode normal: deep sleep | Mode debug: selalu aktif</span>
        </div>

        <form id="deviceConfigForm" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">sleep_minutes</span>
                <input type="number" min="1" max="1440" step="1" name="sleep_minutes" value="{{ (int) $deviceSetting->sleep_minutes }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">awake_minutes</span>
                <input type="number" min="1" max="240" step="1" name="awake_minutes" value="{{ (int) $deviceSetting->awake_minutes }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">rain_tip_threshold</span>
                <input type="number" min="1" max="200" step="1" name="rain_tip_threshold" value="{{ (int) $deviceSetting->rain_tip_threshold }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">rain_stop_timeout_ms</span>
                <input type="number" min="1000" max="1800000" step="100" name="rain_stop_timeout_ms" value="{{ (int) $deviceSetting->rain_stop_timeout_ms }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">wifi_warmup_ms</span>
                <input type="number" min="100" step="100" name="wifi_warmup_ms" value="{{ (int) $deviceSetting->wifi_warmup_ms }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">mm_per_tip</span>
                <input type="number" min="0.01" max="20" step="0.001" name="mm_per_tip" value="{{ number_format((float) $deviceSetting->mm_per_tip, 3, '.', '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">baseline_cm</span>
                <input type="number" min="0" max="1000" step="0.01" name="baseline_cm" value="{{ number_format((float) $deviceSetting->baseline_cm, 2, '.', '') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2">
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">esp_mode</span>
                <select name="esp_mode" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    <option value="0" @selected((int) $deviceSetting->esp_mode === 0)>0 - NORMAL</option>
                    <option value="1" @selected((int) $deviceSetting->esp_mode === 1)>1 - DEBUG</option>
                </select>
            </label>

            <label class="block text-sm">
                <span class="mb-1 block text-xs font-medium text-slate-600">force_rain</span>
                <select name="force_rain" class="w-full rounded-xl border border-slate-300 px-3 py-2">
                    <option value="0" @selected(!$deviceSetting->force_rain)>false</option>
                    <option value="1" @selected($deviceSetting->force_rain)>true</option>
                </select>
            </label>

            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900">Simpan & Publish Config</button>
            </div>
        </form>

        <p id="configStatus" class="mt-3 text-xs text-slate-500">Konfigurasi siap dikirim.</p>
    </section>

    @push('head')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script src="https://unpkg.com/mqtt@5.10.3/dist/mqtt.min.js"></script>
    @endpush

    @push('scripts')
        <script>
            const mqttConfig = @json($mqtt);
            const dashboardLiveEndpoint = @json(route('dashboard.live'));
            const dashboardChartEndpoint = @json(route('dashboard.chart-data'));
            const dashboardConfigEndpoint = @json(route('dashboard.config'));
            const csrfToken = @json(csrf_token());

            const rainStatusDotEl = document.getElementById('rainStatusDot');
            const rainStatusTextEl = document.getElementById('rainStatusText');
            const dailyRainMmValueEl = document.getElementById('dailyRainMmValue');
            const waterLevelValueEl = document.getElementById('waterLevelValue');
            const configStatusEl = document.getElementById('configStatus');
            const deviceConfigFormEl = document.getElementById('deviceConfigForm');
            const rainChartViewEl = document.getElementById('rainChartView');
            const waterChartViewEl = document.getElementById('waterChartView');
            const telemetryEspModeEl = document.getElementById('telemetryEspMode');
            const telemetryBootTypeEl = document.getElementById('telemetryBootType');
            const telemetryWakeReasonEl = document.getElementById('telemetryWakeReason');
            const telemetryCurrentStateEl = document.getElementById('telemetryCurrentState');
            const telemetryDeepSleepReasonEl = document.getElementById('telemetryDeepSleepReason');
            const telemetryIsRainEl = document.getElementById('telemetryIsRain');
            const telemetryWifiStateEl = document.getElementById('telemetryWifiState');
            const telemetryRelayStateEl = document.getElementById('telemetryRelayState');
            const telemetryStartupWindowEl = document.getElementById('telemetryStartupWindow');
            const telemetryStartupWithWifiEl = document.getElementById('telemetryStartupWithWifi');
            const telemetryWifiModeStartedEl = document.getElementById('telemetryWifiModeStarted');
            const telemetryWifiReadyEl = document.getElementById('telemetryWifiReady');
            const lastDataInBadgeEl = document.getElementById('lastDataInBadge');

            let chartLabels = @json($chartLabels);
            let rainfallData = @json($rainfallData);
            let waterLevelData = @json($waterLevelData);

            function asNumber(value, fallback = 0) {
                const parsed = Number(value);
                return Number.isFinite(parsed) ? parsed : fallback;
            }

            function asBool(value) {
                if (typeof value === 'boolean') {
                    return value;
                }

                if (typeof value === 'number') {
                    return value === 1;
                }

                if (typeof value === 'string') {
                    const normalized = value.trim().toLowerCase();
                    return ['1', 'true', 'yes', 'on'].includes(normalized);
                }

                return false;
            }

            function updateRainBadge(isRaining) {
                if (!rainStatusTextEl || !rainStatusDotEl) {
                    return;
                }

                rainStatusTextEl.textContent = isRaining ? 'HUJAN' : 'TIDAK HUJAN';
                rainStatusTextEl.classList.remove('text-red-700', 'text-emerald-700');
                rainStatusDotEl.classList.remove('bg-red-500', 'bg-emerald-500');

                if (isRaining) {
                    rainStatusTextEl.classList.add('text-red-700');
                    rainStatusDotEl.classList.add('bg-red-500');
                } else {
                    rainStatusTextEl.classList.add('text-emerald-700');
                    rainStatusDotEl.classList.add('bg-emerald-500');
                }
            }

            function updateStatusFields(payload) {
                const isRaining = asBool(payload.latest_is_raining ?? payload.is_raining);

                updateRainBadge(isRaining);

                if (dailyRainMmValueEl) {
                    dailyRainMmValueEl.textContent = asNumber(payload.latest_rainfall_mm ?? payload.daily_rain_mm).toFixed(2);
                }

                if (waterLevelValueEl) {
                    waterLevelValueEl.textContent = String(Math.round(asNumber(payload.latest_water_level_cm ?? payload.jarak_air_cm)));
                }

                updateTelemetryFields(payload);
            }

            function asLabel(value, fallback = '-') {
                if (value === null || value === undefined) {
                    return fallback;
                }

                const text = String(value).trim();
                return text === '' ? fallback : text;
            }

            function asOnOffLabel(value) {
                return asBool(value) ? 'ON / TRUE' : 'OFF / FALSE';
            }

            function formatWitaTime(dateValue) {
                return new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Makassar',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false,
                }).format(dateValue);
            }

            function updateLastDataBadge(dateValue = new Date()) {
                if (!lastDataInBadgeEl) {
                    return;
                }

                lastDataInBadgeEl.textContent = `Terakhir data masuk: ${formatWitaTime(dateValue)} WITA`;
            }

            function hasValue(value) {
                return value !== null && value !== undefined && String(value).trim() !== '';
            }

            function updateTelemetryFields(payload) {
                if (telemetryEspModeEl && (hasValue(payload.esp_mode_name) || hasValue(payload.latest_esp_mode_name) || hasValue(payload.esp_mode) || hasValue(payload.latest_esp_mode))) {
                    telemetryEspModeEl.textContent = asLabel(payload.esp_mode_name ?? payload.latest_esp_mode_name, asNumber(payload.esp_mode ?? payload.latest_esp_mode) === 1 ? 'DEBUG' : 'NORMAL').toUpperCase();
                }

                if (telemetryBootTypeEl && hasValue(payload.boot_type)) {
                    telemetryBootTypeEl.textContent = asLabel(payload.boot_type);
                }

                if (telemetryWakeReasonEl && hasValue(payload.wake_reason)) {
                    telemetryWakeReasonEl.textContent = asLabel(payload.wake_reason);
                }

                if (telemetryCurrentStateEl && (hasValue(payload.current_state) || hasValue(payload.device_position))) {
                    telemetryCurrentStateEl.textContent = asLabel(payload.current_state ?? payload.device_position);
                }

                if (telemetryDeepSleepReasonEl && hasValue(payload.deep_sleep_reason)) {
                    telemetryDeepSleepReasonEl.textContent = asLabel(payload.deep_sleep_reason);
                }

                if (telemetryIsRainEl && (hasValue(payload.is_rain) || hasValue(payload.is_raining) || hasValue(payload.latest_is_raining))) {
                    telemetryIsRainEl.textContent = asLabel(payload.is_rain, asBool(payload.is_raining ?? payload.latest_is_raining) ? 'RAIN' : 'NO_RAIN').toUpperCase();
                }

                if (telemetryWifiStateEl && hasValue(payload.wifi_state)) {
                    telemetryWifiStateEl.textContent = asLabel(payload.wifi_state);
                }

                if (telemetryRelayStateEl && hasValue(payload.relay_state)) {
                    telemetryRelayStateEl.textContent = asLabel(payload.relay_state).toUpperCase();
                }

                if (telemetryStartupWindowEl && hasValue(payload.startup_window_active)) {
                    telemetryStartupWindowEl.textContent = asOnOffLabel(payload.startup_window_active);
                }

                if (telemetryStartupWithWifiEl && hasValue(payload.startup_with_wifi)) {
                    telemetryStartupWithWifiEl.textContent = asOnOffLabel(payload.startup_with_wifi);
                }

                if (telemetryWifiModeStartedEl && hasValue(payload.wifi_mode_started)) {
                    telemetryWifiModeStartedEl.textContent = asOnOffLabel(payload.wifi_mode_started);
                }

                if (telemetryWifiReadyEl && hasValue(payload.wifi_ready_to_connect)) {
                    telemetryWifiReadyEl.textContent = asOnOffLabel(payload.wifi_ready_to_connect);
                }
            }

            function chartSlice(data, limit) {
                const safeLimit = Math.max(1, Number(limit) || 24);
                return data.slice(-safeLimit);
            }

            const rainChart = new Chart(document.getElementById('rainChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: chartSlice(chartLabels, 24),
                    datasets: [{
                        label: 'Curah Hujan (mm)',
                        data: chartSlice(rainfallData, 24),
                        borderColor: '#0891b2',
                        backgroundColor: 'rgba(6, 182, 212, 0.6)',
                        borderWidth: 2,
                        borderRadius: 5,
                        maxBarThickness: 28,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            ticks: { maxTicksLimit: 8 },
                        },
                    },
                },
            });

            const waterChart = new Chart(document.getElementById('waterChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: chartSlice(chartLabels, 24),
                    datasets: [{
                        label: 'Tinggi Air (cm)',
                        data: chartSlice(waterLevelData, 24),
                        borderColor: '#4338ca',
                        backgroundColor: 'rgba(99, 102, 241, 0.6)',
                        borderWidth: 2,
                        borderRadius: 5,
                        maxBarThickness: 28,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            ticks: { maxTicksLimit: 8 },
                        },
                    },
                },
            });

            function redrawCharts() {
                const rainLimit = asNumber(rainChartViewEl?.value, 24);
                const waterLimit = asNumber(waterChartViewEl?.value, 24);

                rainChart.data.labels = chartSlice(chartLabels, rainLimit);
                rainChart.data.datasets[0].data = chartSlice(rainfallData, rainLimit);
                rainChart.update('none');

                waterChart.data.labels = chartSlice(chartLabels, waterLimit);
                waterChart.data.datasets[0].data = chartSlice(waterLevelData, waterLimit);
                waterChart.update('none');
            }

            rainChartViewEl?.addEventListener('change', redrawCharts);
            waterChartViewEl?.addEventListener('change', redrawCharts);

            async function refreshLiveFromBackend() {
                try {
                    const response = await fetch(dashboardLiveEndpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    updateStatusFields(payload);
                    updateLastDataBadge(new Date());

                    if (payload.chart && Array.isArray(payload.chart.chartLabels)) {
                        chartLabels = payload.chart.chartLabels;
                        rainfallData = payload.chart.rainfallData || [];
                        waterLevelData = payload.chart.waterLevelData || [];
                        redrawCharts();
                    }
                } catch (error) {
                    console.error('Gagal refresh live data:', error);
                }
            }

            async function refreshChartsOnly() {
                try {
                    const response = await fetch(dashboardChartEndpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    if (!Array.isArray(payload.chartLabels)) {
                        return;
                    }

                    chartLabels = payload.chartLabels;
                    rainfallData = payload.rainfallData || [];
                    waterLevelData = payload.waterLevelData || [];
                    redrawCharts();
                } catch (error) {
                    console.error('Gagal refresh chart data:', error);
                }
            }

            deviceConfigFormEl?.addEventListener('submit', async (event) => {
                event.preventDefault();
                const formData = new FormData(deviceConfigFormEl);

                const payload = {
                    sleep_minutes: asNumber(formData.get('sleep_minutes')),
                    awake_minutes: asNumber(formData.get('awake_minutes')),
                    rain_tip_threshold: asNumber(formData.get('rain_tip_threshold')),
                    rain_stop_timeout_ms: asNumber(formData.get('rain_stop_timeout_ms')),
                    wifi_warmup_ms: asNumber(formData.get('wifi_warmup_ms')),
                    mm_per_tip: asNumber(formData.get('mm_per_tip')),
                    baseline_cm: asNumber(formData.get('baseline_cm')),
                    esp_mode: asNumber(formData.get('esp_mode')),
                    force_rain: asNumber(formData.get('force_rain')) === 1,
                };

                configStatusEl.textContent = 'Menyimpan dan mengirim konfigurasi...';

                try {
                    const response = await fetch(dashboardConfigEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                    });

                    const result = await response.json();
                    if (!response.ok) {
                        throw new Error(result.message || 'Gagal update konfigurasi.');
                    }

                    configStatusEl.textContent = `${result.message} (${new Date().toLocaleTimeString('id-ID')})`;
                    refreshLiveFromBackend();
                } catch (error) {
                    configStatusEl.textContent = `Error: ${error.message}`;
                }
            });

            function setupMqttRealtime() {
                if (!window.mqtt) {
                    return;
                }

                const wsProtocol = mqttConfig.ws_protocol || 'wss';
                const wsHost = mqttConfig.ws_host || 'broker.emqx.io';
                const wsPort = mqttConfig.ws_port || 8084;
                const wsPath = mqttConfig.ws_path || '/mqtt';
                const dataTopic = mqttConfig.data_topic;
                const statusTopic = mqttConfig.status_topic;

                const url = `${wsProtocol}://${wsHost}:${wsPort}${wsPath}`;
                const client = mqtt.connect(url, {
                    clientId: `web_iot_${Math.random().toString(16).slice(2, 10)}`,
                    clean: true,
                    reconnectPeriod: 4000,
                    connectTimeout: 20000,
                    keepalive: 60,
                });

                client.on('connect', () => {
                    client.subscribe([dataTopic, statusTopic], { qos: 0 }, (error) => {
                        if (error) {
                            console.error('MQTT subscribe gagal:', error);
                        }
                    });
                });

                client.on('message', (topic, messageBuffer) => {
                    try {
                        const payload = JSON.parse(messageBuffer.toString());
                        updateStatusFields(payload);
                        updateLastDataBadge(new Date());
                        refreshChartsOnly();

                    } catch (error) {
                        console.error('Payload MQTT tidak valid:', error);
                    }
                });

                client.on('error', (error) => {
                    console.error('MQTT error:', error);
                });
            }

            refreshLiveFromBackend();
            setInterval(refreshLiveFromBackend, 3500);
            setupMqttRealtime();
        </script>
    @endpush
</x-layouts.app>
