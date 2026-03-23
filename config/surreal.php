<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SurrealDB Foundation Configuration
    |--------------------------------------------------------------------------
    |
    | Katra talks to SurrealDB directly over HTTP for queries and schema work.
    | In local desktop development it can still use the Surreal CLI to
    | auto-start a local runtime when the CLI is available. Server
    | deployments can point Laravel at an already-running SurrealDB endpoint.
    |
    */

    'driver' => env('SURREAL_DRIVER', 'cli'),

    'runtime' => env('SURREAL_RUNTIME', 'local'),

    'autostart' => env('SURREAL_AUTOSTART', true),

    'binary' => env('SURREAL_BINARY', 'surreal'),

    'extras_path' => env('NATIVEPHP_EXTRAS_PATH'),

    'bundled_binary_relative_path' => env('SURREAL_BUNDLED_BINARY_RELATIVE_PATH', 'surreal/bin/surreal'),

    'host' => env('SURREAL_HOST', '127.0.0.1'),

    'port' => (int) env('SURREAL_PORT', 18001),

    'endpoint' => env('SURREAL_ENDPOINT', sprintf('ws://%s:%d', env('SURREAL_HOST', '127.0.0.1'), (int) env('SURREAL_PORT', 18001))),

    'username' => env('SURREAL_USER', 'root'),

    'password' => env('SURREAL_PASS', 'root'),

    'namespace' => env('SURREAL_NAMESPACE', 'katra'),

    'database' => env('SURREAL_DATABASE', 'workspace'),

    'storage_engine' => env('SURREAL_STORAGE_ENGINE', 'surrealkv'),

    'storage_path' => env('SURREAL_STORAGE_PATH', storage_path('app/surrealdb/dev')),

    'runtime_pid_path' => env('SURREAL_RUNTIME_PID_PATH', storage_path('app/surrealdb/runtime.pid')),

    'runtime_log_path' => env('SURREAL_RUNTIME_LOG_PATH', storage_path('logs/surreal-runtime.log')),
];
