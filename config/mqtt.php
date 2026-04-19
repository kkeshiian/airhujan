<?php

return [
    'host' => env('MQTT_HOST', 'broker.emqx.io'),
    'port' => (int) env('MQTT_PORT', 1883),
    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),
    'qos' => (int) env('MQTT_QOS', 0),
    'config_qos' => (int) env('MQTT_CONFIG_QOS', 1),
    'config_retain' => (bool) env('MQTT_CONFIG_RETAIN', true),

    // TCP MQTT topics.
    'sensor_topic' => env('MQTT_SENSOR_TOPIC', 'risetkebencanaan2026/alat1/data'),
    'status_topic' => env('MQTT_STATUS_TOPIC', 'risetkebencanaan2026/alat1/status'),
    'cmd_topic' => env('MQTT_CMD_TOPIC', 'risetkebencanaan2026/alat1/cmd'),
    'config_topic' => env('MQTT_CONFIG_TOPIC', 'risetkebencanaan2026/alat1/config'),
    'audio_topic' => env('MQTT_AUDIO_TOPIC', 'risetkebencanaan2026/alat2/audio'),

    // Browser MQTT over WebSocket settings.
    'ws_host' => env('MQTT_WS_HOST', env('MQTT_HOST', 'broker.emqx.io')),
    'ws_port' => (int) env('MQTT_WS_PORT', 8084),
    'ws_path' => env('MQTT_WS_PATH', '/mqtt'),
    'ws_protocol' => env('MQTT_WS_PROTOCOL', 'wss'),

    'client_id_prefix' => env('MQTT_CLIENT_ID_PREFIX', 'monitoring_laravel'),

    'connect_timeout' => (int) env('MQTT_CONNECT_TIMEOUT', 10),
    'socket_timeout' => (int) env('MQTT_SOCKET_TIMEOUT', 5),
    'keep_alive' => (int) env('MQTT_KEEP_ALIVE', 30),

    // Use true for MQTT over TLS (usually port 8883).
    'use_tls' => (bool) env('MQTT_TLS', false),
    'tls_verify_peer' => (bool) env('MQTT_TLS_VERIFY_PEER', true),
    'tls_verify_peer_name' => (bool) env('MQTT_TLS_VERIFY_PEER_NAME', true),
];
