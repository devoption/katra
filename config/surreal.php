<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SurrealDB Spike Configuration
    |--------------------------------------------------------------------------
    |
    | This spike proves a local-first SurrealDB flow by starting a local
    | SurrealDB process and then driving it from Laravel via the `surreal`
    | CLI. It does not prove true in-process embedded PHP support.
    |
    */

    'binary' => env('SURREAL_BINARY', 'surreal'),

    'host' => env('SURREAL_HOST', '127.0.0.1'),

    'port' => (int) env('SURREAL_PORT', 18001),

    'username' => env('SURREAL_USER', 'root'),

    'password' => env('SURREAL_PASS', 'root'),

    'namespace' => env('SURREAL_NAMESPACE', 'katra'),

    'database' => env('SURREAL_DATABASE', 'workspace'),

    'storage_engine' => env('SURREAL_STORAGE_ENGINE', 'surrealkv'),

    'storage_path' => env('SURREAL_STORAGE_PATH', storage_path('app/surrealdb/dev')),
];
