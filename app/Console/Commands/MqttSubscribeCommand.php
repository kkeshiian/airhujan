<?php

namespace App\Console\Commands;

use App\Models\AudioRecord;
use App\Models\SensorLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\MqttClient;
use Throwable;

class MqttSubscribeCommand extends Command
{
    protected $signature = 'mqtt:subscribe
        {--host= : MQTT host}
        {--port= : MQTT port}
        {--username= : MQTT username}
        {--password= : MQTT password}
        {--client-id= : MQTT client id}
        {--sensor-topic= : Sensor topic (alat 1)}
        {--audio-topic= : Audio topic (alat 2)}
        {--qos= : QoS level 0-2}';

    protected $description = 'Subscribe MQTT topic dan simpan data sensor/audio ke database.';

    public function handle(): int
    {
        $host = (string) ($this->option('host') ?: config('mqtt.host'));
        $port = (int) ($this->option('port') ?: config('mqtt.port'));
        $username = $this->option('username') ?: config('mqtt.username');
        $password = $this->option('password') ?: config('mqtt.password');
        $sensorTopic = (string) ($this->option('sensor-topic') ?: config('mqtt.sensor_topic'));
        $audioTopic = (string) ($this->option('audio-topic') ?: config('mqtt.audio_topic'));
        $qos = (int) ($this->option('qos') ?: config('mqtt.qos'));
        $clientId = (string) ($this->option('client-id') ?: config('mqtt.client_id_prefix').'_'.Str::random(8));

        $topics = array_values(array_unique(array_filter([$sensorTopic, $audioTopic])));
        if (empty($topics)) {
            $this->error('Topic MQTT belum diatur. Isi MQTT_SENSOR_TOPIC atau MQTT_AUDIO_TOPIC.');

            return self::FAILURE;
        }

        $settings = (new ConnectionSettings)
            ->setConnectTimeout((int) config('mqtt.connect_timeout', 10))
            ->setSocketTimeout((int) config('mqtt.socket_timeout', 5))
            ->setKeepAliveInterval((int) config('mqtt.keep_alive', 30));

        if (!empty($username)) {
            $settings = $settings->setUsername((string) $username);
        }

        if (!empty($password)) {
            $settings = $settings->setPassword((string) $password);
        }

        if ((bool) config('mqtt.use_tls', false)) {
            $settings = $settings
                ->setUseTls(true)
                ->setTlsVerifyPeer((bool) config('mqtt.tls_verify_peer', true))
                ->setTlsVerifyPeerName((bool) config('mqtt.tls_verify_peer_name', true));
        }

        $mqtt = new MqttClient($host, $port, $clientId);

        try {
            $mqtt->connect($settings, true);
            $this->info(sprintf('MQTT connected: %s:%d (clientId=%s)', $host, $port, $clientId));

            foreach ($topics as $topic) {
                $mqtt->subscribe($topic, function (string $topic, string $message, bool $retained, array $matchedWildcards) use ($sensorTopic, $audioTopic): void {
                    $this->line(sprintf('MQTT message received: topic=%s retained=%s', $topic, $retained ? 'yes' : 'no'));

                    try {
                        $this->processMessage($topic, $message, $sensorTopic, $audioTopic);
                    } catch (Throwable $e) {
                        $this->error('Failed processing MQTT payload: '.$e->getMessage());
                    }
                }, $qos);
                $this->line('Subscribed to: '.$topic);
            }

            if (function_exists('pcntl_async_signals') && function_exists('pcntl_signal')) {
                pcntl_async_signals(true);
                pcntl_signal(SIGINT, function () use ($mqtt): void {
                    $this->warn('Stopping MQTT subscriber...');
                    $mqtt->interrupt();
                });
            }

            $mqtt->loop(true);
            $mqtt->disconnect();
        } catch (MqttClientException|Throwable $e) {
            $this->error('MQTT subscriber error: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function processMessage(string $topic, string $message, string $sensorTopic, string $audioTopic): void
    {
        $payload = json_decode($message, true);

        if (!is_array($payload)) {
            $this->warn('Payload bukan JSON object, diabaikan: '.$topic);

            return;
        }

        $normalizedTopic = trim($topic);
        $normalizedSensorTopic = trim($sensorTopic);
        $normalizedAudioTopic = trim($audioTopic);

        $isSensorPayload = array_key_exists('curah_hujan_mm', $payload) || array_key_exists('jarak_air_cm', $payload);
        $isAudioPayload = array_key_exists('file_path', $payload) || array_key_exists('duration_seconds', $payload) || array_key_exists('durasi_detik', $payload);

        if ($normalizedTopic === $normalizedSensorTopic || str_ends_with($normalizedTopic, '/alat1/data') || $isSensorPayload) {
            $this->storeSensorPayload($payload);

            return;
        }

        if ($normalizedTopic === $normalizedAudioTopic || str_ends_with($normalizedTopic, '/alat2/audio') || $isAudioPayload) {
            $this->storeAudioPayload($payload);

            return;
        }

        if (str_contains($normalizedTopic, '/audio')) {
            $this->storeAudioPayload($payload);

            return;
        }

        if (str_contains($normalizedTopic, '/data')) {
            $this->storeSensorPayload($payload);
        }
    }

    private function storeSensorPayload(array $payload): void
    {
        $rainfall = $this->toFloat($payload['curah_hujan_mm'] ?? null);
        $distance = $this->toFloat($payload['jarak_air_cm'] ?? null);

        if ($rainfall === null && $distance === null) {
            $this->warn('Payload sensor tidak punya curah_hujan_mm/jarak_air_cm, diabaikan.');

            return;
        }

        $recordedAt = $this->resolveRecordedAt($payload['recorded_at'] ?? $payload['timestamp'] ?? null);

        $data = [
            'device_code' => (string) ($payload['device_code'] ?? 'alat_1'),
            'rainfall_mm' => $rainfall,
            'water_level_cm' => $distance,
            'rain_status' => ($rainfall ?? 0.0) > 0 ? 'Rain' : 'No Rain',
            'battery_percent' => $this->toInt($payload['battery_percent'] ?? null),
            'solar_power_watts' => $this->toInt($payload['solar_power_watts'] ?? null),
            'device_status' => (string) ($payload['device_status'] ?? 'online via mqtt'),
            'recorded_at' => $recordedAt,
        ];

        $log = $this->createSensorLogWithRetry($this->filterFillableTableColumns('sensor_logs', $data));

        $this->info(sprintf(
            'Sensor saved: #%d rainfall=%.2f mm, distance=%.2f cm',
            $log->id,
            (float) ($rainfall ?? 0.0),
            (float) ($distance ?? 0.0)
        ));
    }

    private function storeAudioPayload(array $payload): void
    {
        $recordedAt = $this->resolveRecordedAt($payload['recorded_at'] ?? $payload['timestamp'] ?? null);
        $duration = $this->toInt($payload['duration_seconds'] ?? $payload['durasi_detik'] ?? null);

        $title = (string) ($payload['title'] ?? ('hujan-'.$recordedAt->format('HisdmY')));
        $filePath = (string) ($payload['file_path'] ?? ('audio/mqtt-'.Str::lower(Str::random(10)).'.mp3'));

        $data = [
            'title' => $title,
            'device_code' => (string) ($payload['device_code'] ?? 'alat_2'),
            'file_path' => $filePath,
            'duration_seconds' => $duration,
            'device_status' => (string) ($payload['device_status'] ?? 'audio mqtt ingested'),
            'recorded_at' => $recordedAt,
        ];

        $record = $this->createAudioRecordWithRetry($this->filterFillableTableColumns('audio_records', $data));

        $this->info(sprintf('Audio saved: #%d title=%s', $record->id, $record->title));
    }

    private function resolveRecordedAt(mixed $value): Carbon
    {
        if (empty($value)) {
            return now();
        }

        try {
            return Carbon::parse((string) $value);
        } catch (Throwable) {
            return now();
        }
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function createSensorLogWithRetry(array $data): SensorLog
    {
        try {
            return SensorLog::create($data);
        } catch (Throwable $e) {
            DB::reconnect();

            return SensorLog::create($data);
        }
    }

    private function createAudioRecordWithRetry(array $data): AudioRecord
    {
        try {
            return AudioRecord::create($data);
        } catch (Throwable $e) {
            DB::reconnect();

            return AudioRecord::create($data);
        }
    }

    private function filterFillableTableColumns(string $table, array $data): array
    {
        $columns = Schema::getColumnListing($table);

        return array_filter(
            $data,
            fn (string $key): bool => in_array($key, $columns, true),
            ARRAY_FILTER_USE_KEY
        );
    }
}
