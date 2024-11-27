<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'unix_socket' => env('DB_SOCKET'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
    ],
    'migrations' => 'migrations',
    'redis' => [
        'client' => env('REDIS_DRIVER', 'phpredis'),
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'read_write_timeout' => 0,
            'port' => env('REDIS_PORT', 6379),
            'options' => [
                \Redis::OPT_READ_TIMEOUT => -1,
            ],
            'database' => env('REDIS_SELECT', 0),
        ],
    ],
];
