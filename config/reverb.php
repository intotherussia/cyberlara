<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Reverb Server
    |--------------------------------------------------------------------------
    */
    'default' => env('REVERB_DRIVER', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Reverb Servers
    |--------------------------------------------------------------------------
    */
    'servers' => [
        'reverb' => [
            'host' => env('REVERB_HOST', '127.0.0.1'),
            'port' => env('REVERB_PORT', 8080),
            'hostname' => env('REVERB_HOSTNAME', '127.0.0.1'),
            'options' => [
                'tls' => [],
            ],
            'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 10_000),
            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'server' => [
                    'url' => env('REDIS_URL'),
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => env('REDIS_PORT', '6379'),
                    'username' => env('REDIS_USERNAME'),
                    'password' => env('REDIS_PASSWORD'),
                    'database' => env('REDIS_DB', '0'),
                ],
            ],
            'pulse_ingest_interval' => env('REVERB_PULSE_INGEST_INTERVAL', 15),
            'telescope_ingest_interval' => env('REVERB_TELESCOPE_INGEST_INTERVAL', 15),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reverb Applications
    |--------------------------------------------------------------------------
    */
    'apps' => [
        'provider' => 'config',
        'apps' => [
            [
                'id' => env('REVERB_APP_ID', '383362'),
                'key' => env('REVERB_APP_KEY', 'vhfqctzcergbqlzbmtda'),
                'secret' => env('REVERB_APP_SECRET', 'mexhdfdqbnlahm0vqjld'),
                'app_id' => env('REVERB_APP_ID', '383362'),
                'options' => [
                    'host' => env('REVERB_HOST', '127.0.0.1'),
                    'port' => env('REVERB_PORT', 8080),
                    'scheme' => env('REVERB_SCHEME', 'http'),
                    'useTLS' => false,
                ],
                'allowed_origins' => ['*'],
                'ping_interval' => 60,
                'activity_timeout' => 30,
                'max_connections' => 100,
                'max_message_size' => 10_000,
            ],
        ],
    ],
];
