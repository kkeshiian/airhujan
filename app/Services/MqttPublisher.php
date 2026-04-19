<?php

namespace App\Services;

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\MqttClient;
use Throwable;

class MqttPublisher
{
    /**
     * @throws MqttClientException
     */
    public function publish(string $topic, array $payload, int $qos = 0, bool $retain = false): void
    {
        $host = (string) config('mqtt.host');
        $port = (int) config('mqtt.port');
        $username = config('mqtt.username');
        $password = config('mqtt.password');
        $clientId = (string) config('mqtt.client_id_prefix', 'monitoring_laravel').'_pub_'.bin2hex(random_bytes(4));

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
            $mqtt->publish($topic, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $qos, $retain);
            $mqtt->disconnect();
        } catch (Throwable $e) {
            try {
                $mqtt->interrupt();
                $mqtt->disconnect();
            } catch (Throwable) {
                // Ignore cleanup errors from interrupted MQTT session.
            }

            throw $e;
        }
    }
}
