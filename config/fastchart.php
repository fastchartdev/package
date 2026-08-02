<?php

// config for Fastchartdev/Package
return [
    'debug' => env('FASTCHART_DEBUG', false),

    'queues' => [
        'main' => [
            'connection' => env('FASTCHART_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
            'queue' => env('FASTCHART_QUEUE_NAME', 'event-records'),
            'unique_via_cache_driver' => env('FASTCHART_QUEUE_UNIQUE_VIA_CACHE_DRIVER', 'redis'),
            'backoff' => [
                10, // Retry after 10 seconds
                30, // Retry after 30 seconds
                60 * 1, // Retry after 1 minute
                60 * 2, // Retry after 2 minutes
                60 * 5, // Retry after 5 minutes
                60 * 10, // Retry after 10 minutes
                60 * 30, // Retry after 30 minutes
                60 * 60, // Retry after 1 hour
            ],
        ],
    ],

    'connections' => [
        'main' => [
            'connection' => env('FASTCHART_DB_CONNECTION', env('DB_CONNECTION', 'sqlite')),
        ],
        'event_records' => [
            'connection' => env('FASTCHART_DB_EVENT_RECORDS_CONNECTION', config('fastchart.connections.main.connection', 'sqlite')),
        ],
    ],
];
