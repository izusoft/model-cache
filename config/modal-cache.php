<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Отключаются только методы remember и set
    |
    |--------------------------------------------------------------------------
    */
    'enabled-redis-db' => env('REDIS_DB_ENABLED', true),
    'enabled-redis-cache' => env('REDIS_CACHE_ENABLED', true),
    'debug-events' => env('REDIS_CACHE_EVENT_DEBUG', false),
    'debug-sql' => env('REDIS_CACHE_SQL_DEBUG', false),

    // тег => [теги которые чистятся вместе с родительским тегом]
    'cleaning-dependencies' => [
        //'tag' => [ 'tag dependency 1', 'tag dependency 1']
    ],
];
