<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    |
    | Supported: "apc", "array", "database", "file", "memcached", "redis"
    |
    */

    'default' => env('CACHE_DRIVER'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    */

    'stores' => [

        'apc' => [
            'driver' => 'apc',
        ],

        'array' => [
            'driver' => 'array',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache'),
        ],

        'memcached' => [
//            'driver' => 'memcached',
//            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
//            'sasl' => [
//                env('MEMCACHED_USERNAME'),
//                env('MEMCACHED_PASSWORD'),
//            ],
//            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT  => 2000,
//            ],
            [
                'host' => env('REDIS_HOST'),
                'port' => env('REDIS_PORT'),
                'weight' => 100,
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'read_write_timeout' => 0,
            'connection' => 'default',
             'options' => [
                 \Redis::OPT_READ_TIMEOUT  => -1,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing a RAM based store such as APC or Memcached, there might
    | be other applications utilizing the same cache. So, we'll specify a
    | value to get prefixed to all our keys so we can avoid collisions.
    |
    */

    'prefix' => 'laravel',

];
