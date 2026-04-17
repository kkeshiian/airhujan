<x-layouts.app :title="'Dashboard Monitoring'" :heading="'Dashboard Monitoring'">
    <!-- Row atas: Card Alat 1 & Alat 2 -->
    <section class="grid gap-4 lg:grid-cols-2">
        <article class="rounded-2xl bg-gradient-to-br from-sky-50 via-cyan-50 to-blue-100 p-5 shadow-sm ring-1 ring-cyan-200">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">ALAT 1</p>
                    <h3 class="mt-1 font-semibold text-ink">Curah Hujan & Tinggi Air</h3>
                </div>
                <svg class="h-6 w-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3v-9"></path></svg>
            </div>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <p id="alat1RuntimeBadge" class="inline-flex rounded-full bg-cyan-100 px-2.5 py-1 text-[11px] font-medium text-cyan-800 ring-1 ring-cyan-200">
                    {{ $alat1RuntimeStatus }}
                </p>
                <p id="rainStatusBadge" class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $latestRainStatus === 'Rain' ? 'bg-cyan-100 text-cyan-800' : 'bg-slate-100 text-slate-700' }}">Status: {{ $latestRainStatus }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-white/80 p-3 ring-1 ring-cyan-100">
                    <p class="text-xs text-slate-600">Curah Hujan</p>
                    <p class="mt-2 text-2xl font-bold text-cyan-700"><span id="currentRainfallValue">{{ number_format($latestAlat1?->rainfall_mm ?? 0, 1) }}</span> <span class="text-sm">mm</span></p>
                </div>
                <div class="rounded-xl bg-white/80 p-3 ring-1 ring-blue-100">
                    <p class="text-xs text-slate-600">Jarak Air</p>
                    <p class="mt-2 text-2xl font-bold text-cyan-700"><span id="jarakAirValue">{{ number_format($latestAlat1?->water_level_cm ?? 0, 1) }}</span> <span class="text-sm">cm</span></p>
                </div>
            </div>
        </article>

        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-3 flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">ALAT 2</p>
                    <h3 class="mt-1 font-semibold text-ink">Perekam Suara</h3>
                </div>
                <svg class="h-6 w-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4"></path></svg>
            </div>
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <p id="alat2RuntimeBadge" class="inline-flex rounded-full bg-sky-100 px-2.5 py-1 text-[11px] font-medium text-sky-800 ring-1 ring-sky-200">
                    {{ $alat2RuntimeStatus }}
                </p>
                <a href="{{ route('audio.index') }}" class="inline-block rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                    Lihat Rekaman
                </a>
            </div>
            <div class="rounded-xl bg-sky-50 p-3">
                <p class="text-xs text-slate-600">Status</p>
                <p id="alat2StatusText" class="mt-2 text-lg font-semibold text-sky-700">Siap Merekam</p>
            </div>
        </article>
    </section>

    <!-- Row bawah: 3 chart bersampingan -->
    <section class="mt-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div class="grid gap-3 lg:grid-cols-3">
            <section class="rounded-xl bg-sky-50/70 p-2.5 ring-1 ring-sky-100">
                <p class="mb-1 text-xs font-semibold text-sky-800">Curah Hujan (mm)</p>
                <div class="h-[190px] w-full">
                    <canvas id="rainfallBarChart"></canvas>
                </div>
            </section>
            <section class="rounded-xl bg-blue-50/70 p-2.5 ring-1 ring-blue-100">
                <p class="mb-1 text-xs font-semibold text-blue-800">Jarak Air (cm)</p>
                <div class="h-[190px] w-full">
                    <canvas id="waterLevelBarChart"></canvas>
                </div>
            </section>
            <section class="rounded-xl bg-cyan-50/70 p-2.5 ring-1 ring-cyan-100">
                <p class="mb-1 text-xs font-semibold text-cyan-800">Frekuensi Rekaman Audio</p>
                <div class="h-[190px] w-full">
                    <canvas id="audioFrequencyChart"></canvas>
                </div>
            </section>
        </div>
    </section>

    <!-- Ringkasan daya + chart daya -->
    <section class="mt-6 grid gap-5 lg:grid-cols-[1fr_1.2fr]">
        <article class="rounded-2xl bg-gradient-to-br from-sky-50 via-blue-50 to-cyan-100 p-5 shadow-sm ring-1 ring-blue-200">
            <div class="flex flex-col gap-5">
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-white/75 p-3 ring-1 ring-sky-100">
                        <div class="mb-2 flex items-center gap-2">
                            <svg class="h-5 w-5 text-sky-600" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3" stroke="currentColor" stroke-width="2"/><line x1="12" y1="21" x2="12" y2="23" stroke="currentColor" stroke-width="2"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64" stroke="currentColor" stroke-width="2"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78" stroke="currentColor" stroke-width="2"/><line x1="1" y1="12" x2="3" y2="12" stroke="currentColor" stroke-width="2"/><line x1="21" y1="12" x2="23" y2="12" stroke="currentColor" stroke-width="2"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36" stroke="currentColor" stroke-width="2"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22" stroke="currentColor" stroke-width="2"/></svg>
                            <p class="text-xs font-semibold uppercase tracking-[0.05em] text-sky-700">Solar Panel</p>
                        </div>
                        <p class="text-2xl font-bold text-sky-700"><span id="latestSolarValue">{{ $latestSolar }}</span> <span class="text-sm">W</span></p>
                        <p class="mt-1 text-xs text-slate-600">Daya input saat ini</p>
                    </div>
                    <div class="rounded-xl bg-white/75 p-3 ring-1 ring-blue-100">
                        <div class="mb-2 flex items-center gap-2">
                            <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="10" rx="1" stroke="currentColor" stroke-width="2" fill="none"/><rect x="22" y="11" width="2" height="2" fill="currentColor"/></svg>
                            <p class="text-xs font-semibold uppercase tracking-[0.05em] text-blue-700">Baterai</p>
                        </div>
                        <p class="text-2xl font-bold text-blue-700"><span id="latestBatteryValue">{{ $latestBattery }}</span> <span class="text-sm">%</span></p>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-300">
                            <div id="batteryLevelBar" class="h-full rounded-full transition-all {{ $latestBattery >= 75 ? 'bg-sky-500' : ($latestBattery >= 50 ? 'bg-cyan-500' : ($latestBattery >= 25 ? 'bg-blue-500' : 'bg-red-500')) }}" style="width: {{ $latestBattery }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-white/75 p-3 ring-1 ring-slate-200">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.08em] text-slate-500">Relay WiFi</p>
                            <p id="relayMqttStatus" class="mt-1 inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800 ring-1 ring-blue-200">
                                Menunggu status MQTT
                            </p>
                        </div>
                        <label class="inline-flex cursor-pointer items-center gap-2">
                            <span class="text-xs font-semibold text-blue-700">OFF</span>
                            <span class="relative inline-flex items-center">
                                <input type="checkbox" class="peer sr-only" aria-label="Toggle relay wifi">
                                <span class="h-7 w-12 rounded-full bg-blue-200 transition peer-checked:bg-blue-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300"></span>
                                <span class="pointer-events-none absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition-all peer-checked:left-6"></span>
                            </span>
                            <span class="text-xs font-semibold text-blue-700">ON</span>
                        </label>
                    </div>
                </div>
            </div>
        </article>

        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="font-semibold text-ink">Daya Solar & Baterai</h3>
            </div>
            <div class="h-[260px] w-full md:h-[290px]">
                <canvas id="powerChart"></canvas>
            </div>
        </article>
    </section>

    <!-- Lokasi Perangkat -->
    <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Lokasi Perangkat</p>
                <h3 class="mt-1 font-semibold text-ink">Peta Lokasi Alat Monitoring</h3>
            </div>
            <svg class="h-6 w-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </div>
        <div id="locationMap" class="h-[230px] w-full rounded-xl ring-1 ring-slate-200 sm:h-[270px]"></div>
        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($locations as $location)
                <div class="rounded-lg bg-slate-50 p-2.5">
                    <p class="text-xs font-semibold uppercase text-slate-700">{{ $location->device_code }}</p>
                    <p class="truncate text-xs text-slate-600">{{ $location->device_name }}</p>
                </div>
            @endforeach
        </div>
    </section>

    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script src="https://unpkg.com/mqtt@5.10.3/dist/mqtt.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script>
            // Map
            @php
                $mapPoints = $locations->map(function ($item) {
                    return [
                        'name' => $item->device_name,
                        'code' => strtoupper($item->device_code),
                        'lat' => $item->latitude,
                        'lng' => $item->longitude,
                    ];
                })->values();
            @endphp

            const points = @json($mapPoints);
            const defaultPoint = points[0] || { lat: -6.2, lng: 106.8 };
            const locationMap = L.map('locationMap', {
                zoomControl: true,
                scrollWheelZoom: false,
            }).setView([defaultPoint.lat, defaultPoint.lng], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(locationMap);

            const bounds = [];

            points.forEach((point) => {
                L.marker([point.lat, point.lng])
                    .addTo(locationMap)
                    .bindPopup(`<strong>${point.code}</strong><br>${point.name}`);

                bounds.push([point.lat, point.lng]);
            });

            if (bounds.length > 1) {
                locationMap.fitBounds(bounds, { padding: [24, 24] });
            }

            // Resize map on window resize
            window.addEventListener('resize', () => {
                setTimeout(() => locationMap.invalidateSize(), 250);
            });

            // Main bar charts
            const labels = @json($chartLabels);
            const dashboardLiveEndpoint = @json(route('dashboard.live'));
            const chartDataEndpoint = @json(route('dashboard.chart-data'));
            const rainfallCtx = document.getElementById('rainfallBarChart').getContext('2d');
            const rainfallBarChart = new Chart(rainfallCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Curah Hujan (mm)',
                        data: @json($rainfallData),
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(14, 165, 233, 0.72)',
                        borderRadius: 6,
                        maxBarThickness: 22
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            ticks: { color: '#0ea5e9' },
                            grid: { color: 'rgba(148, 163, 184, 0.2)' }
                        },
                        x: {
                            ticks: { maxTicksLimit: 8, color: '#64748b' },
                            grid: { display: false }
                        }
                    }
                }
            });

            const waterLevelCtx = document.getElementById('waterLevelBarChart').getContext('2d');
            const waterLevelBarChart = new Chart(waterLevelCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jarak Air (cm)',
                        data: @json($waterLevelData),
                        borderColor: '#1d4ed8',
                        backgroundColor: 'rgba(37, 99, 235, 0.72)',
                        borderRadius: 6,
                        maxBarThickness: 22
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            ticks: { color: '#2563eb' },
                            grid: { color: 'rgba(148, 163, 184, 0.2)' }
                        },
                        x: {
                            ticks: { maxTicksLimit: 8, color: '#64748b' },
                            grid: { display: false }
                        }
                    }
                }
            });

            const audioFrequencyCtx = document.getElementById('audioFrequencyChart').getContext('2d');
            const audioFrequencyChart = new Chart(audioFrequencyCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Frekuensi Rekaman',
                        data: @json($audioFrequencyData),
                        borderColor: '#0891b2',
                        backgroundColor: 'rgba(6, 182, 212, 0.72)',
                        borderRadius: 6,
                        maxBarThickness: 22
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#0e7490' },
                            grid: { color: 'rgba(148, 163, 184, 0.2)' }
                        },
                        x: {
                            ticks: { maxTicksLimit: 8, color: '#64748b' },
                            grid: { display: false }
                        }
                    }
                }
            });

            // Power Chart
            const powerCtx = document.getElementById('powerChart').getContext('2d');
            const powerChart = new Chart(powerCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Daya Solar (W)',
                            data: @json($solarData),
                            borderColor: '#0ea5e9',
                            backgroundColor: 'rgba(14, 165, 233, 0.15)',
                            fill: true,
                            borderWidth: 2.5,
                            tension: 0.4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Baterai (%)',
                            data: @json($batteryData),
                            borderColor: '#1d4ed8',
                            backgroundColor: 'rgba(29, 78, 216, 0.15)',
                            fill: true,
                            borderWidth: 2.5,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Daya Solar (W)' },
                            ticks: { color: '#0ea5e9' },
                            grid: { color: 'rgba(148, 163, 184, 0.2)' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: { display: true, text: 'Baterai (%)' },
                            ticks: { color: '#1d4ed8' },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });

            // MQTT over WSS (EMQX public broker)
            const mqtt_broker = 'broker.emqx.io';
            const mqtt_port = 8084;
            const mqtt_topic_data = 'risetkebencanaan2026/alat1/data';
            const mqttStatusEl = document.getElementById('relayMqttStatus');
            const alat1RuntimeBadgeEl = document.getElementById('alat1RuntimeBadge');
            const alat2RuntimeBadgeEl = document.getElementById('alat2RuntimeBadge');
            const alat2StatusTextEl = document.getElementById('alat2StatusText');
            const rainStatusBadgeEl = document.getElementById('rainStatusBadge');
            const currentRainfallValueEl = document.getElementById('currentRainfallValue');
            const jarakAirValueEl = document.getElementById('jarakAirValue');
            const latestSolarValueEl = document.getElementById('latestSolarValue');
            const latestBatteryValueEl = document.getElementById('latestBatteryValue');
            const batteryLevelBarEl = document.getElementById('batteryLevelBar');

            function toNumber(value) {
                const parsed = Number(value);
                return Number.isFinite(parsed) ? parsed : null;
            }

            function setRainStatusByRainfall(rainfall) {
                if (!rainStatusBadgeEl || rainfall === null) {
                    return;
                }

                const isRain = rainfall > 0;
                rainStatusBadgeEl.textContent = `Status: ${isRain ? 'Rain' : 'No Rain'}`;
                rainStatusBadgeEl.classList.remove('bg-cyan-100', 'text-cyan-800', 'bg-slate-100', 'text-slate-700');
                if (isRain) {
                    rainStatusBadgeEl.classList.add('bg-cyan-100', 'text-cyan-800');
                } else {
                    rainStatusBadgeEl.classList.add('bg-slate-100', 'text-slate-700');
                }
            }

            function setRainStatusByText(status) {
                if (!rainStatusBadgeEl) {
                    return;
                }

                const isRain = String(status || '').toLowerCase() === 'rain';
                rainStatusBadgeEl.textContent = `Status: ${isRain ? 'Rain' : 'No Rain'}`;
                rainStatusBadgeEl.classList.remove('bg-cyan-100', 'text-cyan-800', 'bg-slate-100', 'text-slate-700');
                if (isRain) {
                    rainStatusBadgeEl.classList.add('bg-cyan-100', 'text-cyan-800');
                } else {
                    rainStatusBadgeEl.classList.add('bg-slate-100', 'text-slate-700');
                }
            }

            function setBatteryMeter(value) {
                if (!batteryLevelBarEl) {
                    return;
                }

                const safeValue = Math.max(0, Math.min(100, Number(value) || 0));
                batteryLevelBarEl.style.width = `${safeValue}%`;
                batteryLevelBarEl.classList.remove('bg-sky-500', 'bg-cyan-500', 'bg-blue-500', 'bg-red-500');

                if (safeValue >= 75) {
                    batteryLevelBarEl.classList.add('bg-sky-500');
                } else if (safeValue >= 50) {
                    batteryLevelBarEl.classList.add('bg-cyan-500');
                } else if (safeValue >= 25) {
                    batteryLevelBarEl.classList.add('bg-blue-500');
                } else {
                    batteryLevelBarEl.classList.add('bg-red-500');
                }
            }

            function sanitizeSeries(values, fallbackLength) {
                if (!Array.isArray(values)) {
                    return Array(fallbackLength).fill(0);
                }

                return values.map((value) => {
                    const parsed = Number(value);
                    return Number.isFinite(parsed) ? parsed : 0;
                });
            }

            function applyChartsFromDb(payload) {
                if (!payload || !Array.isArray(payload.chartLabels)) {
                    return;
                }

                const nextLabels = payload.chartLabels;
                const pointsCount = nextLabels.length;

                rainfallBarChart.data.labels = nextLabels;
                rainfallBarChart.data.datasets[0].data = sanitizeSeries(payload.rainfallData, pointsCount);
                rainfallBarChart.update('none');

                waterLevelBarChart.data.labels = nextLabels;
                waterLevelBarChart.data.datasets[0].data = sanitizeSeries(payload.waterLevelData, pointsCount);
                waterLevelBarChart.update('none');

                audioFrequencyChart.data.labels = nextLabels;
                audioFrequencyChart.data.datasets[0].data = sanitizeSeries(payload.audioFrequencyData, pointsCount);
                audioFrequencyChart.update('none');

                powerChart.data.labels = nextLabels;
                powerChart.data.datasets[0].data = sanitizeSeries(payload.solarData, pointsCount);
                powerChart.data.datasets[1].data = sanitizeSeries(payload.batteryData, pointsCount);
                powerChart.update('none');
            }

            async function refreshChartsFromDb() {
                try {
                    const response = await fetch(chartDataEndpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    applyChartsFromDb(payload);
                } catch (error) {
                    console.error('Gagal refresh chart dari database:', error);
                }
            }

            function applyDashboardLiveData(payload) {
                if (!payload || typeof payload !== 'object') {
                    return;
                }

                const rainfall = toNumber(payload.latest_rainfall_mm);
                const waterLevel = toNumber(payload.latest_water_level_cm);
                const battery = toNumber(payload.latest_battery_percent);
                const solar = toNumber(payload.latest_solar_power_watts);

                if (currentRainfallValueEl && rainfall !== null) {
                    currentRainfallValueEl.textContent = rainfall.toFixed(1);
                }

                if (jarakAirValueEl && waterLevel !== null) {
                    jarakAirValueEl.textContent = waterLevel.toFixed(1);
                }

                setRainStatusByText(payload.latest_rain_status);

                if (alat1RuntimeBadgeEl && payload.alat1_runtime_status) {
                    alat1RuntimeBadgeEl.textContent = payload.alat1_runtime_status;
                }

                if (alat2RuntimeBadgeEl && payload.alat2_runtime_status) {
                    alat2RuntimeBadgeEl.textContent = payload.alat2_runtime_status;
                }

                if (alat2StatusTextEl && payload.alat2_status_text) {
                    alat2StatusTextEl.textContent = payload.alat2_status_text;
                }

                if (latestBatteryValueEl && battery !== null) {
                    latestBatteryValueEl.textContent = String(Math.round(battery));
                    setBatteryMeter(battery);
                }

                if (latestSolarValueEl && solar !== null) {
                    latestSolarValueEl.textContent = String(Math.round(solar));
                }

                if (payload.chart) {
                    applyChartsFromDb(payload.chart);
                }
            }

            async function refreshDashboardLive() {
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
                    applyDashboardLiveData(payload);
                } catch (error) {
                    console.error('Gagal refresh live dashboard:', error);
                }
            }

            let chartRefreshDebounceTimer = null;
            function scheduleChartRefresh(delay = 1300) {
                if (chartRefreshDebounceTimer) {
                    clearTimeout(chartRefreshDebounceTimer);
                }

                chartRefreshDebounceTimer = setTimeout(() => {
                    refreshDashboardLive();
                }, delay);
            }

            refreshDashboardLive();
            setInterval(() => {
                refreshDashboardLive().catch(() => {});
            }, 3000);

            function setAlat1Runtime(message) {
                if (!alat1RuntimeBadgeEl) {
                    return;
                }

                alat1RuntimeBadgeEl.textContent = message;
            }

            function setMqttStatus(message, connected = false) {
                if (!mqttStatusEl) {
                    return;
                }

                mqttStatusEl.textContent = message;
                mqttStatusEl.classList.remove('bg-blue-100', 'text-blue-800', 'ring-blue-200', 'bg-emerald-100', 'text-emerald-800', 'ring-emerald-200', 'bg-amber-100', 'text-amber-800', 'ring-amber-200');
                if (connected) {
                    mqttStatusEl.classList.add('bg-emerald-100', 'text-emerald-800', 'ring-emerald-200');
                } else {
                    mqttStatusEl.classList.add('bg-amber-100', 'text-amber-800', 'ring-amber-200');
                }
            }

            if (window.mqtt) {
                const mqttClient = mqtt.connect(`wss://${mqtt_broker}:${mqtt_port}/mqtt`, {
                    clientId: `monitoring_web_${Math.random().toString(16).slice(2, 10)}`,
                    clean: true,
                    reconnectPeriod: 5000,
                    connectTimeout: 30000,
                    keepalive: 60,
                });

                setMqttStatus('Menyambungkan MQTT...');

                mqttClient.on('connect', () => {
                    setMqttStatus('MQTT tersambung', true);
                    setAlat1Runtime('ALAT 1: MQTT tersambung');

                    mqttClient.subscribe(mqtt_topic_data, { qos: 0 }, (error) => {
                        if (error) {
                            setMqttStatus('Subscribe topic gagal');
                            setAlat1Runtime('ALAT 1: subscribe gagal');
                            return;
                        }

                        setMqttStatus('MQTT aktif, menunggu data...', true);
                        setAlat1Runtime('ALAT 1: menunggu data MQTT');
                    });
                });

                mqttClient.on('message', (topic, payloadBuffer) => {
                    const payload = payloadBuffer.toString();
                    const timestamp = new Date().toLocaleTimeString('id-ID');

                    setMqttStatus(`Data diterima ${timestamp}`, true);

                    try {
                        const data = JSON.parse(payload);
                        const curahHujan = toNumber(data.curah_hujan_mm);
                        const jarakAir = toNumber(data.jarak_air_cm);

                        if (topic === mqtt_topic_data) {
                            if (currentRainfallValueEl && curahHujan !== null) {
                                currentRainfallValueEl.textContent = curahHujan.toFixed(1);
                            }

                            if (jarakAirValueEl && jarakAir !== null) {
                                jarakAirValueEl.textContent = jarakAir.toFixed(2);
                            }

                            scheduleChartRefresh();

                            setRainStatusByRainfall(curahHujan);
                            setAlat1Runtime(`ALAT 1: data masuk ${timestamp}`);
                        }

                        console.log('MQTT message parsed:', topic, data);
                    } catch {
                        console.log('MQTT raw message:', topic, payload);
                    }
                });

                mqttClient.on('reconnect', () => {
                    setMqttStatus('Reconnect MQTT...');
                    setAlat1Runtime('ALAT 1: reconnect MQTT...');
                });

                mqttClient.on('close', () => {
                    setMqttStatus('Koneksi MQTT terputus');
                    setAlat1Runtime('ALAT 1: koneksi terputus');
                });

                mqttClient.on('error', (error) => {
                    console.error('MQTT error:', error);
                    setMqttStatus('MQTT error, cek koneksi');
                    setAlat1Runtime('ALAT 1: MQTT error');
                });
            } else {
                setMqttStatus('Library MQTT tidak termuat');
                setAlat1Runtime('ALAT 1: library MQTT gagal dimuat');
            }
        </script>
    @endpush
</x-layouts.app>
