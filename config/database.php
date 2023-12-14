<?php
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', 'host.docker.internal' ),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', ''),
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD', ''),
        ],
    ],
];