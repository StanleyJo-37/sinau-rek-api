<?php

return [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'encrypted' => true,
            'host' => env('PUSHER_APP_HOST', '127.0.0.1'),
            'port' => env('PUSHER_APP_PORT', 6001),
            'scheme' => env('PUSHER_APP_SCHEME', 'http'),
        ],
    ],
];